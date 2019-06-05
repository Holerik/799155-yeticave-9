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
$autor_id = 0;

$rate_info = ['cost' => 0];  //данные полей формы
$dictionary = [
    'cost' => 'Ставка лота'
];
$errors = [];   //перечень ошибок для полей формы
$lot_row = [];

if (isset($_SESSION['sess_id'])) {
    $user_id = $_SESSION['sess_id'];
}
if (isset($_SESSION['sess_name'])) {
    $user_name = $_SESSION['sess_name'];
    $is_auth = 1;
}

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
        $min_rate = get_min_rate($yetiCave, $lot_id);
        if ($rate_info['cost'] < $min_rate) {
            $errors['cost'] = 'Ставка меньше ' . $min_rate;  
        }
    }
    //информация о лоте
    $sql = "SELECT * FROM lots WHERE key_id = $lot_id";
    $result = $yetiCave->query($sql);
    if ($result) {
        $lot_row = mysqli_fetch_assoc($result);
        //проверим время окончания торгов по ставке
        $now = time();
        $fin = strtotime($lot_row['dt_fin']);
        if ($now > $fin) {
            $error = "Ставки не принимаются";
        }
    } else {
        $error = $yetiCave->error();
    }

    if (!isset($errors['cost'])) {
        //запишем данные ставку лота в базу
        $sql = "INSERT INTO rates (dt_add, price, user_id, lot_id)"
                . " VALUES (NOW(), ?, ?, ?)";
        $stmt = $yetiCave->prepare_stmt($sql, [$rate_info['cost'], $user_id, $lot_id]);
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            $last_id = $yetiCave->last_id();
            $rate_info['rate_id'] = $last_id;
        } else {
            $error = $yetiCave->error();
        }
    }
} //$_POST

//сюда приходит ссылка из письма победителю
//или после добавления лота
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['lot_id'])) {
        $lot_id = intval($_GET['lot_id']);
    }
    $winner_name = "";
    $winner_id = 0;
    $sql = "SELECT winner_id, autor_id  FROM lots WHERE key_id = $lot_id";
    $result = $yetiCave->query($sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $winner_id = $row['winner_id'];
        $autor_id = $row['autor_id'];
    } else {
        $error = $yetiCave->error();
        header("Location:_404.php?hdr=SQL error&msg=" . $error . " (1)");
    }
    if (isset($_GET['uname'])) {
        $winner_name = htmlspecialchars($_GET['uname']);
        $sql = "SELECT name FROM users WHERE key_id = $winner_id";
        $result = $yetiCave->query($sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            if (!($winner_name === $row['name'])) {
                $winner_name = "";
                $winner_id = 0;
            }
        } else {
            $error = $yetiCave->error();
            header("Location:_404.php?hdr=SQL error&msg=" . $error . " (2)");
        }
    }
    $sql = "";
    if ($lot_id > 0) {
        $sql = "SELECT COUNT(*) FROM lots l WHERE l.key_id = $lot_id";
        if ($is_auth == 1 && ($user_id != $winner_id)) {
            $winner_id = 0;
        }
        if ($is_auth == 0) {
            $user_id = $winner_id;
            $user_name = $winner_name;
        }
        if ($winner_id == 0) {
            $sql .= " AND l.dt_fin > NOW()";     
        }
    }
    if (!empty($sql)) {
        $result = $yetiCave->query($sql);
        if ($result) {
            $rows = mysqli_fetch_row($result);
            if ($rows[0] == 0) {
                header("Location: _404.php?msg=Такого лота нет");
            }
        } else {
            $error = $yetiCave->error();
            header("Location:_404.php?hdr=SQL error&msg=" . $error . " (3)");
        }

        $min_rate = get_min_rate($yetiCave, $lot_id);
        //получим информацию о лоте
        $sql = "SELECT * FROM lots WHERE key_id = $lot_id";
        $result = $yetiCave->query($sql);
        if ($result) {
            $lot_row = mysqli_fetch_assoc($result);
            if (mysqli_num_rows($result) > 0) {
                $rate_info['cost'] = get_min_rate($yetiCave, $lot_row['key_id']);
            }
        } else {
            $error = $yetiCave->error();
        }
    } else {
        header("Location:_404.php");
    }
}//$_GET

$cat_name = category_name($catsArray, $lot_row['cat_id']);

//история ставок
$user_win = 0;
$history = [];

$sql = "SELECT price, r.dt_add, name, r.user_id FROM rates r" . 
       " JOIN users u ON r.user_id = u.key_id " .
       " WHERE r.lot_id = $lot_id" .
       " ORDER BY r.dt_add DESC";
$result = $yetiCave->query($sql);
if ($result) {
    $history = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $now = time();
    $fin = strtotime($lot_row['dt_fin']);
    if ($now > $fin) {
        //определим победителя
        foreach ($history as $item) {
            if (check_rate($yetiCave, $lot_row['key_id'], $item['price'])) {
                $user_win = $item['user_id']; 
                break;
            }
        }
    } else {
          //определим новую цену лота
        if (mysqli_num_rows($result) > 0) {
            $lot_row['price'] = $history[0]['price'];
        }
    }
} else {
    $error = $yetiCave->error();
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
            'autor_id' => $autor_id,
            'errors' => $errors,
            'rate' => $rate_info,
            'dict' => $dictionary,
            'user_win' => $user_win,
            'history' => $history
        ]);
} else {
        header("Location:_404.php?hdr=SQL error&msg=" . $error . " (4)");
}

print($lot_content);

