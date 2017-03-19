<?php

/**
* Класс работы с базой данных
*/
class DbDataSource extends AbstractDbDataSource
{

    /**
     * Исключенные поля из преобразователей
     * @var array
     */
    protected $excludedFields = [];

    /**
     * Заполнить сущность данными
     * @param User $entity
     * @param array $data
     * @return void
     */
    public function fillEntity($entity, array $data)
    {
        foreach ($data as $name => $value) {
            if(property_exists($entity, $name)
                and !in_array($name, $this->excludedFields)){
                $entity->$name = $value;
            }
        }
    }

    /**
     * Преобразовать сущность в массив
     * @param User $entity
     * @return array
     */
    public function entityToArray($entity)
    {
        $entityReflect = new ReflectionClass($entity);
        $properties   = $entityReflect->getProperties(ReflectionProperty::IS_PUBLIC);
        $array = [];
        foreach ($properties as $property) {
            $name = $property->getName();
            $value = $property->getValue();
            if(!empty($value) and !in_array($name, $this->excludedFields)){
                $array[$name] = $value;
            }
        }
        return $array;
    }
}
