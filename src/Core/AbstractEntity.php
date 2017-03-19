<?php

namespace Core;

use Core\ModelEvents;

abstract class AbstractEntity extends Model
{
    /**
     * Идентификатор сущности
     * @var integer
     */
    public $id = 0;

}
