<?php

namespace Core\Modules;

use Core\WebApp;
use Core\Module;
use Http\Response;
use Http\Stream;
use Psr\Http\Message\ResponseInterface;

class ResponseModule implements Module
{

    protected $responseProtocol;
    protected $responseStatusCode;
    protected $responseHeaders;

    public function __construct(
        $responseProtocol = '1.1',
        $responseStatusCode = 200,
        $responseHeaders = []
    )
    {
        $this->responseProtocol = $responseProtocol;
        $this->responseStatusCode = $responseStatusCode;
        $this->responseHeaders = $responseHeaders;
    }

    /**
     * Регистрация модуля в ядре
     * @param WebApp $app
     * @return void
     */
    public function register(WebApp $app)
    {
        $app['response'] = function () {
            /** @var ResponseInterface $response */
            $response = (new Response())
                ->withBody(new Stream(fopen('php://memory', 'a')))
                ->withProtocolVersion($this->responseProtocol)
                ->withStatus($this->responseStatusCode);
            if (!empty($this->responseHeaders)) {
                foreach ($this->responseHeaders as $name => $value) {
                    $response = $response->withHeader($name, $value);
                }
            }
            return $response;
        };
    }
}