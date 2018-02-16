<?php
    /**
     * @var $lem  Lems
     * @var $word string
     */
?>
<div class="lem-text">
    <div><?= $word.': '.join('#', $lem->morph) ?>(<?= $lem->count ?>)</div>
</div>