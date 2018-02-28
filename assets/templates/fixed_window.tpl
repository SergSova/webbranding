<?php if ($is_geo_data): ?>
    <div class="window-fixed">
        <h4>Гео данные</h4>
        <hr>
        <div class="geo-rem">
            <?php $i = 1;
                foreach ($filter_obj->geo_location->words as $eid => $item) : ?>
                    <p><span><?= $i++ ?></span> <?= $item ?> <a href="" data-id="<?= $eid ?>">delete</a></p>
                <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>