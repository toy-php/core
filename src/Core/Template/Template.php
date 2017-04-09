<?php

namespace Core\Template;

class Template
{

    protected $templateDir;
    protected $templateExt;

    public function __construct($templateDir = '', $templateExt = '.php')
    {
        $this->templateDir = $templateDir;
        $this->templateExt = $templateExt;
    }

    /**
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * @return string
     */
    public function getTemplateExt()
    {
        return $this->templateExt;
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
     * @param ViewModel $viewModel
     * @return string
     */
    public function render($templateName, ViewModel $viewModel = null)
    {
        return $this->makeParser()->render($templateName, $viewModel);
    }
}