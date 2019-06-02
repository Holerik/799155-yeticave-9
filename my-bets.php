<?php
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$user_id = 0;
$user_name = "";
$is_auth = 0;
$my_bets = [];
$bets_content = "";

if (isset($_SESSION['sess_id'])) {
    $user_id = $_SESSION['sess_id'];
}
if (isset($_SESSION['sess_name'])) {
    $user_name = $_SESSION['sess_name'];
    $is_auth = 1;
}

$my_bets[] = [
    'bet_id' => 0,
    'bet' => 0,
    'lot_id' => 0,
    'dt_add' => 0,
    'lot_img' => "",
    'lot_name' => "",
    'lot_descr' => "",
    'dt_fin' => 0,
    'cat_id' => 0,
    'user_info' => "",
    'status' => 0,
    'fin' => false,
    'is_auth' => 0
];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($user_id > 0) {
        $sql = "SELECT r.key_id, r.price, r.lot_id, l.dt_add, img_url, l.name, descr," .
        " dt_fin, cat_id, info  FROM rates r JOIN lots l ON r.lot_id = l.key_id  ".
        "JOIN users u ON u.key_id = r.user_id WHERE r.user_id = $user_id";

        $result = $yetiCave->query($sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            unset($my_bets);
            foreach ($rows as $row) {
                $status = check_rate($yetiCave, $row['lot_id'], $row['price']);
                $now = time();
                $fin = strtotime($row['dt_fin']);
                $finish = $now >= $fin;
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
                    'user_info' => $row['info'],
                    'status' => $status,
                    'fin' => $finish,
                    'is_auth' => $is_auth
                ];
            }
        } else {
            $error = $yetiCave->error();
        }
    }
}

if (empty($error)) {
    if ($is_auth == 1) {
        $bets_content = include_template('Betstempl.php', [
                    'catsInfo' => $catsArray,
                    'betsInfo' => $my_bets,
                    'user_name' => $user_name,
                    'user_id' => $user_id,
                    'is_auth' => $is_auth
        ]);
    } else {
        header("Location:_404php?hdr=Error 403&msg=Пожалуйста, авторизуйтесь!");
    }
} else {
    header("Location:_404php?hdr=SQL error&msg=" . $error);
}

print($bets_content);
