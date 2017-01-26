<?php

namespace Core;

interface ObserverInterface
{

    /**
     * Обновление состояния наблюдателя
     * @param SubjectInterface $subject
     * @param $event
     * @param array $options
     * @return void
     */
    public function update(SubjectInterface $subject, $event, array $options = []);
}