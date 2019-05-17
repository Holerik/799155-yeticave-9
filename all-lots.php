<?php
require_once('dbinit.php');
require_once('pagination.php');
require_once('functions.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();
$user_name = "";
$user_id = 0;
$is_auth = 0;
//категория лота
$cat_id = 0;
//количество ставок для каждого лота
$bets_count = [];

$all_lots_content = "";
$max_lots_per_page = 2;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $visit_cookie = 'visit_' . $user_id;
        if (isset($_SESSION[$visit_cookie])) {
            $user_name = $_SESSION[$visit_cookie];
            $is_auth = 1;
        }
    }
    if (isset($_GET['cat_id'])) {
    	$cat_id = $_GET['cat_id'];
    }
}

if (empty($error)) {
	foreach ($catsInfoArray as $catsInfo) {
    	$sql = "SELECT COUNT(*) FROM rates r WHERE r.lot_id = " . $catsInfo['lot_id'];
	    $result = mysqli_query($link, $sql);
	    if ($result)
    	{
	    	$count = 0;
	    	if ($result) {
				$rows = mysqli_fetch_row($result);
				$count = $rows[0];
				$bets_count[$catsInfo['lot_id']] = $count;
	    	}
    		else {
    			$error = mysqli_error($link);
    			break;
	    	}
	    }
	}
}

if (empty($error)) {
	$sql = "SELECT COUNT(*) FROM lots l WHERE l.cat_id = $cat_id";
	$result = mysqli_query($link, $sql);
	if ($result) {
		$rows = mysqli_fetch_row($result);
		$lots_count = $rows[0];
		$offset_page = ($lot_page - 1) * $max_lots_per_page;
		$max_page = floor($lots_count / $max_lots_per_page);
		if ($lots_count % $max_lots_per_page > 0) {
			$max_page++;
		}	    
		$sql = "SELECT l.name, c.name as cat_name, cat_id, l.price, img_url, l.key_id, l.dt_fin FROM lots l" .
	        " JOIN categories c ON l.cat_id = c.key_id  WHERE l.cat_id = $cat_id " .
    	    " LIMIT $max_lots_per_page OFFSET $offset_page";
	    $result = mysqli_query($link, $sql);
	    if ($result) {
		    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
		    foreach ($rows as $row) {
			    $catsInfoArray[] = [
		    	'Название' => $row['name'],
			    'Категория' => $row['cat_name'],
		    	'Цена' => $row['price'],
			    'URL картинки' => $row['img_url'],
			    'lot_id' => $row['key_id'],
			    'cat_id' => $row['cat_id'],
		    	'dt_fin' => $row['dt_fin']
	    		];
		    }
		}
    	else {
    		$error = mysqli_error($link);
    	}
	}
	else {
		$error = mysqli_error($link);
	}
}

if(empty($error)) {
	$all_lots_content = include_template('Alltempl.php', [
        'catsArray' => $catsArray,
        'catsInfoArray' => $catsInfoArray,
        'bets_count' => $bets_count,
        'cat_id' => $cat_id,
        'user_id' => $user_id,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
       	'max_page' => $max_page,
       	'lot_page' => $lot_page
    ]);
} 
else {
    $all_lots_content = include_template('error.php', ['error' => $error]);
}
print($all_lots_content);
