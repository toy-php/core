<?php

namespace Core\Template\Handlers;

use Core\Template\Queries\View;
use Core\Bus\Interfaces\Message;
use Core\Bus\Interfaces\QueryHandler;

class ViewHandler implements QueryHandler
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
     * @var \ArrayAccess;
     */
    protected $extends;

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
     * @return \ArrayAccess
     */
    public function getExtends()
    {
        return $this->extends;
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
     * Обработать сообщение определенного типа
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message)
    {
        /** @var View $message */
        $this->templateDir = $message->getTemplateDir();
        $this->templateExt = $message->getTemplateExt();
        $this->extends = $message->getExtends();
        return $this->make()->render($message->getTemplateName(), $message->getData());
    }
}