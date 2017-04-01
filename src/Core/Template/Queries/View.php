<?php

namespace Core\Template\Queries;

use Core\Bus\Interfaces\Query;

class View implements Query
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

    /**
     * Данные для рендеринга
     * @var array|object
     */
    protected $data;

    /**
     * Массив расширений функционала
     * @var array
     */
    protected $extends;

    /**
     * View constructor.
     * @param object|array $data
     * @param string $templateDir
     * @param string $templateName
     * @param string $templateExt
     * @param \ArrayAccess $extends
     */
    public function __construct($data, $templateDir, $templateName, $templateExt, \ArrayAccess $extends)
    {
        $this->data = $data;
        $this->templateDir = $templateDir;
        $this->templateExt = $templateExt;
        $this->templateName = $templateName;
        $this->extends = $extends;
    }

    /**
     * Получить расширение файлов шаблонов
     * @return string
     */
    public function getTemplateExt()
    {
        return $this->templateExt;
    }

    /**
     * Получить директория шаблонов
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * Получить имя шаблона
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * Получить данные для шаблона
     * @return array|object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Функции расширяющие парсер
     * @return \ArrayAccess
     */
    public function getExtends()
    {
        return $this->extends;
    }



}