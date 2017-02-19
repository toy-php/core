<?php

namespace Core;

use \Core\Interfaces\Subject as SubjectInterface;
use Core\Interfaces\Observer as ObserverInterface;

/**
 * Class EventObject
 * Объект, который может как наблюдателем так и наблюдаемым
 * @package Core
 */
class EventObject extends Observer implements SubjectInterface, ObserverInterface
{

    /**
     * Массив событий
     * @var array
     */
    protected $events = [];

    /**
     * @inheritdoc
     */
    public function bind(array $events, ObserverInterface $observer)
    {
        foreach ($events as $event) {
            if (!isset($this->events[$event])) {
                $this->events[$event] = new \SplObjectStorage();
            }
            $this->events[$event]->attach($observer);
        }
    }

    /**
     * @inheritdoc
     */
    public function unbind(array $events, ObserverInterface $observer)
    {
        foreach ($events as $event) {
            if (isset($this->events[$event])) {
                $this->events[$event]->detach($observer);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function trigger($event, array $options = [])
    {
        if (!isset($this->events[$event])) {
            return;
        }
        /** @var ObserverInterface $observer */
        foreach ($this->events[$event] as $observer) {
            $observer->update($event, $this, $options);
        }
    }

}