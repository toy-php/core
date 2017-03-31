<?php

namespace Core\App\Handlers;

use Core\App\Queries\View;
use Core\Bus\Interfaces\Message;
use Core\Bus\Interfaces\QueryHandler;
use Core\Container;

class ViewHandler extends Container implements QueryHandler
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

    public function __construct()
    {
        parent::__construct([]);
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
     * Обработать сообщение определенного типа
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message)
    {
        /** @var View $message */
        $this->templateDir = $message->getTemplateDir();
        $templateName = $message->getTemplateName();
        $this->templateExt = $message->getTemplateExt();
        $data = $message->getData();
        $extends = $message->getExtends();
        $this->values->exchangeArray($extends);
        return $this->make()->render($templateName, $data);
    }
}