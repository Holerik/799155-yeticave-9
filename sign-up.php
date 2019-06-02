<?php
require_once('dbinit.php');

$user_info = [];
$errors = [];   //перечень ошибок для полей формы

$required_fields = ['name', 'email', 'password', 'message'];
$dictionary = [
    'name' => 'Имя пользователя',
    'email' => 'Адрес эл.почты',
    'password' => 'Пароль авторизации',
    'message' => 'Поле для контакта'
];

$user_id = 0; // id пользователя
$user_info[] = [
    'name' => "",
    'email' => "",
    'password' => "",
    'message' => ""
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Проверка полей на заполненность
    unset($user_info);
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            $errors[$field] = 'Поле отсутствует!';
            continue;
        }
        if (empty($_POST[$field])) {
             $errors[$field] = 'Поле не заполнено!';
             continue;
        }
        //обезопасимся от XSS-уязвимости
        $user_info[$field] = htmlspecialchars($_POST[$field]);
    }
    if (!filter_var($user_info['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Поле не соответствует email!';
    }
    if (strlen($_POST['message']) > 255) {
        $errors['message'] = 'Извините, но не более 255 символов';
    }

    $safe_name = "";
    $safe_email = "";
    $safe_info = "";    

    if (count($errors) == 0) {
        $safe_name = $yetiCave->escape_str($user_info['name']);
        $safe_email = $yetiCave->escape_str($user_info['email']);
        $safe_info = $yetiCave->escape_str($user_info['message']);
    
        // есть ли пользователь с таким именем?
        $sql = "SELECT * FROM users WHERE name = '$safe_name'";
        $result = $yetiCave->query($sql);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $errors['name'] = 'Пользователь ' . $safe_name . ' уже зарегистрирован!';
            }
        } else {
            $error = $yetiCave->error();
        }

        $sql = "SELECT * FROM users WHERE email = '$safe_email'";
        $result = $yetiCave->query($sql);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $errors['email'] = 'Почта ' . $safe_email . ' уже зарегистрирована!';
            }
        } else {
            $error = $yetiCave->error();
        }
    }

    if (count($errors) == 0 && empty($error)) {
        //зарегистрируем пользователя и получим его id
        //получим для пароля хэш
        $passwordHash = password_hash($user_info['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (safe_email, safe_name, password, safe_info)"
                    . " VALUES (?, ?, ?, ?)";
        $stmt = $yetiCave->prepare_stmt($sql, [$safe_email, $safe_name, $passwordHash, $safe_info]);
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            $user_id = $yetiCave->last_id($link);
        } else {
            $error = $yetiCave->error();
        }
    }
    if ($user_id > 0) {
        //переходим на страницу авторизации
        header("Location:login.php");
    }
}

if (empty($error)) {
    $sign_content = include_template('Signtempl.php', [
        'catsInfo' => $catsArray,
        'userInfo' => $user_info,
        'user_id' => $user_id,
        'errors' => $errors,
        'dictionary' => $dictionary
    ]);
} else {
    header("Location:_404.php?hdr=SQL error&msg=" . $error);
}
    
print($sign_content);