<?php

namespace Core;

use Core\Exceptions\CriticalExceptions;
use Core\Interfaces\Converter as ConverterInterface;
use Core\Interfaces\Model as ModelInterface;

class Template implements ConverterInterface
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
     * Данные шаблона
     * @var null
     */
    protected $templateData = null;

    /**
     * Имя макета шаблона
     * @var string
     */
    protected $layoutTemplateName = '';

    /**
     * Данные макета шаблона
     * @var string
     */
    protected $layoutTemplateData = null;

    /**
     * Секции
     * @var array
     */
    protected $section = [];

    public function __construct($templateDir, $templateName, $templateData = null)
    {
        $this->templateDir = $templateDir;
        $this->templateName = $templateName;
        $this->templateData = $templateData;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if(is_object($this->layoutTemplateData)){
            return !empty($this->templateData) ? $this->templateData->$name : null;
        }

        if(is_array($this->layoutTemplateData)){
            return isset($this->templateData[$name]) ? $this->templateData[$name] : null;
        }
        return null;
    }

    /**
     * Старт секции
     * @param string $name
     * @throws CriticalExceptions
     */
    public function start($name)
    {
        if ($name === 'content') {
            throw new CriticalExceptions('Секция с именем "content" зарезервированна.');
        }
        $this->section[$name] = null;
        ob_start();
    }

    /**
     * Стоп секции
     * @throws CriticalExceptions
     */
    public function stop()
    {
        if (empty($this->section)) {
            throw new CriticalExceptions('Сперва нужно стартовать секцию методом start()');

        }
        end($this->section);
        $this->section[key($this->section)] = ob_get_contents();
        ob_end_clean();
    }

    /**
     * Вывод секции
     * @param string $name
     * @return string|null;
     */
    public function section($name)
    {
        return isset($this->section[$name]) ? $this->section[$name] : null;
    }

    /**
     * Объявление макета шаблона
     * @param $layoutTemplateName
     * @param $layoutTemplateData
     */
    public function layout($layoutTemplateName, $layoutTemplateData = null)
    {
        $this->layoutTemplateName = $layoutTemplateName;
        $this->layoutTemplateData = $layoutTemplateData;
    }

    /**
     * Вставка представления в текущий шаблон
     * @param $templateName
     * @param null $templateData
     * @return string
     */
    public function insert($templateName, $templateData = null)
    {
        return $this->loadTemplate($templateName, $templateData)->render();
    }

    /**
     * Получить экземпляр представления
     * @param $templateName
     * @param null $templateData
     * @return $this|Template
     */
    private function loadTemplate($templateName, $templateData = null)
    {
        if($this->templateName === $templateName){
            return $this;
        }
        return new static($this->templateDir, $templateName, $templateData);
    }

    /**
     * Загрузка шаблона
     * @throws CriticalExceptions
     */
    private function loadTemplateFile()
    {
        $fileName = $this->templateDir . $this->templateName . $this->templateExt;
        if(!file_exists($fileName)){
            throw new CriticalExceptions('Файл шаблона не может быть загружен');
        }
        include $fileName;
    }

    /**
     * Рендеринг шаблона
     * @return string
     * @throws CriticalExceptions
     */
    public function render()
    {
        try{
            ob_start();
            $this->loadTemplateFile();
            $content = ob_get_contents();
            ob_end_clean();
            if (!empty($this->layoutTemplateName)) {
                $layout = $this->loadTemplate($this->layoutTemplateName, $this->layoutTemplateData);
                $layout->section = array_merge($this->section, ['content' => $content]);
                $content = $layout->render();
            }
            return $content;
        }catch(CriticalExceptions $e){
            if (ob_get_length() > 0) {
                ob_end_clean();
            }
            throw $e;
        }
    }

    /**
     * Конвертировать модель в строку
     * @param ModelInterface $model
     * @return string
     */
    public function convert(ModelInterface $model)
    {
        $this->templateData = $model;
        return $this->render();
    }
}