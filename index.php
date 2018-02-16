<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 20.12.2017
     * Time: 13:16
     */
    session_start();
        session_reset();
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

        /**
     * @return array
     */
    function getTextArray($index = 0, $limit = 0, $offset = 0)
    {
        $lim = $limit;
        $off = $offset;
        $arr_obj = include 'read_xlsx.php';
        return $arr_obj;
    }

    try {
        $morphy = new phpMorphy($dir, $lang, $opts);
        $exceptions_ = $exclude_ = array();

//        $link = mysqli_connect($db_conf->host, $db_conf->user, $db_conf->pass, $db_conf->db_name);
//        $link->set_charset("utf8");

        $filter_obj = new Filter();

//        if ($reg_excl = $_POST[Excluded::DB_NAME]) {
//            if ($reg_excl == '#') {
//                unset($_SESSION[Excluded::DB_NAME]);
//            } else {
//                $_SESSION[Excluded::DB_NAME] = $reg_excl;
//                /*заменить значения из чекбоксов на слова*/
//                array_walk($_SESSION[Excluded::DB_NAME],
//                    function (&$item, $key) use ($filter_obj) {
//                        $item = $filter_obj->excluded->words[$key];
//                    }
//                );
//            }
//        }

        if ($reg_excl = $_POST['include_geo']) {
            if ($reg_excl == '#') {
                unset($_SESSION['include_geo']);
            } else {
                $_SESSION['include_geo'] = $_POST['include_geo'];
            }
        }

        if ($del_excl = $_POST['excl_del_id']) {
            mysqli_query($link, "DELETE FROM excluded_words WHERE id = $del_excl");
        }
        //endregion

        if ($_POST['is_geo_data']) {
            if ($_POST['is_geo_data'] == '#') {
                $_SESSION['is_geo_data'] = false;
            } else {
                $_SESSION['is_geo_data'] = $_POST['is_geo_data'];
            }
        }

        //region Select exceptions
        if ($get_except = $_POST['exception']) {
            $get_except = mb_strtolower($get_except);
            mysqli_query($link, "INSERT INTO exceptions_lems(word,lema) VALUE ('$get_except','$get_except')");
        }
        if ($del_except = $_POST['except_del_id']) {
            mysqli_query($link, "DELETE FROM exceptions_lems WHERE id = $del_except");
        }
        //endregion


        switch ($_POST['working']) {
            case 'Work':
                $texts = json_decode($_SESSION['text']);

                $res_obj = new Result();

                if ($_SESSION['res_obj']) {
                    $res_obj = unserialize($_SESSION['res_obj']);
                }

                if ($_POST['offset'] <= count($texts) && $_POST['is_last'] == 'false') {
                    $arr = array_slice($texts, $_POST['offset'], $_POST['limit']);
                } else {
                    die(json_encode($res_obj));
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

                if ($link->errno) {
                    $title = 'Error';
                    $message = $link->error;
                    $m_class = 'error';
                } else {
                    $message = 'Done';
                    $m_class = 'success';
                    $title = $site_name;
                }

                $link->close();
                $_SESSION['res_obj'] = serialize($res_obj);

                break;
            case 'Clear':
//                $link = mysqli_connect($db_conf->host, $db_conf->user, $db_conf->pass, $db_conf->db_name);
//                if (mysqli_query($link, 'CALL clear_all')) {
                $message = 'all Cleared';
                $m_class = 'clear';
                unset($_SESSION['is_geo_data']);
                unset($_SESSION['res_obj']);
                unset($_SESSION['exceptions']);
                unset($_SESSION['geolocation']);
                unset($_SESSION['excluded']);
                unset($_SESSION['undefined']);
                session_destroy();
                $arr_obj = getTextArray();
                break;
            case 'ChangeToGeo':
                $link->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
                $_word = $_POST['geoName'];
                $sql_geo = "Insert into geolocation(word) VALUE ('$_word')";
                mysqli_query($link, $sql_1);
                $geo_id = mysqli_insert_id($link);

                $sql_1 = "Select * from lems1 WHERE word='$_word'";
                $resp = mysqli_query($link, $sql_1);
                $row = $resp->fetch_assoc();

                $lem_id = $row['id'];
                $geo_lema = $row['lema'];
                $morph = $row['morph'];
                $count = $row['count'];
                $text = $row['text'];

                $sql_2 = "INSERT INTO geo1(word,lema,morph,count,text,geo_id) VALUES ('$_word','$geo_lema','$morph', $count,'$text', $geo_id)";
                mysqli_query($link, $sql_2);

                $sql_3 = "Delete from lems1 WHERE id=$lem_id";
                mysqli_query($link, $sql_3);

                $link->commit();
                break;
            default:
                $title = 'Choose ';
                $arr_obj = getTextArray();
        }
//        $link->close();
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
                    <?php foreach ($filter_obj->geo_location->getFilters() as $g_id => $g_item): ?>
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
        <!--        <p><input class="red" type="text" name="exception" placeholder="Добавить исключение"></p>-->
        <!--        <p><input type="text" name="text" placeholder="Добавить текст"></p>-->
        <input class="work-btn" type="submit" name="working" value="Work">
        <input type="submit" name="working" value="Clear">
        <input type="button" class="stop-btn" value="Stop">
        <p><input type="checkbox" name="saved" <?= $_POST['saved'] == 1 ? 'checked' : '' ?> >Записать в базу</p>

        <!--        <input type="submit" name="working" value="Clear_session">-->
    </form>
    <?php //include 'assets/templates/texts.tpl'?>

    <?php include 'assets/templates/lems_wrap.tpl' ?>
</div>
<script src="assets/js/lib/jquery.min.js"></script>
<script>
    textCount = 1000;//<?=count($texts)?>;
</script>
<script src="assets/js/main.js"></script>