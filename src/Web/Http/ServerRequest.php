<?php

namespace Web\Http;

use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{

    protected $serverParams = [];
    protected $cookieParams = [];
    protected $queryParams = [];
    protected $uploadedFiles = [];
    protected $parsedBody = [];
    protected $attributes = [];

    function __construct(array $get,
                         array $post,
                         array $files,
                         array $cookie,
                         array $server)
    {
        $this->queryParams = $get;
        $this->parsedBody = $post;
        $this->uploadedFiles = $files;
        $this->cookieParams = $cookie;
        $this->serverParams = $server;
    }

    /**
     * @inheritdoc
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @inheritdoc
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @inheritdoc
     */
    public function withCookieParams(array $cookies)
    {
        if ($this->cookieParams === $cookies) {
            return $this;
        }
        $instance = clone $this;
        $instance->cookieParams = $cookies;
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @inheritdoc
     */
    public function withQueryParams(array $query)
    {
        if ($this->queryParams === $query) {
            return $this;
        }
        $instance = clone $this;
        $instance->queryParams = $query;
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @inheritdoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        if ($this->uploadedFiles === $uploadedFiles) {
            return $this;
        }
        $instance = clone $this;
        $instance->uploadedFiles = $uploadedFiles;
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        if ($this->parsedBody === $data) {
            return $this;
        }
        $instance = clone $this;
        $instance->parsedBody = $data;
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($name, $default = null)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $default;
        }
        return $this->attributes[$name];
    }

    /**
     * @inheritdoc
     */
    public function withAttribute($name, $value)
    {
        $instance = clone $this;
        $instance->attributes[$name] = $value;
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function withoutAttribute($name)
    {
        if (false === isset($this->attributes[$name])) {
            return clone $this;
        }
        $new = clone $this;
        unset($new->attributes[$name]);
        return $new;
    }
}
