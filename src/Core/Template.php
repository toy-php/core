<?php

namespace Core;

use Core\Exceptions\CriticalException;
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
     * Добавить функцию
     * @param $name
     * @param callable $function
     * @throws CriticalException
     */
    public function addFunction($name, callable $function)
    {
        if ($this->dependencyContainer->offsetExists($name)){
            throw new CriticalException('Функция с таким именем уже зарегистрированна');
        }
        $this->dependencyContainer[$name] = $this->dependencyContainer->factory($function);
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