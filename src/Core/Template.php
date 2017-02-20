<?php

namespace Core;

use Core\Interfaces\Converter as ConverterInterface;
use Core\Interfaces\Model as ModelInterface;

class Template extends Container implements ConverterInterface
{

    /**
     * Расширение файла шаблона
     * @var string
     */
    protected $templateExt = '.php';

    /**
     * Путь к директории с шаблонами
     * @var string
     */
    protected $templateDir = '';

    /**
     * Имя шаблона
     * @var string
     */
    protected $templateName = '';

    public function __construct($templateDir, $templateName, $templateExt = '.php')
    {
        $this->templateDir = $templateDir;
        $this->templateExt = $templateExt;
        $this->templateName = $templateName;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTemplateExt()
    {
        return $this->templateExt;
    }

    /**
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * Получить экземпляр парсера
     * @return Parser
     */
    public function make()
    {
        return new Parser($this);
    }

    /**
     * Конвертировать модель в строку
     * @param ModelInterface $model
     * @return string
     */
    public function convert(ModelInterface $model)
    {
        return $this->make()->render($this->templateName, $model);
    }
}