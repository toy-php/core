<?php

namespace Core\Template;

use Core\Exceptions\CriticalException;
use Core\WebApplication;

class Parser
{

    /**
     * @var WebApplication
     */
    protected $template;

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

    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (is_object($this->templateData) and property_exists($this->templateData, $name)) {
            return $this->templateData->$name;
        }

        if (is_array($this->templateData) and isset($this->templateData[$name])) {
            return $this->templateData[$name];
        }
        return isset($this->template['vars'][$name]) ? $this->template['vars'][$name] : null;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (is_object($this->templateData) and method_exists($this->templateData, $name)) {
              return $this->templateData->$name(...$arguments);
        }
        return isset($this->template['functions'][$name])
            ? $this->template['functions'][$name](...$arguments)
            : null;
    }

    /**
     * Старт секции
     * @param string $name
     * @throws CriticalException
     */
    public function start($name)
    {
        if ($name === 'content') {
            throw new CriticalException('Секция с именем "content" зарезервированна.');
        }
        $this->section[$name] = null;
        ob_start();
    }

    /**
     * Стоп секции
     * @throws CriticalException
     */
    public function stop()
    {
        if (empty($this->section)) {
            throw new CriticalException('Сперва нужно стартовать секцию методом start()');

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
        return $this->template->makeParser()->render($templateName, $templateData);
    }

    /**
     * Загрузка шаблона
     * @param $templateName
     * @throws CriticalException
     */
    private function loadTemplateFile($templateName)
    {
        $fileName = $this->template['dir'] .
            $templateName .
            $this->template['file_ext'];
        if (!file_exists($fileName)) {
            throw new CriticalException('Файл шаблона "' . $fileName . '" не может быть загружен');
        }
        include $fileName;
    }

    /**
     * Рендеринг шаблона
     * @param $templateName
     * @param $templateData
     * @return string
     * @throws CriticalException
     */
    public function render($templateName, $templateData = null)
    {
        try {
            ob_start();
            $this->templateData = $templateData;
            $this->loadTemplateFile($templateName);
            $content = ob_get_contents();
            ob_end_clean();
            if (!empty($this->layoutTemplateName)) {
                $layout = $this->template->makeParser();
                $layout->section = array_merge($this->section, ['content' => $content]);
                $content = $layout->render($this->layoutTemplateName, $this->layoutTemplateData);
            }
            return $content;
        } catch (CriticalException $e) {
            if (ob_get_length() > 0) {
                ob_end_clean();
            }
            throw $e;
        }
    }

}