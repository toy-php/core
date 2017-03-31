<?php

namespace Core\App\Handlers;

use Core\App\Queries\Routs;
use Core\Bus\Interfaces\Message;
use Core\Bus\Interfaces\QueryHandler;

class Router implements QueryHandler
{

    /**
     * Парсинг маршрутов
     * @param $routs
     * @param string $group
     * @param array $result
     * @return array
     */
    private function parseRouts($routs, $group = '', &$result = [])
    {
        foreach ($routs as $pattern => $action) {
            if (is_array($action) and !is_callable($action)) {
                $this->parseRouts(
                    $action,
                    $group . (is_string($pattern) ? $pattern : ''),
                    $result);
            } elseif(is_callable($action)) {
                $pattern_array = explode('/', $pattern);
                $method = array_shift($pattern_array);
                $pattern_chunk = '/' . ltrim(implode('/', $pattern_array), '/');
                $result[$method . $group . $pattern_chunk] = $action;
            }
        }
        return $result;
    }

    /**
     * Обработать запрос
     * @param Message $message
     * @return array|false
     */
    public function handle(Message $message)
    {
        /** @var Routs $message */
        $suffix = $message->getSuffix();
        $routs = $this->parseRouts($message->getRouts());
        $query = $message->getQueryString();
        foreach ($routs as $pattern => $handler) {
            if (preg_match('#^' . rtrim($pattern, '/') . $suffix . '$#is', $query, $matches)
                and is_callable($handler)){
                array_shift($matches);
                return [$handler, $matches];
            }
        }
        return false;
    }
}