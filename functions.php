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

function remained_time($dt_fin) {
    $hour = 0;
    $days = 0;
    $min = 0;
    $now = time();
    $fin = strtotime($dt_fin);
    $retVal = [];
    if ($fin > $now) { 
        $diff = $fin - $now;
        $weeks = $diff / 604800 % 52;
        $days = $diff / 86400 % 7;
        $hours = $diff / 3600 % 24;
        $mins = $diff / 60 % 60;
        $retVal = [$hours + 24 * $days + 7 * 24 * $weeks, $mins];
    } 
    return $retVal;
}

function lot_time_info($dt_add, $dt_fin)
{
	$time_info = [ 'info' => "",
				   'status' => 1	
			   ];
    $now = time();
    $add = strtotime($dt_add);
    $fin = strtotime($dt_fin);
    if ($now > $fin) {
      	$time_info['info'] = "Торги окончены";
       	$time_info['status'] = 0;
       	return $time_info;
    }
    $weeks = ($now - $add) / 604800 % 52; 
    $days = ($now - $add) / 86400 % 7 + $weeks * 7;
    $hour = ($now - $add) / 3600 % 24;
    $min = ($now - $add) / 60 % 60;
    if ($days > 2) {
      	$time_info['info'] = date("d.m.Y в H:i", $add);	
    }
    elseif ($days > 1) {
       	$time_info['info'] = date("вчера, в  H:i", $add);
    }
    elseif ($hour > 2) {
      	$time_info['info'] = date("в H:i", $add);
    }
    elseif ($hour > 1) {
       	$time_info['info'] = date("час назад");
    }
    else {
       	$time_info['info'] = $min . " минут назад";
    }
	return $time_info;
}

function strtest($str, $test)
{
	return !(strpos($str, $test) === false);
}

function lot_alt_descr($name)
{
	$result = "Разное";
	if (strtest($name, "oots") || strtest($name, "отинки")) {
		$result = "Ботинки";
	} 
	elseif (strtest($name, "nowboard") || strtest($name, "ноуборд")) {
		$result = "Сноуборд";
	}
	elseif (strtest($name, "уртка")) {
		$result = "Куртка";
	}
	elseif (strtest($name, "ыжи")) {
		$result = "Лыжи";
	}
	elseif (strtest($name, "аска")) {
		$result = "Маска";
	}
	elseif (strtest($name, "чки")) {
		$result = "Очки";
	}
	return $result;
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

function check_my_rate($dblink, $lot_id, $bet)
{
	$rate = get_min_rate($dblink, $lot_id);
	$result = $bet > $rate;
	return $result;
}

function modify_when_error($err, $field, $modify) {
    $res = "";
    if (!empty($field)) {
      if (isset($err[$field])) {
        $res = " " . $modify;
      }
    }
    else {
      if (count($err) > 0)
        $res = " " . $modify;
    }
    return $res;
}

function initcookie($cats)
{
    $result = "0:0";
    foreach ($cats as $cat) {
        $result .= "-";
        $result .= $cat['id'] . ":0";
    }
    return $result;
}

function updatecookie($cookie, $cat)
{
    $result = "";
    $tok = ":-";
    $num = 0;
    $snum = strtok($cookie, $tok);
    while ($snum !== false) {
        $result .= $snum . ":";
        if ($snum == $cat) {
            $snum = strtok($tok);
            $num = $snum + 1;
        }
        else {
            $snum = strtok($tok);
            $num = $snum;
        }
        $result .= $num;
        $snum = strtok($tok);
        if ($snum !== false) {
            $result .= "-";
        }
    }
    return $result;
}

function category_name($catsInfo, $cat_id)
{
	$result = "";
	foreach($catsInfo as $cat) {
		if ($cat['id'] == $cat_id) {
			$result = $cat['name'];
			break;
		}
	}
	return $result;
}

require_once('helpers.php');
