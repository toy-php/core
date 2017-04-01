<?php

namespace Core\App\Queries;

use Core\Bus\Interfaces\Query;
use Core\Template;
use Psr\Http\Message\ServerRequestInterface;

class GetThrowable implements Query
{

    protected $throwable;
    protected $template;
    protected $request;

    public function __construct(\Throwable $throwable, Template $template, ServerRequestInterface $request)
    {
        $this->throwable = $throwable;
        $this->template = $template;
        $this->request = $request;
    }

    /**
     * @return \Throwable
     */
    public function getThrowable()
    {
        return $this->throwable;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }


}