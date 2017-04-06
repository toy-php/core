<?php

namespace Core\Template;

use Core\Container\Container;

class Template extends Container
{

    public function __construct(array $config = [])
    {
        $defaultConfig = [
            'dir' => '',
            'file_ext' => '.php',
            'functions' => [],
            'vars' => []
        ];
        $config = array_replace_recursive($defaultConfig, $config);

        parent::__construct($config);
    }

    /**
     * Получить экземпляр парсера
     * @return Parser
     */
    public function makeParser()
    {
        return new Parser($this);
    }

    /**
     * Рендеринг шаблона
     * @param $templateName
     * @param $templateData
     * @return string
     */
    public function render($templateName, $templateData)
    {
        return $this->makeParser()->render($templateName, $templateData);
    }
}