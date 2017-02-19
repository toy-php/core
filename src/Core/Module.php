<?php

namespace Core;

use Core\Interfaces\Module as ModuleInterface;

class Module extends ServicesLocator implements ModuleInterface
{

    public function __construct(array $config = [])
    {
        parent::__construct(new Container($config));
    }

}