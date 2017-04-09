<?php

namespace Core\Throwable;

use Core\WebApplication;

class Throwable
{

    protected $application;

    public function __construct(WebApplication $application)
    {
        $this->application = $application;
    }

    public function handle(\Throwable $throwable)
    {
        $template = $this->application->getTemplate(__DIR__ . '/template/');
        $assets = str_replace($_SERVER['DOCUMENT_ROOT'], "", __DIR__) . '/template/';
        echo $template->render(
            'error',
            new ViewModel([
                'assets' => $assets,
                'request' => $this->application->getRequest(),
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