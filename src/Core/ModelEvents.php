<?php

namespace Core;

class ModelEvents
{

    /**
     * Событие возникающее перед получением модели
     */
    const EVENT_BEFORE_FETCH = 1;

    /**
     * Событие возникающее при получении данных модели
     */
    const EVENT_FETCH = 2;

    /**
     * Событие возникающее после получения модели
     */
    const EVENT_AFTER_FETCH = 3;

    /**
     * Событие возникающее перед сохранением модели
     */
    const EVENT_BEFORE_SAVE = 4;

    /**
     * Событие возникающее при сохранении модели
     */
    const EVENT_SAVE = 5;

    /**
     * Событие возникающее после сохранения модели
     */
    const EVENT_AFTER_SAVE = 6;

    /**
     * Событие возникающее перед удалением модели
     */
    const EVENT_BEFORE_DELETE = 7;

    /**
     * Событие возникающее при удалении модели
     */
    const EVENT_DELETE = 8;

    /**
     * Событие возникающее после удаления модели
     */
    const EVENT_AFTER_DELETE = 9;

}