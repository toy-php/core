<?php

namespace Core;

use Core\Exceptions\CriticalException;
use Core\Interfaces\Subject as SubjectInterface;

class CallableObserver extends Observer
{

    protected $function;

    function __construct($function)
    {
        if(!is_callable($function)){
            throw new CriticalException('Неверная функция');
        }
        $this->function = $function;
    }

    /**
     * @inheritdoc
     */
    public function update($event, SubjectInterface $subject, array $options = [])
    {
        $function = $this->function;
        return $function($subject, $options);
    }
}