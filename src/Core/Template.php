<?php

namespace Core;

use Core\Template\Handlers\ViewHandler;
use Core\Template\Queries\View;

class Template extends Module
{

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->queryBus->addHandler(View::class, ViewHandler::class);
    }

    /**
     * Рендеринг шаблона
     * @param string $templateDir
     * @param string $templateName
     * @param array|object $data
     * @return string
     */
    public function render($templateDir, $templateName, $data)
    {
        $templateExt = '.php';
        return $this->commonBus->handle(
            new View(
                $data,
                rtrim($templateDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR,
                rtrim($templateName, $templateExt),
                $templateExt,
                $this->dependencyContainer
            )
        );
    }

}