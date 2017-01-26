<?php

namespace Core;

interface ModelInterface extends SubjectInterface, \ArrayAccess
{

    /**
     * Получить имя первичного ключа
     * @return string
     */
    public function getPrimaryKey();

    /**
     * Установка триггера состояния модели,
     * меняет состояние на "неизменившуюся" модель
     * @param boolean $changed
     */
    public function setChanged($changed);

    /**
     * Состояние модели
     * @return bool
     */
    public function isChanged();

    /**
     * Заполнение модели данными
     * @param array $data
     */
    public function fill(array $data);

    /**
     * Валидация данных, происходит перед сохранением модели
     * @param array $data
     * @return array|false
     */
    public function validate(array $data);

    /**
     * Получение состояния триггера наличия ошибки валидации
     * @return bool
     */
    public function hasValidationError();

    /**
     * Наличие атрибута
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * Наличие атрибута
     * @param $name
     * @return bool
     */
    public function __isset($name);

    /**
     * Получение значения атрибута
     * @param $name
     * @return mixed|null
     */
    public function get($name);

    /**
     * Получение значения атрибута
     * @param $name
     * @return mixed|null
     */
    public function __get($name);

    /**
     * Установка значения атрибута
     * @param $name
     * @param $value
     */
    public function set($name, $value);

    /**
     * Установка значения атрибута
     * @param $name
     * @param $value
     */
    public function __set($name, $value);

    /**
     * Удаление атрибута
     * @param $name
     */
    public function remove($name);

    /**
     * Очистка данных модели
     */
    public function clear();

    /**
     * Получение данных модели
     * @param array $options опции поиска данных модели, по умолчанию поиск по идентификатору
     */
    public function fetch(array $options = []);

    /**
     * Метод выполняемый перед созданием записи
     * @param array $data
     * @return array
     */
    public function beforeCreate(array $data);

    /**
     * Метод выполняемый перед обновлением записи
     * @param array $data
     * @return array
     */
    public function beforeUpdate(array $data);

    /**
     * Сохранение данных модели
     * Если нет идентификатора, то генерируется событие на создание данных
     * Так же сохраняются все дочерние модели
     */
    public function save();

    /**
     * Удаление данных модели
     */
    public function destroy();

    /**
     * Преобразование модели в массив
     * @return array
     */
    public function toArray();
}