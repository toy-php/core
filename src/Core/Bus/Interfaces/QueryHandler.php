<?php

namespace Core\Bus\Interfaces;

interface QueryHandler extends Handler
{

    /**
     * Обработать запрос
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message);
}