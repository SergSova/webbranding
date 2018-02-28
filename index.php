<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 20.12.2017
     * Time: 13:16
     */
    namespace models;

    use Exception;
    use phpMorphy;

    session_start();
    //    session_unset();
    mb_internal_encoding("UTF-8");

    error_reporting(E_ALL & ~(E_DEPRECATED | E_NOTICE));

    require_once 'autoloder.php';
    require_once('lib/phpmorphy-0.3.7/src/common.php');
    $dir = 'lib/dicts';
    $lang = 'ru_RU';
    $opts = array(
        'storage' => PHPMORPHY_STORAGE_FILE,
    );

    $site_name = 'WebBranding';
    $files = new ReadFile();

    try {
        $morphy = new phpMorphy($dir, $lang, $opts);
        $filter_obj = new Filter();

        switch ($_POST['working']) {
            case 'Work':
                $res_obj = new Result();
                if ($_POST['is_last'] == 'false') {
                    $files = new ReadFile();
                    $texts = $files->getResult();
                    $arr = array_slice($texts, $_POST['offset'], $_POST['limit']);
                } else {
                    die($res_obj->toJSON());
                }

                foreach ($arr as $id => $text) {
                    $arr1 = array_unique(preg_split("/[\s#,.\«»\[\]()]+/u", $text));
                    foreach ($arr1 as $item) {
                        if ($item == '' || is_numeric($item)) {
                            continue;
                        }

                        $item = strip_tags($item);
                        $morph = $morphy->lemmatize(mb_strtoupper($item));
                        $word = ($morph && count($morph) == 1) ? mb_strtolower($morph[0]) : mb_strtolower($item);

                        if ($filter_obj->filteredWord($word)) {
                            continue;
                        }

                        if ($filter_obj->isGeo($word)) {
                            $geo_obj = $res_obj->updGeo($word, $morph, $text);
                            $res_obj->update_Geo($geo_obj);
                        } else {
                            $lem_obj = $res_obj->updLem($word, $morph, $text);
                            $res_obj->update_Lems($lem_obj);
                        }
                    }
                }

                $res_obj->saveInstance();

                /*
                 if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    return include 'assets/templates/lems_wrap.tpl';
                }
                */

                break;
            case 'Clear':
                $message = 'all Cleared';
                $m_class = 'clear';

                session_unset();
                break;
            case 'addExc':
                echo $filter_obj->excluded->insert($_POST['word']);

                return;
                break;
            case 'addGeo':
                echo $filter_obj->geo_location->insert($_POST['word']);

                return;
                break;
            default:
                $title = 'Choose ';
        }
    } catch (Exception $e) {
        die('Error occured while creating phpMorphy instance: '.$e->getMessage());
    }

?>
<title><?= ($title ? $title.' | ' : '').$site_name ?></title>
<link rel="stylesheet" href="assets/css/main.css">
<?php include 'assets/templates/fixed_window.tpl' ?>
<div class="container">
    <div class="message-wrap">
        <p class="message <?= $m_class ?>"><?= $message ?></p>
    </div>
    <div class="filter-wrap">
        <form>
            <div>
                <h2>Не показывать слова</h2>
                <div class="excluded-rem">
                    <?php foreach ($filter_obj->excluded->words as $eid => $item) : ?>
                        <div>
                            <input class="excluded_input" id="ex_<?= $eid ?>" name="excluded_words[<?= $eid ?>]" type="checkbox">
                            <label for="ex_<?= $eid ?>"><?= $item ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div>
                <h2>Гео данные <span><input class="geo_slect" type="checkbox" name="is_geo_data" checked></span></h2>
                <div class="geo-wrap">
                    <?php foreach ($filter_obj->getGeoFilter() as $g_id => $g_item): ?>
                        <div>
                            <label for="geo_reg_<?= $g_id ?>"><?= $filter_obj->geo_location->words[$g_id] ?></label>
                            <input class="geo_reg_input" id="geo_reg_<?= $g_id ?>" name="include_geo[<?= $g_id ?>]" type="checkbox">
                            <div class="region-wrap">
                                <?php foreach ($g_item as $item): ?>
                                    <input class="geo_input" id="geo_<?= $item ?>" name="include_geo[<?= $item ?>]" type="checkbox">
                                    <label for="geo_<?= $item ?>"><?= $filter_obj->geo_location->words[$item] ?></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>
    </div>
    <form action="" method="post">
        <input class="work-btn" type="submit" name="working" value="Work">
        <input class="clear-btn" type="submit" name="working" value="Clear">
        <input class="stop-btn" type="button" value="Stop">
        <p><label>
                <input type="checkbox" name="saved" <?= $_POST['saved'] == 1 ? 'checked' : '' ?> >
                Записать в базу</label>
        </p>
    </form>
    <div class="lems-wrap">
        <div class="lems"></div>
        <div class="geo-lems"></div>
    </div>
</div>
<script src="assets/js/lib/jquery.min.js"></script>
<script>
    textCount = 200; //<?= $files->getCount()?>;
</script>
<script src="assets/js/main.js"></script>