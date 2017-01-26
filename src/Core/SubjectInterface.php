<?php

namespace Core;

interface SubjectInterface
{

    /**
     * Привязать обработчик к событию
     * @param $event
     * @param ObserverInterface $observer
     */
    public function bind($event, ObserverInterface $observer);

    /**
     * Отвязать обработчик от события
     * @param $event
     * @param ObserverInterface $observer
     */
    public function unbind($event, ObserverInterface $observer);

    /**
     * Оповестить наблюдателей о возникневении события
     * @param $event
     * @param array $options
     */
    public function trigger($event, array $options = []);
}