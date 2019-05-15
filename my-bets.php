<?php
require_once('functions.php');
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$user_id = 0;
$user_name = "";
$is_auth = 0;
$my_bets = [];
$bets_content = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id']; 
        $visit_cookie = 'visit_' . $user_id;
        if (isset($_SESSION[$visit_cookie])) {
            $is_auth = 1;
            $user_name = $_SESSION[$visit_cookie];
        }
        $sql = "SELECT r.key_id, r.price, r.lot_id, l.dt_add, img_url, l.name, descr," .
        " dt_fin, cat_id, info  FROM rates r JOIN lots l ON r.lot_id = l.key_id  ".
        "JOIN users u ON u.key_id = $user_id WHERE r.user_id = $user_id";

        $result = mysqli_query($link, $sql);
        if ($result) {
        	$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        	foreach ($rows as $row) {
        		$my_bets[] = [
        			'bet_id' => $row['key_id'],
        			'bet' => $row['price'],
        			'lot_id' => $row['lot_id'],
        			'dt_add' => $row['dt_add'],
        			'lot_img' => $row['img_url'],
        			'lot_name' => $row['name'],
        			'lot_descr' => $row['descr'],
        			'dt_fin' => $row['dt_fin'],
        			'cat_id' => $row['cat_id'],
        			'user_info' => $row['info']
        		];
        	}
        }
    }
}

if (empty($error)) {
    if ($is_auth == 1) {
        $bets_content = include_template('Betstempl.php', [
        			'dblink' => $link,
                    'catsInfo' => $catsArray,
                    'betsInfo' => $my_bets,
                    'user_name' => $user_name,
                    'user_id' => $user_id,
                    'is_auth' => $is_auth
                ]);
    }
    else {
        http_response_code(403);
    }
}
else {
    $bets_content = include_template('error.php', ['error' => $error]);
}

print($bets_content);
