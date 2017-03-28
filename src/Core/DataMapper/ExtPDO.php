<?php

namespace Core\DataMapper;

class ExtPDO extends \PDO
{

    protected $log = [];

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @inheritdoc
     */
    public function action($actions)
    {
        if (!is_callable($actions)) {
            throw new \InvalidArgumentException('Неверная функция');
        }
        $this->beginTransaction();
        $result = $actions($this);
        if ($result === false) {
            $this->rollBack();
        } else {
            $this->commit();
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function select($table, $columns = null, $where = null, $join = null)
    {
        $sql = sprintf('SELECT %s FROM %s %s %s',
            $this->parseColumns($columns),
            $this->parseTableName($table),
            $this->parseJoin($join),
            $this->parseConditions($where));
        if (is_array($where)) {
            unset($where['LIMIT']);
            unset($where['ORDER']);
        }
        $bindings = $this->parseBindings($where);
        $this->log($sql, $bindings);
        $stmt = $this->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    /**
     * @inheritdoc
     */
    public function insert($table, $data)
    {
        $keys = array_keys($data);
        $sql = sprintf('INSERT INTO %s %s VALUES %s;',
            $this->parseTableName($table),
            '(' . rtrim(implode(', ', $keys), ', ') . ')',
            '(' . rtrim(str_repeat('?, ', count($keys)), ', ') . ')');
        $bindings = $this->parseBindings($data);
        $this->log($sql, $bindings);
        $stmt = $this->prepare($sql);
        return $stmt->execute($bindings);
    }

    /**
     * @inheritdoc
     */
    public function update($table, $data, $where)
    {
        $set = '';
        $bindings = [];
        foreach ($data as $key => $value) {
            $set .= $key . ' = ?, ';
            $bindings[] = $value;
        }
        $sql = sprintf('UPDATE %s SET %s %s;',
            $this->parseTableName($table),
            rtrim($set, ', '),
            $this->parseConditions($where));
        $bindings = array_merge($this->parseBindings($data),
            $this->parseBindings($where));
        $this->log($sql, $bindings);
        $stmt = $this->prepare($sql);
        return $stmt->execute($bindings);
    }

    /**
     * @inheritdoc
     */
    public function delete($table, $where)
    {
        $sql = sprintf('DELETE FROM %s %s',
            $this->parseTableName($table),
            $this->parseConditions($where));
        $bindings = $this->parseBindings($where);
        $this->log($sql, $bindings);
        $stmt = $this->prepare($sql);
        return $stmt->execute($bindings);
    }

    /**
     * Добавление запроса в журнал
     * @param $sql
     * @param $bindings
     */
    protected function log($sql, $bindings)
    {
        $strReplaceOnce = function ($search, $replace, $text) {
            $pos = strpos($text, $search);
            return $pos !== false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
        };

        foreach ($bindings as $binding) {
            $sql = $strReplaceOnce('?', '\'' . $binding . '\'', $sql);
        }
        $this->log[] = $sql;
    }

    /**
     * Парсинг имени таблицы
     * @param $data
     * @return string
     */
    protected function parseTableName($data)
    {
        if (is_string($data)) {
            return $data;
        }

        $parseAlias = function ($data) {
            if (preg_match('/^([A-Za-z_0-9]+)\(([A-Za-z_0-9]+)\)$/i', $data, $matches)) {
                return $matches;
            }
            return $data;
        };

        $result = '';
        foreach ($data as $tableName) {
            $result .= ((($parsedTableName = $parseAlias($tableName)) != $tableName)
                    ? $parsedTableName[1] . ' AS ' . $parsedTableName[2]
                    : $tableName) . ', ';
        }
        return rtrim($result, ', ');
    }

    /**
     * Парсинг связанных данных
     * @param $data
     * @return array|mixed
     */
    protected function parseBindings($data)
    {
        if (!is_array($data)) {
            return [];
        }
        $parseBindings = function (\RecursiveArrayIterator $iterator) use (&$parseBindings) {
            $result = [];
            while ($iterator->valid()) {
                if ($iterator->hasChildren()) {
                    $result = array_merge($result, $parseBindings($iterator->getChildren()));
                } else {
                    $result[] = $iterator->current();
                }
                $iterator->next();
            }
            return $result;
        };

        return $parseBindings(new \RecursiveArrayIterator($data));
    }

    /**
     * Парсинг колонок
     * @param $data
     * @return string
     */
    protected function parseColumns($data)
    {
        if (empty($data)) {
            return '*';
        }
        if (is_string($data)) {
            return $data;
        }
        $result = '';
        foreach ($data as $table => $columns) {
            foreach ($columns as $column) {
                $result .= $table . '.' . $column . ', ';
            }
        }
        return rtrim($result, ', ');
    }

    /**
     * Парсинг джоинов
     * @param $joins
     * @return string
     */
    protected function parseJoin($joins)
    {
        if (empty($joins)) {
            return '';
        }
        if (is_string($joins)) {
            return ' ' . $joins . ' ';
        }
        $join_directions = [
            '>' => function ($table, $condition) {
                return ' LEFT JOIN ' . $table . ' ON ' . $condition;
            },
            '<' => function ($table, $condition) {
                return ' RIGHT JOIN ' . $table . ' ON ' . $condition;
            },
            '<>' => function ($table, $condition) {
                return ' FULL JOIN ' . $table . ' ON ' . $condition;
            },
            '><' => function ($table, $condition) {
                return ' INNER JOIN ' . $table . ' ON ' . $condition;
            },
        ];

        $condition = function (array $on, $alias) {
            $result = '';
            foreach ($on as $foreign => $primary) {
                $result .= $foreign . ' = ' . $alias . '.' . $primary . ' AND ';
            }
            return '(' . rtrim($result, ' AND ') . ')';
        };

        $result = '';
        foreach ($joins as $join => $on) {
            if (preg_match('/^(\[([<>]+)\])([A-Za-z_0-9]+)(\(([A-Za-z_0-9]+)\))*?$/i', $join, $matches)) {
                $direction = $matches[2];
                $source = $matches[3];
                $alias = isset($matches[5]) ? $matches[5] : $source;
                if (isset($join_directions[$direction])) {
                    $table = $source != $alias ? $source . ' AS ' . $alias : $source;
                    $result .= $join_directions[$direction]($table, $condition($on, $alias));
                }

            }
        }
        return $result;
    }

    /**
     * Парсинг критериев
     * @param $conditions
     * @return string
     */
    protected function parseConditions($conditions)
    {
        if (empty($conditions)) {
            return '';
        }
        if (is_string($conditions)) {
            return ' WHERE ' . $conditions;
        }
        $operators = [
            '=' => function ($field, $bind) {
                if (is_array($bind)) {
                    return $field . ' IN (' . implode(', ', $bind) . ')';
                }
                if (is_null($bind)) {
                    return $field . ' IS NULL';
                }
                return $field . ' = ' . $bind;
            },
            '>' => function ($field, $bind) {
                return $field . ' > ' . $bind;
            },
            '<' => function ($field, $bind) {
                return $field . ' < ' . $bind;
            },
            '>=' => function ($field, $bind) {
                return $field . ' >= ' . $bind;
            },
            '<=' => function ($field, $bind) {
                return $field . ' <= ' . $bind;
            },
            '!' => function ($field, $bind) {
                if (is_array($bind)) {
                    return $field . ' NOT IN (' . implode(', ', $bind) . ')';
                }
                if (is_null($bind)) {
                    return $field . ' IS NOT NULL';
                }
                return $field . ' != ' . $bind;
            },
            '~' => function ($field, $bind) {
                if (is_array($bind)) {
                    $like_value = '';
                    $count_value = count($bind);
                    foreach ($bind as $key => $item) {
                        $like_value .= (($count_value - 1) > $key)
                            ? $field . ' LIKE ' . $item . ' OR '
                            : $field . ' LIKE ' . $item;
                    }
                    return $like_value;
                }
                return $field . ' LIKE ' . $bind;
            },
            '!~' => function ($field, $bind) {
                if (is_array($bind)) {
                    $like_value = '';
                    $count_value = count($bind);
                    foreach ($bind as $key => $item) {
                        $like_value .= (($count_value - 1) > $key)
                            ? $field . ' NOT LIKE ' . $item . ' OR '
                            : $field . ' NOT LIKE ' . $item;
                    }
                    return $like_value;

                }
                return $field . ' NOT LIKE ' . $bind;
            },
            '<>' => function ($field, $bind) {
                return $field . ' BETWEEN ' . implode(' AND ', $bind);
            },
            '><' => function ($field, $bind) {
                return $field . ' NOT BETWEEN ' . implode(' AND ', $bind);
            },
            'LIMIT' => function ($value) {
                return ' LIMIT ' . implode(',', $value);
            },
            'ORDER' => function ($order) {
                $result = ' ORDER BY ';
                foreach ($order as $key => $value) {
                    $result .= ' ' . $key . ' ' . $value . ',';
                }
                return rtrim($result, ',');
            }
        ];

        $parseConditions = function (array $criteria) use (&$parseConditions, $operators) {
            $result = [];
            foreach ($criteria as $key => $value) {
                if (preg_match('/^(and|or)(#[0-9]+)*?$/i', $key) and is_array($value)) {
                    $result[$key] = $parseConditions($value);
                } elseif (preg_match('/^(limit|order)$/i', $key, $matches)) {
                    $result[$key] = $operators[$key]($value);
                } elseif (preg_match('/^([A-Za-z0-9_.\'`]+)(\[([~=<>!]+)\])*?$/i', $key, $matches)) {
                    $operator = isset($matches[3]) ? $matches[3] : '=';
                    if (isset($operators[$operator])) {
                        $bind = is_array($value) ? array_fill(0, count($value), '?') : '?';
                        $result[] = $operators[$operator]($matches[1], $bind);
                    }
                }
            }
            return $result;
        };

        $convertConditions = function (array $condition) use (&$convertConditions) {
            $result = [];
            foreach ($condition as $key => $value) {
                if (preg_match('/^(or|and)(#[0-9]+)*?$/i', $key, $matches)) {
                    $result[$key] = '(' . implode(' ' . strtoupper($matches[1]) . ' ', $convertConditions($value)) . ')';
                } else {
                    $result[$key] = $value;
                }
            }
            return $result;
        };

        $condition = $convertConditions($parseConditions($conditions));
        return !empty($condition) ? ' WHERE ' . implode(' ', $condition) : '';
    }
}


