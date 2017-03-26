<?php

namespace Core\Bus\Interfaces;

interface Handler
{

    /**
     * Обработать сообщение определенного типа
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message);
}