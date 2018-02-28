<div class="texts">
    <?php foreach ($arr as $id => $text) : ?>
        <p><span><?= $id ?></span>: <span><?= $text ?></span> <a href="" class="del-text" data-id="<?= $id ?>">delete</a></p>
    <?php endforeach; ?>
</div>