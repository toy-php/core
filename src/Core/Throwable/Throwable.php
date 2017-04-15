<?php

namespace Core\Throwable;

use Psr\Http\Message\ServerRequestInterface;
use Template\Interfaces\View;

class Throwable
{

    protected $request;
    protected $view;

    public function __construct(ServerRequestInterface $request, View $view)
    {
        $this->view = $view;
        $this->request = $request;
    }

    public function __invoke(\Throwable $throwable)
    {
        $assets = str_replace($_SERVER['DOCUMENT_ROOT'], "", __DIR__) . '/template/';
        echo $this->view->render(
            'error',
            new ViewModel([
                'assets' => $assets,
                'request' => $this->request,
                'message' => $throwable->getMessage(),
                'code' => $throwable->getCode(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'previous' => $throwable->getPrevious(),
                'trace' => $throwable->getTrace()
            ])
        );
    }
}