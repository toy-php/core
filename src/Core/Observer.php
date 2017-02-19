<?php

namespace Core;

use Core\Interfaces\Observer as ObserverInterface;
use Core\Interfaces\Subject as SubjectInterface;

class Observer implements ObserverInterface
{

    public function getEventRouts()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function update($event, SubjectInterface $subject, array $options = [])
    {
        $routs = $this->getEventRouts();
        if (!isset($routs[$event])) {
            return;
        }
        $method = $routs[$event];
        if (is_callable($method)) {
            $method($subject, $options);
            return;
        }
        if (method_exists($this, $method)) {
            $this->$method($subject, $options);
        }
    }
}