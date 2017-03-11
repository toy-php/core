<?php

namespace Web;

use Core\Application as BaseApplication;
use Core\Exceptions\CriticalException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Web\Http\Response;
use Web\Http\ServerRequest;
use Web\Http\Stream;
use Web\Http\Uri;

class Application extends BaseApplication
{

    /**
     * Работа ядра в режиме продакшн
     */
    const MODE_PROD = 'production';

    /**
     * Работа ядра в режиме разработки
     */
    const MODE_DEV = 'dev';

    /**
     * Протокол ответа по умолчанию
     * @var string
     */
    protected $responseProtocol = '1.1';

    /**
     * Статус код ответа по умолчанию
     * @var int
     */
    protected $responseStatusCode = 200;

    /**
     * Заголовки ответа по умолчанию
     * @var array
     */
    protected $responseHeaders = [];

    /**
     * Тип запроса
     * @var string
     */
    protected $requestType = '';

    /**
     * Объект запроса
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * Объект ответа
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Application constructor.
     * @param array $config
     *
     * Параметры конфигурации
     * [
     * // Режим работы ядра
     * 'mode' => string
     *
     * // Объект запроса, по умолчанию реализующий интерфей psr 7
     * 'request' => object
     *
     *  // Объект ответа, по умолчанию реализующий интерфей psr 7
     * 'response' => object
     *
     *  // Объект маршрутизатора
     * 'router' => object
     *
     * // Маршруты
     * 'routs' => [
     *      // Шаблон и его обработчки
     *      'pattern' => callable
     *      // Маршруты можно группировать
     *      'group' => [
     *          'pattern' => callable
     *          ]
     *      ]
     *
     * // Шаблон суффикса ЧПУ
     * 'friendly_url_suffix' => string
     *
     * // Обработчик исключений
     * 'exceptions_handler' => object
     *
     * // Папка шаблонов обработчика критических ошибок
     * 'critical_error_template_dir' => string
     *
     * // Имя шаблона обработчика критических ошибок
     * 'critical_error_template_name' => string
     *
     * // Папка шаблонов обработчика http ошибок
     * 'http_error_template_dir' => string
     *
     * // Имя шаблона обработчика http ошибок
     * 'http_error_template_name' => string
     *
     * // Папка вспомогательных файлов обработчика ошибок
     * 'assets_error_template_dir' => string
     *
     * ]
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        /*
         * Через конфигурацию можно определить режим работы ядра
         * По умолчанию ядра работает в режиме разработки
         */
        $this->mode = $this->get('mode', static::MODE_DEV);

        // Через конфигурацию можно задать сторониие библиотеки для обработки ответов, запросов и маршрутов
        $this->request = $this->get('request', $this->createRequest());
        $this->response = $this->get('response', $this->createResponse());
        $this->router = $this->get('router', new Router());

        // Через конфигурацию можно задать сторониий обработчик исключений
        $this->exceptionHandler = $this->get('exceptions_handler', new ExceptionsHandler($this));
    }

    /**
     * Получить объект запроса
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Получить объект ответа
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Конфигурирование Uri
     * @return UriInterface
     * @throws CriticalException
     */
    public function createUri()
    {
        $uri = $this->get('uri');
        if (!empty($uri)) {
            if (!$uri instanceof UriInterface) {
                throw new CriticalException('Объект не реализует необходимый интерфейс');
            }
            return $uri;
        }
        $uri = new Uri();
        return $uri->withHost(filter_input(INPUT_SERVER, 'HTTP_HOST'))
            ->withScheme(trim(filter_input(INPUT_SERVER, 'HTTPS')) ? 'https' : 'http')
            ->withPath(\parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'))['path'])
            ->withPort(filter_input(INPUT_SERVER, 'SERVER_PORT'))
            ->withQuery(filter_input(INPUT_SERVER, 'QUERY_STRING'));
    }

    /**
     * Запуск приложения,
     * Если используются сторониие объекты запроса и ответа -
     * данные метод необходимо переопределить
     */
    public function run()
    {
        $response = parent::run();
        if (!$response instanceof ResponseInterface) {
            throw new CriticalException(
                'Обработчик маршрута не вернул объект реализующий необходимый интерфейс'
            );
        }
        $this->respond($response);
    }

    /**
     * Вывод подготовленного ответа
     * @param ResponseInterface $response
     */
    public function respond(ResponseInterface $response)
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
     * Конфигурирование ответа
     * @return ResponseInterface
     */
    private function createResponse()
    {
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
    }

    /**
     * Конфигурирование запроса
     * @return ServerRequestInterface
     */
    private function createRequest()
    {
        /** @var ServerRequestInterface $request */
        $request = new ServerRequest($_GET, $_POST, $_FILES, $_COOKIE, $_SERVER);
        $headers = $this->getHeaders();
        if (!empty($headers)) {
            foreach ($headers as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }
        $request = $request
            ->withMethod(filter_input(INPUT_SERVER, 'REQUEST_METHOD'))
            ->withUri($this->createUri())
            ->withBody(new Stream(fopen('php://input', 'r')));
        return $request;
    }

    /**
     * Получение заголовков запроса
     * @return array|false
     */
    private function getHeaders()
    {
        if (!function_exists('apache_request_headers')) {
            $arh = array();
            $rx_http = '/\AHTTP_/';
            foreach ($_SERVER as $key => $val) {
                if (preg_match($rx_http, $key)) {
                    $arh_key = preg_replace($rx_http, '', $key);
                    $rx_matches = explode('_', $arh_key);
                    if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                        foreach ($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                        $arh_key = implode('-', $rx_matches);
                    }
                    $arh[$arh_key] = $val;
                }
            }
            return ($arh);
        }
        return apache_request_headers();
    }
}