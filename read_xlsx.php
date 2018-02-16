<?php
    /**
     * Created by PhpStorm.
     * User: Chepur
     * Date: 09.01.2018
     * Time: 13:38
     *
     * @var $lim int
     * @var $off int
     */
    session_start();
    require_once 'lib/dicts/simplexlsx.class.php';
    $zip = new ZipArchive();
    $base_dir = 'work_dir';
    $files = scandir($base_dir);
    $_SESSION['is_change'] = false;
    if (!isset($index)) {
        return array();
    }
    $output = array();
    if ($files) {
        foreach ($files as $name) {
            if (!in_array($name, array(".", ".."))) {
                $name = iconv("cp1251", "UTF-8", $name);
                $f_name = $base_dir.DIRECTORY_SEPARATOR.$name;
                if (isset($_SESSION['file']) && array_key_exists($name, $_SESSION['file']) && $file = $_SESSION['file'][$name] && $file['file_size'] != filesize($f_name)) {
                    $output = array_merge(
                        $output,
                        $_SESSION['file'][$name]['out']
                    );
                } else {
                    if ($zip->open($f_name) === true) {
                        $extract = $zip->extractTo($base_dir.DIRECTORY_SEPARATOR.'temp');
                        $zip->close();
                        if ($extract) {

                            $xml = simplexml_load_file($base_dir.DIRECTORY_SEPARATOR.'temp/xl/sharedStrings.xml');
                            $sharedStringsArr = array();
                            foreach ($xml->children() as $item) {
                                $sharedStringsArr[] = (string)$item->t;
                            }
                            $handle = @opendir($base_dir.DIRECTORY_SEPARATOR.'temp/xl/worksheets');
                            $out = array();
                            while ($file = @readdir($handle)) {
                                //проходим по всем файлам из директории /xl/worksheets/
                                if ($file != "." && $file != ".." && $file != '_rels') {
                                    $xml = simplexml_load_file($base_dir.DIRECTORY_SEPARATOR.'temp/xl/worksheets/'.$file);
                                    //по каждой строке
                                    $row = 0;
                                    foreach ($xml->sheetData->row as $item) {
                                        $out[$file][$row] = array();
                                        //по каждой ячейке строки
                                        $cell = 0;
                                        foreach ($item as $child) {
                                            $attr = $child->attributes();
                                            $value = isset($child->v) ? (string)$child->v : false;
                                            $out[$file][$row][$cell] = isset($attr['t']) ? $sharedStringsArr[$value] : $value;
                                            $cell++;
                                        }
                                        $row++;
                                    }
                                }
                            }

                            $res = array_map(
                                function ($el) use ($index) {
                                    return $el[$index];
                                },
                                array_shift($out)
                            );
                            $_SESSION['file'][$name] = ['file_size' => filesize($f_name), 'out' => $res];
                            $output = array_merge($output, $res);
                            $_SESSION['is_change'] = true;
                        }
                    }
                }
            }
        }
    }

    $response = array('count' => count($output) - 1);
    $_SESSION['text'] = json_encode(array_slice($output, 1));
    unset($_SESSION['file']);
    unset($_SESSION['file_size']);

    return $response;