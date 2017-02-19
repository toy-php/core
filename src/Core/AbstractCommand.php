<?php

namespace Core;

use Core\Interfaces\Command;
use Core\Interfaces\Model as ModelInterface;

abstract class AbstractCommand implements Command
{
    /**
     * @var ModelInterface
     */
    protected $model;

    /**
     * Command constructor.
     * @param ModelInterface $model
     */
    public function __construct(ModelInterface $model)
    {
        $this->model = $model;
    }
}