<?php
/**
 * @var \Web\Parser|\Web\ExceptionsHandler\Models\Error $this
 */
?>

<pre class="line-numbers" data-start="<?= array_keys($this->chunkCode)[0] ?>" data-line="<?= array_search($this->chunkCode[$this->lineError], array_values($this->chunkCode)) ?>" data-line-offset="-1">
<code class="language-php"><?php foreach ($this->chunkCode as $line => $fileString): ?><?= $this['clean']($fileString) ?><?php endforeach; ?></code>
</pre>