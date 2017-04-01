<?php

namespace Core\App\Handlers;

use Core\App\Commands\Dispatch;
use Core\Bus\Interfaces\CommandHandler;
use Core\Bus\Interfaces\Message;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher implements CommandHandler
{
    /**
     * Вывод подготовленного ответа
     * @param ResponseInterface $response
     */
    private function respond(ResponseInterface $response)
    {
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }
        $content = $response->getBody()->getContents();
        file_put_contents('php://output', $content);
    }

    /**
     * Установка атрибутов запроса
     * @param ServerRequestInterface $request
     * @param array $attributes
     * @return ServerRequestInterface
     */
    private function addAttributes(ServerRequestInterface $request, array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }
        return $request;
    }

    /**
     * Обработать команду
     * @param Message $message
     * @return boolean
     */
    public function handle(Message $message)
    {
        /** @var Dispatch $message */
        $handler = $message->getHandler();
        $request = $this->addAttributes($message->getRequest(), $message->getMatches());
        $response = $message->getResponse();
        $this->respond($handler($request, $response));
        return true;
    }
}