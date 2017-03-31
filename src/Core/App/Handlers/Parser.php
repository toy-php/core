<?php

namespace Core\App\Handlers;

use Core\Exceptions\CriticalException;

class Parser implements \ArrayAccess
{

    /**
     * @var ViewHandler
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

    public function __construct(ViewHandler $template)
    {
        $this->template = $template;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (is_object($this->templateData)) {
            return !empty($this->templateData) ? $this->templateData->$name : null;
        }

        if (is_array($this->templateData)) {
            return isset($this->templateData[$name]) ? $this->templateData[$name] : null;
        }
        return null;
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
        return $this->template->make()->render($templateName, $templateData);
    }

    /**
     * Загрузка шаблона
     * @param $templateName
     * @throws CriticalException
     */
    private function loadTemplateFile($templateName)
    {
        $fileName = $this->template->getTemplateDir() . $templateName . $this->template->getTemplateExt();
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
                $layout = $this->template->make();
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

    public function offsetExists($offset)
    {
        return $this->template->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->template->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new CriticalException('Из шаблона невозможно изменить или добавить параметр');
    }

    public function offsetUnset($offset)
    {
        throw new CriticalException('Из шаблона невозможно удалить параметр');
    }

}