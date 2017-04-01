<?php

namespace Core\App\Handlers;

use Core\App\Queries\GetThrowable;
use Core\Bus\Interfaces\Message;
use Core\Bus\Interfaces\QueryHandler;

class ThrowableHandler implements QueryHandler
{

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

    /**
     * Обработать событие
     * @param Message $message
     * @return void
     */
    public function handle(Message $message)
    {
        /** @var GetThrowable $message */
        $template = $message->getTemplate();
        $template->addFunction('getChunkCode', function () {
            return [$this, 'getChunkCode'];
        });
        $template->addFunction('clean', function () {
            return function ($string) {
                return filter_var($string, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            };
        });
        $exception = $message->getThrowable();
        $assets = str_replace($_SERVER['DOCUMENT_ROOT'], "", __DIR__) . '/throwable_template/';
        echo $template->render(
            __DIR__ . '/throwable_template',
            'error',
            [
                'assets' => $assets,
                'request' => $message->getRequest(),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'previous' => $exception->getPrevious(),
                'trace' => $exception->getTrace()
            ]
        );
    }
}