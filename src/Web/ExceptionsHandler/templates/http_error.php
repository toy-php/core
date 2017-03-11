<?php
/**
 * @var \Web\Parser|\Web\ExceptionsHandler\Models\Error $this
 */
?>
<?php $this->layout('http_error_layout') ?>
<div class="container">
    <div class="header">
        <h1 class="text-danger text-center">Код ошибки: <?= $this->code?></h1>
        <h3 class="text-center"><?= $this->message ?></h3>
    </div>
</div>