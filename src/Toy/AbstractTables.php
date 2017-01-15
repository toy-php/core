<?php

namespace Toy;

abstract class AbstractTables
{

    protected $connections;

    public function __construct(AbstractConnections $connections)
    {
        $this->connections = $connections;
    }


}