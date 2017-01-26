<?php

namespace Core;

abstract class AbstractObserver implements ObserverInterface
{

    /**
     * Массив событий и связанных с ними методах
     * @var array
     */
    protected $events = [];

    /**
     * Расширение существующих событий
     * @param array $events
     */
    protected function extendEvents(array $events)
    {
        $this->events = array_merge($this->events, $events);
    }

    /**
     * Обновление состояния наблюдателя
     * @param SubjectInterface $subject
     * @param $event
     * @param array $options
     * @return void
     */
    public function update(SubjectInterface $subject, $event, array $options = [])
    {
        if(isset($this->events[$event])){
            call_user_func_array([$this, $this->events[$event]], [$subject, $event, $options]);
        }
    }
}