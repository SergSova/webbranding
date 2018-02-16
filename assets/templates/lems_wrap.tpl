<div class="lems-wrap">
    <div class="lems">
        <?php if (count($res_obj->lems)): ?>
            <h3>Лемы</h3>
            <?php foreach ($res_obj->lems as $word => $lem) : ?>
                <?php include 'assets/templates/lems.tpl' ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="geo-lems">
        <?php if (count($res_obj->geo)): ?>
            <h3>Гео-лемы</h3>
            <?php foreach ($res_obj->geo as $word => $lem) : ?>
                <?php include 'assets/templates/lems.tpl' ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>