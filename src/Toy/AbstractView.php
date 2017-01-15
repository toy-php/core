<?php

namespace Toy;

abstract class AbstractView extends AbstractObserver
{

    /**
     * Конвертер представления в строку
     * @var ConverterInterface
     */
    protected $converter;

    /**
     * AbstractView constructor.
     * @param ConverterInterface $converter
     */
    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * Получение представления в виде строки
     * @return string
     */
    public function __toString()
    {
        return $this->converter->convert($this);
    }

}