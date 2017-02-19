<?php

namespace Core\Interfaces;

interface Subject
{

    /**
     * Подписка на события субъекта
     * @param array $events
     * @param Observer $observer
     * @return void
     */
    public function bind(array $events, Observer $observer);

    /**
     * Отписка от событий субъекта
     * @param array $events
     * @param Observer $observer
     * @return void
     */
    public function unbind(array $events, Observer $observer);

    /**
     * Генерация события
     * @param $event
     * @param array $options
     * @return void
     */
    public function trigger($event, array $options);
}