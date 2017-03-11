<?php
/**
 * @var \Web\Parser|\Web\ExceptionsHandler\Models\Error $this
 */
?>

<?php if ($this->mode == \Web\Application::MODE_DEV): ?>
    <?php $this->layout('critical_error_layout') ?>
    <div class="container">
        <div class="header">
            <h1 class="text-danger text-center">Код ошибки: <?= $this->code ?></h1>
            <h3 class="text-center"><?= $this->message ?></h3>
        </div>
        <div class="content">
            <div class="col-md-6">
                <p><b>Место возникновения ошибки:</b></p>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?= $this->fileName ?> <b>#<?= $this->lineError ?></b>
                    </div>
                    <div class="panel-body">
                        <?= $this->insert('code', $this); ?>
                    </div>
                </div>

                <p><b>Трассировка:</b></p>
                <?php foreach ($this->trace as $key => $error): ?>
                    <div class="panel panel-default">

                        <div class="panel-heading trace">
                            <a class="btn-block" data-toggle="collapse" data-target="#panel_<?= $key ?>">
                                <?= $error->fileName ?> <b>#<?= $error->lineError ?></b>
                            </a>
                        </div>

                        <div class="panel-body collapse" id="panel_<?= $key ?>">
                            <?= $this->insert('code', $error); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-6">
                <?= $this->insert('environ'); ?>
            </div>

        </div>
    </div>
<?php elseif ($this->mode == \Web\Application::MODE_PROD): ?>
    <?php $this->insert('http_error', [
        'code' => 500,
        'message' => 'Критическая ошибка'
    ]) ?>
<?php endif; ?>
