<?php

namespace Core\Bus\Interfaces;

interface Bus
{

    /**
     * Обработать сообщение
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message);
}