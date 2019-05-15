<?php
require_once('functions.php');
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();
$user_name = "";
$user_id = 0;
$is_auth = 0;
$cat_id = 0;
//количество ставок для каждого лота
$bets_count = [];
//lot count per category
$lots_count = 0;
$max_lots_per_page = 2;
//first number of lot in $catsInfoArray per page
$first_lot_on_page = 0;
//номер страницы
//page number
$lot_page = 0;

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
    if (isset($_GET['lot_page'])) {
    	$lot_page = $_GET['lot_page'];
    }
}

$first_lot_on_page = $lot_page * $max_lots_per_page;

foreach ($catsInfoArray as $catsInfo) {
    $sql = "SELECT key_id FROM rates r WHERE r.lot_id = " . $catsInfo['lot_id'];
    $result = mysqli_query($link, $sql);
    $count = 0;
    if ($result) {
    	$count = mysqli_num_rows($result);
    }
    $bets_count[$catsInfo['lot_id']] = $count;
}

$sql = "SELECT key_id FROM lots l WHERE l.cat_id = $cat_id";
$result = mysqli_query($link, $sql);
if ($result) {
 	$count = mysqli_num_rows($result);
   	$lots_count = $count;
}

$page_count = $lots_count / $max_lots_per_page + ($lots_count % $max_lots_per_page > 0) ? 1 : 0;
if ($page_count == 0) {
	$page_count = 1;
}

$all_lots_content = include_template('Alltempl.php', [
        'catsArray' => $catsArray,
        'catsInfoArray' => $catsInfoArray,
        'bets_count' => $bets_count,
        'cat_id' => $cat_id,
        'user_id' => $user_id,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
        'first_lpp' => $first_lot_on_page,
        'max_lpp' => $max_lots_per_page,
        'pages' => $page_count
    ]);
    
print($all_lots_content);
