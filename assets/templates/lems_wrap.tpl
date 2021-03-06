<div class="lems-wrap">
    <div class="lems">
        <?php if (is_array($res_obj->lems) && count($res_obj->lems)): ?>
            <h3>Лемы</h3>
            <?php foreach ($res_obj->lems as $word => $lem) : ?>
                <div class="lem-text">
                    <div><?= $word.': '.join('#', $lem->morph) ?>(<?= $lem->count ?>)</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="geo-lems">
        <?php if (is_array($res_obj->geo) && count($res_obj->geo)): ?>
            <h3>Гео-лемы</h3>
            <?php foreach ($res_obj->geo as $word => $lem) : ?>
                <div class="lem-text">
                    <div><?= $word.': '.join('#', $lem->morph) ?>(<?= $lem->count ?>)</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>