<?php

namespace Core;

use Core\Exceptions\ValidateException;

class DataObject extends Model
{

    public function __construct(array $data)
    {
    	parent::__construct();
        try {
            foreach ($data as $key => $value) {
                $method = 'validate' . ucfirst(strtolower($key));
                if (method_exists($this, $method)) {
                    $this[$key] = new Value($this->$method($value));
                } else {
                    $this[$key] = new Value($value);
                }
            }
        } catch (ValidateException $e) {
        	$this->errorMessage = $e->getMessage();
            $this->isError = true;
        	$this->trigger(ModelEvents::EVENT_VALIDATE_ERROR);
        }
    }

}
