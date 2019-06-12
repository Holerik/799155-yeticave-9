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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($user_id > 0) {
        $sql = "SELECT r.key_id, r.price, r.lot_id, r.dt_add, img_url, l.name, descr," .
        " dt_fin, cat_id, info  FROM rates r JOIN lots l ON r.lot_id = l.key_id  ".
        "JOIN users u ON u.key_id = r.user_id WHERE r.user_id = $user_id" . 
        " ORDER BY r.dt_add DESC";

        $result = $yetiCave->query($sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach ($rows as $row) {
                //в процессе торгов за лот м.б. несколько ставок подряд
                //выбираем последнюю
                $flag = true;
                foreach ($my_bets as $bet) {
                    if (isset($bet['lot_id']) && $bet['lot_id'] == $row['lot_id'] ) {
                        $flag = false;
                        break;
                    }
                }
                if (!$flag) {
                    continue;
                }
                $status = check_rate($yetiCave, $row['lot_id'], $row['price']);
                $now = time();
                $fin = strtotime($row['dt_fin']);
                $finish = $now >= $fin;
                //поищем в каталоге 'маленький' файл с изображением
                $fname = $row['img_url'];
                str_replace(".", "_small.", $fname);
                if (file_exists($fname)) {
                    $row['img_url'] = $fname;
                }
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
        header("Location:_404.php?hdr=Error 403&msg=Пожалуйста, авторизуйтесь!");
    }
} else {
    header("Location:_404.php?hdr=SQL error&msg=" . $error);
}

print($bets_content);
