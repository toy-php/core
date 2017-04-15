<?php
/**
 * @var \Template\Parser|\Core\Throwable\ViewModel $this
 */
?>

<?php $this->layout('error_layout', new \Core\Throwable\ViewModel(['assets' => $this->assets])) ?>
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
                    <?= $this->file ?> <b>#<?= $this->line ?></b>
                </div>
                <div class="panel-body">
                    <?= $this->insert('code', new \Core\Throwable\ViewModel([
                        'chunk' => $this->getChunkCode($this->file, $this->line, 10),
                        'line' => $this->line
                    ])); ?>
                </div>
            </div>

            <p><b>Трассировка:</b></p>
            <?php foreach ($this->trace as $key => $error): ?>
                <div class="panel panel-default">

                    <div class="panel-heading trace">
                        <a class="btn-block" data-toggle="collapse" data-target="#panel_<?= $key ?>">
                            <?= $error['file'] ?> <b>#<?= $error['line'] ?></b>
                        </a>
                    </div>

                    <div class="panel-body collapse" id="panel_<?= $key ?>">
                        <?= $this->insert('code', new \Core\Throwable\ViewModel([
                            'chunk' => $this->getChunkCode($error['file'], $error['line'], 10),
                            'line' => $error['line']
                        ])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-md-6">
            <?= $this->insert('environ', new \Core\Throwable\ViewModel(['request' => $this->request])); ?>
        </div>

    </div>
</div>
