<?php

namespace Toy;

use Toy\Components\Config\ConfigInterface;

abstract class AbstractConnections
{

    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

}