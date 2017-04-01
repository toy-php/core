<?php

namespace Core\App\Handlers;

use Core\App\Http\Response;
use Core\App\Http\Stream;
use Core\App\Queries\GetHttpResponse;
use Core\Bus\Interfaces\Message;
use Core\Bus\Interfaces\QueryHandler;
use Psr\Http\Message\ResponseInterface;

class HttpResponse implements QueryHandler
{

    protected static $response;

    /**
     * Конфигурирование ответа
     * @param string $responseProtocol
     * @param string $responseStatusCode
     * @param array $responseHeaders
     * @return ResponseInterface
     */
    private function buildResponse($responseProtocol, $responseStatusCode, array $responseHeaders)
    {
        /** @var ResponseInterface $response */
        $response = (new Response())
            ->withBody(new Stream(fopen('php://memory', 'a')))
            ->withProtocolVersion($responseProtocol)
            ->withStatus($responseStatusCode);
        if (!empty($responseHeaders)) {
            foreach ($responseHeaders as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }
        return $response;
    }

    /**
     * Обработать запрос
     * @param Message $message
     * @return mixed
     */
    public function handle(Message $message)
    {
        /** @var GetHttpResponse $message */
        $responseProtocol = $message->getResponseProtocol();
        $responseStatusCode = $message->getResponseStatusCode();
        $responseHeaders = $message->getResponseHeaders();
        return !empty(static::$response)
            ? static::$response
            : static::$response = $this->buildResponse(
                $responseProtocol,
                $responseStatusCode,
                $responseHeaders
            );
    }
}