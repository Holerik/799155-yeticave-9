<?php
$error = "";

function format_price($price) {
    $result = "";
    $price = ceil($price);
    if ($price > 1000) {
        $result = number_format($price, 0, ".", " ");
    }
    else {
        $result = $price;
    }
    $result .= " ₽";
    return $result;
}

function remained_time() {
    $retVal = [];
    $cur_date = date_parse(date('H:i:s d.m.Y'));
    $hour = $cur_date['hour'];
    $minute = $cur_date['minute'];
    $maxHour = 24;
    if ($minute > 0) {
        $maxHour--;
        $minute = 60 - $minute;
    }
    $hour = $maxHour - $hour;
    $retVal = [$hour, $minute];
    return $retVal;
}

function get_min_rate($dblink, $lot_id) {
    $min_rate = 0;
    $sql = "SELECT * FROM rates r WHERE r.lot_id = $lot_id";
    $result = mysqli_query($dblink, $sql);
    if ($result) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach ($rows as $row) {
            if ($min_rate < $row['price'])
            $min_rate = $row['price'];
        }
    }
    return $min_rate;
}

require_once('helpers.php');
