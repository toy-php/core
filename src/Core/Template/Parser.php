<?php

namespace Core\Template;

use Core\Exceptions\CriticalException;

class Parser
{

    /**
     * @var Template
     */
    protected $template;

    /**
     * Модель представления
     * @var ViewModel
     */
    protected $model = null;

    /**
     * Имя макета шаблона
     * @var string
     */
    protected $layoutTemplateName = '';

    /**
     * Модель макета шаблона
     * @var ViewModel
     */
    protected $layoutModel = null;

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
        return (!empty($this->model) and isset($this->model->$name))
            ? $this->model->$name
            : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return (!empty($this->model) and isset($this->model->$name));
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws CriticalException
     */
    public function __call($name, $arguments)
    {
        if (!empty($this->model) and method_exists($this->model, $name)) {
              return $this->model->$name(...$arguments);
        }
        throw new CriticalException('Метод недоступен');
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
     * @param $layoutModel
     */
    public function layout($layoutTemplateName, ViewModel $layoutModel = null)
    {
        $this->layoutTemplateName = $layoutTemplateName;
        $this->layoutModel = $layoutModel ?: $this->model;
    }

    /**
     * Вставка представления в текущий шаблон
     * @param $templateName
     * @param ViewModel $viewModel
     * @return string
     */
    public function insert($templateName, ViewModel $viewModel = null)
    {
        return $this->template->makeParser()
            ->render($templateName, $viewModel ?: $this->model);
    }

    /**
     * Загрузка шаблона
     * @param $templateName
     * @throws CriticalException
     */
    private function loadTemplateFile($templateName)
    {
        $fileName = $this->template->getTemplateDir() .
            $templateName .
            $this->template->getTemplateExt();
        if (!file_exists($fileName)) {
            throw new CriticalException('Файл шаблона "' . $fileName . '" не может быть загружен');
        }
        include $fileName;
    }

    /**
     * Рендеринг шаблона
     * @param $templateName
     * @param $viewModel
     * @return string
     * @throws CriticalException
     */
    public function render($templateName, ViewModel $viewModel = null)
    {
        try {
            ob_start();
            $this->model = $viewModel;
            $this->loadTemplateFile($templateName);
            $content = ob_get_contents();
            ob_end_clean();
            if (!empty($this->layoutTemplateName)) {
                $layout = $this->template->makeParser();
                $layout->section = array_merge($this->section, ['content' => $content]);
                $content = $layout->render($this->layoutTemplateName, $this->layoutModel);
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