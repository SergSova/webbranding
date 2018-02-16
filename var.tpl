<?php

    /** @var array $arr */

    array_shift($arr);
    foreach ($arr as $item) {
        $item = explode(',', $item);
        foreach ($item as $s_item) {
            if ($s_item=='')continue;
            $lems = mb_substr($s_item, 0, -2);
            $sql = "INSERT INTO geolocation(word,lema) VALUES ('$s_item','$lems')";
            mysqli_query($link, $sql);
        }
    }
    $link->close();