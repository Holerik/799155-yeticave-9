<?php
require_once('functions.php');
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$lot_content = "";
$cat_name = "";
$min_rate = 0;
$user_id = 0;
$user_name = "";
$is_auth = 0;

$rate_info = ['cost' => 0];  //данные полей формы
$dictionary = [
    'cost' => 'Ставка лота'
];
$errors = [];   //перечень ошибок для полей формы
$lot_ok = true;
$row = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $field = 'cost';
    if (isset($_POST[$field])) {
        if (empty($_POST[$field])) {
            $errors[$field] = 'Ставка равна нулю!';
            $rate_info[$field] = 0;
        }
        else {
            $flag = true;
            $options = [
                    'options' => [
                        'default' => 0,
                        'min_range' => 1,
                        'max_range' => 1000000
                    ]
            ];
            if (!filter_var($_POST[$field], FILTER_VALIDATE_INT, $options)) {
                $errors[$field] = 'Поле не должно содержать символов!';
                $flag = false;
            }   
            if ($flag) {
                //обезопасимся от XSS-уязвимости
                $rate_info[$field] = htmlspecialchars($_POST[$field]);
            }
        }
    }
    if (isset($_POST['lot_id'])) {
        $lotId = $_POST['lot_id'];
        if ($link) {
            $sql = "SELECT * FROM lots WHERE key_id = $lotId";
            $result = mysqli_query($link, $sql);
            if ($result) {
                $row = mysqli_fetch_assoc($result);
            }
        }
        $user_id = $_POST['user_id'];
        $visit_cookie = 'visit_' . $user_id;
        if (isset($_SESSION[$visit_cookie])) {
            $user_name = $_SESSION[$visit_cookie];
            $is_auth = 1;
        }
    }
    if (count($errors) == 0) {
        //запишем данные ставки лота в базу
        $sql = "INSERT INTO rates (dt_add, price, user_id, lot_id)"
                . " VALUES (NOW(), ?, ?, ?)";
        $stmt = db_get_prepare_stmt($link, $sql, [$rate_info['cost'], $user_id, $lotId]);
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            $last_id = mysqli_insert_id($link);
            $rate_info['rate_id'] = $last_id;
        }
    }
} //_POST

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
                        //если хозяин лота не его владелец
                        if ($row['autor_id'] != $user_id) {
                        	$visit_cookie = 'visit_' . $user_id;
                        	$path = "/";
                        	if (isset($_COOKIE[$visit_cookie])) {
                            	$cookie = $_COOKIE[$visit_cookie];
                            	$cookie = updatecookie($cookie, $row['cat_id']);
                            	$expire = time() + 3600;
                            	setcookie($visit_cookie, $cookie, $expire, $path, "", false, true);
                        	}
                        }
                    }
                    $rate_info[$field] = get_min_rate($link, $row['key_id']);
                }
                else {
                    $lot_ok = false;
                    http_response_code(404);
                }
            }
            else{
                $error = mysqli_error($link);
            }
        }
    }
}//_GET

if ($lot_ok) {
    $min_rate = get_min_rate($link, $row['key_id']);
    if ($rate_info['cost'] < $min_rate) {
        $errors['cost'] = 'Ставка меньше ' . $min_rate;  
    }
     foreach ($catsArray as $cat) {
        if ($cat['id'] == $row['cat_id']) {
            $cat_name = $cat['name'];
            break;
        }
    }

    if (empty($error)) {
        $lot_content = include_template('Lotempl.php', [
            'lotInfo' => $row,
            'catsInfo' => $catsArray,
            'cat_name' => $cat_name,
            'is_auth' => $is_auth,
            'min_rate' => $min_rate,
            'user_id' => $user_id,
            'user_name' => $user_name,
            'errors' => $errors,
            'rate' => $rate_info,
            'dict' => $dictionary
            ]);
    }
    else {
        $lot_content = include_template('error.php', ['error' => $error]);
    }
    print($lot_content);
}
?>

