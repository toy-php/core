<?php

namespace Toy;

abstract class AbstractSubject
{

    /**
     * Массив наблюдателей
     * @var array
     */
    protected $observers = [];

    /**
     * Привязать обработчик к событию
     * @param $event
     * @param AbstractObserver $observer
     */
    public function bind($event, AbstractObserver $observer)
    {
        $events = is_array($event) ? $event : [$event];
        foreach ($events as $_event) {
            if (!isset($this->observers[$_event])) {
                $this->observers[$_event] = new \SplObjectStorage();
            }
            $this->observers[$_event]->attach($observer);
        }
    }

    /**
     * Отвязать обработчик от события
     * @param $event
     * @param AbstractObserver $observer
     */
    public function unbind($event, AbstractObserver $observer)
    {
        $events = is_array($event) ? $event : [$event];
        foreach ($events as $_event) {
            if (isset($this->observers[$_event])) {
                $this->observers[$_event]->detach($observer);
            }
        }
    }

    /**
     * Оповестить наблюдателей о возникневении события
     * @param $event
     * @param array $options
     */
    public function trigger($event, array $options = [])
    {
        $events = is_array($event) ? $event : [$event];
        foreach ($events as $_event) {
            if (!isset($this->observers[$_event])) {
                return;
            }
            /** @var AbstractObserver $observer */
            foreach ($this->observers[$_event] as $observer) {
                $observer->update($this, $_event, $options);
            }
        }
    }
}