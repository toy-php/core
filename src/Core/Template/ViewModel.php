<?php

namespace Core\Template;

class ViewModel
{

    protected $dataType;
    protected $data = [];

    public function __construct($data)
    {
        if(is_array($data) or is_object($data)){
            $this->dataType = gettype($data);
            $this->data = $data;
        }
    }

    function __get($name)
    {
        if(!$this->__isset($name)){
            return null;
        }
        switch ($this->dataType){
            case 'array':
                return $this->data[$name];
                break;
            case 'object':
                return $this->data->$name;
                break;
            default:
                return null;
        }
    }

    public function __isset($name)
    {
        switch ($this->dataType){
            case 'array':
                return isset($this->data[$name]);
                break;
            case 'object':
                return isset($this->data->$name);
                break;
            default:
                return false;
        }
    }
}