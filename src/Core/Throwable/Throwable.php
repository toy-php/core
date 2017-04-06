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

    public function getChunkCode($fileName, $startRow, $numRows)
    {
        if (!file_exists($fileName)) {
            return false;
        }
        $arrayLines = (array)file($fileName);
        array_unshift($arrayLines, '');
        $startRow = (count($arrayLines) > $numRows)
            ? ($startRow - ceil($numRows / 2)) + 1
            : 1;
        return array_slice($arrayLines, $startRow, $numRows, true);
    }

    public function handle(\Throwable $throwable)
    {
        $templateConfig['dir'] = __DIR__ . '/template/';
        $templateConfig['functions'] = [
            'getChunkCode' => [$this, 'getChunkCode'],
            'clean' => function ($string) {
                return filter_var($string, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            },
        ];
        $template = $this->application->getTemplate($templateConfig);
        $assets = str_replace($_SERVER['DOCUMENT_ROOT'], "", __DIR__) . '/template/';
        echo $template->render(
            'error',
            [
                'assets' => $assets,
                'request' => $this->application->getRequest(),
                'message' => $throwable->getMessage(),
                'code' => $throwable->getCode(),
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'previous' => $throwable->getPrevious(),
                'trace' => $throwable->getTrace()
            ]
        );
    }
}