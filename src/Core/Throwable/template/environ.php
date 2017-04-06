<?php
/**
 * @var \Core\Template\Parser $this
 * @var \Psr\Http\Message\ServerRequestInterface $request
 */
$request = $this->request;
?>
<p><b>Серверное окружение:</b></p>

<div class="panel panel-default">
    <div class="panel-heading trace">
        <a class="btn-block" data-toggle="collapse" data-target="#panel_headers">
            Заголовки
        </a>
    </div>

    <div class="panel-body collapse" id="panel_headers">
        <?php foreach ($request->getHeaders() as $key => $headers): ?>
            <div class="row">
                <div class="col-md-4 clamp"><p><?= $key ?></p></div>
                <div class="col-md-8">
                    <?php foreach ($headers as $header): ?>
                        <p><?= $header ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading trace">
        <a class="btn-block" data-toggle="collapse" data-target="#panel_server">
            $_SERVER
        </a>
    </div>

    <div class="panel-body collapse" id="panel_server">
        <?php foreach ($request->getServerParams() as $key => $value): ?>
            <div class="row">
                <div class="col-md-4 clamp"><p><?= $key ?></p></div>
                <div class="col-md-8"><p><?= $value ?></p></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading trace">
        <a class="btn-block" data-toggle="collapse" data-target="#panel_get">
            $_GET
        </a>
    </div>

    <div class="panel-body collapse" id="panel_get">
        <?php foreach ($request->getQueryParams() as $key => $value): ?>
            <div class="row">
                <div class="col-md-4 clamp"><p><?= $key ?></p></div>
                <div class="col-md-8"><p><?= $value ?></p></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading trace">
        <a class="btn-block" data-toggle="collapse" data-target="#panel_post">
            $_POST
        </a>
    </div>

    <div class="panel-body collapse" id="panel_post">
        <?php foreach ($request->getParsedBody() as $key => $value): ?>
            <div class="row">
                <div class="col-md-4 clamp"><p><?= $key ?></p></div>
                <div class="col-md-8"><p><?= $value ?></p></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading trace">
        <a class="btn-block" data-toggle="collapse" data-target="#panel_cookie">
            $_COOKIE
        </a>
    </div>

    <div class="panel-body collapse" id="panel_cookie">
        <?php foreach ($request->getCookieParams() as $key => $value): ?>
            <div class="row">
                <div class="col-md-4 clamp"><p><?= $key ?></p></div>
                <div class="col-md-8"><p><?= $value ?></p></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


