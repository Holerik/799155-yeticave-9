<?php
require_once('dbinit.php');
require_once('functions.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$lot_content = "";
$user_id = 0;
$user_name = "";
$is_auth = 0;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $visit_cookie = 'visit_' . $user_id;
        if (isset($_SESSION[$visit_cookie])) {
            $user_name = $_SESSION[$visit_cookie];
            $is_auth = 1;
        }
    }
    if (isset($_GET['lot_id'])) {
        $lotId = $_GET['lot_id'];  
        if ($link) {
            $sql = "SELECT * FROM lots WHERE key_id = $lotId";
            $result = mysqli_query($link, $sql);
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                if (mysqli_num_rows($result) > 0) {
                    if ($is_auth == 1) {
                        //отметимся в куках для категории этого лота
                        $visit_cookie = 'visit_' . $user_id;
                        $path = "/";
                        if (isset($_COOKIE[$visit_cookie])) {
                            $cookie = $_COOKIE[$visit_cookie];
                            $cookie = updatecookie($cookie, $row['cat_id']);
                            $expire = time() + 3600;
                            setcookie($visit_cookie, $cookie, $expire, $path, "", false, true);
                        }
                    }
                    $lot_content = include_template('Lotempl.php', [
                    'lotInfo' => $row,
                    'catsInfo' => $catsArray,
                    'dblink' => $link,
                    'is_auth' => $is_auth,
                    'user_id' => $user_id,
                    'user_name' => $user_name
                    ]);
                    print($lot_content);
                }
                else {
                    http_response_code(404);
                }
            }
            else {
                $error = mysqli_error($link);
                $lot_content = include_template('error.php', ['error' => $error]);
                print($lot_content);
            }
        }
    }
    else {
        http_response_code(404);
    }
}
?>

