<?php
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
$lot_id = 0;
$user_id = 0;

$rate_info = ['cost' => 0];  //данные полей формы
$dictionary = [
    'cost' => 'Ставка лота'
];
$errors = [];   //перечень ошибок для полей формы
$lot_row = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $field = 'cost';
    if (isset($_POST[$field])) {
        if (empty($_POST[$field])) {
            $errors[$field] = 'Ставка не сделана!';
            $rate_info[$field] = 0;
        }
    }
    if (empty($errors[$field])) {
       	$options = [
           'options' => [
	           'default' => 0,
               'min_range' => 1,
               'max_range' => 1000000
       	    ]
       	];
    	if (!filter_var($_POST[$field], FILTER_VALIDATE_INT, $options)) {
               	$errors[$field] = 'Поле не должно содержать символов!';
    	 }   
    }
    if (empty($errors[$field])) {
   	    //обезопасимся от XSS-уязвимости
        $rate_info[$field] = htmlspecialchars($_POST[$field]);
    }
    //проверим величину ставки
    if (isset($_POST['lot_id'])) {
        $lot_id = $_POST['lot_id'];
	    $min_rate = get_min_rate($link, $lot_id);
	    if ($rate_info['cost'] <= $min_rate) {
    	    $errors['cost'] = 'Ставка меньше или равна ' . $min_rate;  
	    }
    }
	//информация о лоте
	$sql = "SELECT * FROM lots WHERE key_id = $lot_id";
	$result = mysqli_query($link, $sql);
	if ($result) {
    	$lot_row = mysqli_fetch_assoc($result);
	    //проверим время окончания торгов по ставке
	    $now = time();
	    $fin = strtotime($lot_row['dt_fin']);
    	if ($now > $fin) {
    		$error = "Ставки не принимаются";
	    }
	}
	else {
			$error = mysqli_error($link);
		}
	$flag = true;
    //узнаем автора лота
    if (isset($_POST['user_id'])) {
    	$row = [];
	    $user_id = $_POST['user_id'];
   		$visit_cookie = 'visit_' . $user_id;
		if (isset($_SESSION[$visit_cookie])) {
			$user_name = $_SESSION[$visit_cookie];
   			$is_auth = 1;
		}

        $sql = "SELECT autor_id FROM lots WHERE key_id = $lot_id";
        $result = mysqli_query($link, $sql);
        if ($result) {
	        $row = mysqli_fetch_assoc($result);
            if ($row['autor_id'] == $user_id)
            {
            	$errors['cost'] = 'Автор не может делать ставки!';
            	$flag = false;
            }
		}
		else {
            $error = mysqli_error($link);
            $flag = false;
		}
    }

    if (isset($errors['cost'])) {
        $flag = false;
    }

    if ($flag) {
        //запишем данные ставку лота в базу
        $sql = "INSERT INTO rates (dt_add, price, user_id, lot_id)"
                . " VALUES (NOW(), ?, ?, ?)";
        $stmt = db_get_prepare_stmt($link, $sql, [$rate_info['cost'], $user_id, $lot_id]);
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            $last_id = mysqli_insert_id($link);
            $rate_info['rate_id'] = $last_id;
        }
        else {
            $error = mysqli_error($link);
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
    if ($is_auth == 0) {
       	http_response_code(404);
    }
	if (isset($_GET['lot_id'])) {
        $lot_id = $_GET['lot_id'];  
        $min_rate = get_min_rate($link, $lot_id);
	    //получим информацию о лоте
    	$sql = "SELECT * FROM lots WHERE key_id = $lot_id";
        $result = mysqli_query($link, $sql);
	    if ($result) {
			$lot_row = mysqli_fetch_assoc($result);
        	if (mysqli_num_rows($result) > 0) {
    	    	$rate_info['cost'] = get_min_rate($link, $lot_row['key_id']);
	        }
        }
   	    else {
    	    $error = mysqli_error($link);
   		}
    }
}//_GET

foreach ($catsArray as $cat) {
	if ($cat['id'] === $lot_row['cat_id']) {
	    $cat_name = $cat['name'];
        break;
    }
}

//история ставок
$user_win = 0;
$history = [];

$sql = "SELECT price, r.dt_add, name, r.user_id FROM rates r JOIN users u ON r.user_id = u.key_id " .
		"  WHERE r.lot_id = $lot_id" .
		" ORDER BY r.dt_add DESC";
$result = mysqli_query($link, $sql);
if ($result) {
      $history = mysqli_fetch_all($result, MYSQLI_ASSOC);
	  $now = time();
	  $fin = strtotime($lot_row['dt_fin']);
      if ($now > $fin) {
      //определим победителя
		foreach ($history as $item) {
            if (check_rate($link, $lot_row['key_id'], $item['price'])) {
                $user_win = $item['user_id']; 
      		    break;
      		}
      	}
      }
}
else {
		$error = mysqli_error($link);
	}

if (empty($error)) {
        $lot_content = include_template('Lotempl.php', [
            'lotInfo' => $lot_row,
            'catsInfo' => $catsArray,
            'cat_name' => $cat_name,
            'is_auth' => $is_auth,
            'min_rate' => $min_rate,
            'user_id' => $user_id,
            'user_name' => $user_name,
            'errors' => $errors,
            'rate' => $rate_info,
            'dict' => $dictionary,
            'user_win' => $user_win,
            'history' => $history
            ]);
}
else {
        $lot_content = include_template('error.php', ['error' => $error]);
    }

print($lot_content);

