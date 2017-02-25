<?php

namespace Core;

use Core\Interfaces\Value as ValueInterface;

class Value extends Model implements ValueInterface
{

    private $value;

    public function __construct($value)
    {
        $this->value = $value;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

}