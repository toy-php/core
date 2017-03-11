<?php

namespace Web;

use Web\Exceptions\HttpErrorException;
use Web\ExceptionsHandler\Models\Error;
use Web\ExceptionsHandler\Models\Trace;

class ExceptionsHandler extends \Core\ExceptionsHandler
{

    /**
     * @var \Web\Application
     */
    protected $application;

    public function getRoutsHandlers()
    {
        return [
            HttpErrorException::class => [$this, 'handleHttpException'],
            \Throwable::class => [$this, 'handleCriticalException']
        ];
    }

    private function buildErrorModel(\Throwable $exception)
    {
        $message = $exception->getMessage();
        $code = $exception->getCode();
        $fileName = $exception->getFile();
        $lineError = $exception->getLine();
        $error = new Error($fileName, $lineError, $message, $code);
        $error->trace = new Trace();
        $error->mode = $this->application->getMode();
        $trace = $exception->getTrace();
        foreach ($trace as $item) {
            $error->trace[] = new Error($item['file'], $item['line']);
        }
        return $error;
    }

    private function buildTemplate($prefix)
    {
        $templateDir = $this->application->get($prefix . 'error_template_dir', __DIR__ . '/ExceptionsHandler/templates/');
        $templateName = $this->application->get($prefix . 'error_template_name', $prefix . 'error');
        $template = new Template($templateDir, $templateName);
        $template['assets'] = $this->application->get('assets_error_template_dir',
            str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__) . '/ExceptionsHandler/templates/');
        $template['clean'] = $template->factory(function () {
            return function ($string) {
                return filter_var($string, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            };
        });
        $template['request'] = $this->application->getRequest();
        return $template;
    }

    public function handleHttpException(\Throwable $exception)
    {
        $template = $this->buildTemplate('http_');
        $error = $this->buildErrorModel($exception);
        $response = $this->application->getResponse();
        $response->getBody()->write($template->convert($error));
        $this->application->respond($response->withStatus($error->code));
    }

    public function handleCriticalException(\Throwable $exception)
    {
        $template = $this->buildTemplate('critical_');
        $error = $this->buildErrorModel($exception);
        $response = $this->application->getResponse();
        $response->getBody()->write($template->convert($error));
        $this->application->respond($response->withStatus(500));
    }

}