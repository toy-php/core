<?php

namespace Core\Bus\Interfaces;

interface EventHandler extends Handler
{

    /**
     * Обработать событие
     * @param Message $message
     * @return void
     */
    public function handle(Message $message);
}