<?php
/**
 * @var \Template\Parser|\Core\Throwable\ViewModel $this
 */

?>

<pre class="line-numbers" data-start="<?= array_keys($this->chunk)[0] ?>" data-line="<?= array_search($this->chunk[$this->line], array_values($this->chunk)) ?>" data-line-offset="-1">
<code class="language-php"><?php foreach ($this->chunk as $fileString): ?><?= $this->clean($fileString) ?><?php endforeach; ?></code>
</pre>