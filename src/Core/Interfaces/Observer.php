<?php

namespace Core\Interfaces;

interface Observer
{

    /**
     * Обновление состояния наблюдателя
     * @param $event
     * @param Subject $subject
     * @param array $options
     * @return void
     */
    public function update($event, Subject $subject, array $options);
}