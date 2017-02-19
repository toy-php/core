<?php

namespace Core;

use Core\Interfaces\Converter;
use Core\Interfaces\View as ViewInterface;

class View extends Observer implements ViewInterface
{

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var Model
     */
    protected $model;

    public function __construct(Converter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Получение экземпляра с необходимым преобразователем
     * @param Converter $converter
     * @return static
     */
    public function withConverter(Converter $converter)
    {
        if ($this->converter === $converter){
            return $this;
        }
        $instance = clone $this;
        $instance->converter = $converter;
        return $instance;
    }

    /**
     * Рендеринг
     * @return string
     */
    public function render()
    {
        if(empty($this->converter) or empty($this->model)){
            return '';
        }
        return $this->converter->convert($this->model);
    }
}