<?php
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$user_id = 0;
$user_name = "";
$is_auth = 0;

$errors = [];
$pwds = [
    'pwd_old' => "", 
    'pwd_new1' => "", 
    'pwd_new2' => ""
];

if (isset($_SESSION['sess_id'])) {
    $user_id = $_SESSION['sess_id'];
}
if (isset($_SESSION['sess_name'])) {
    $user_name = $_SESSION['sess_name'];
    $is_auth = 1;
}

if ($is_auth == 0) {
    header("Location:_404.php?hdr=Error 403&msg=Пожалуйста, авторизуйтесь!");
    echo(" ");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($pwds as $key => $value) {
        if (isset($_POST[$key])) {
            if (!empty($_POST[$key])) {
                $value = htmlspecialchars($_POST[$key]);
                $pwds[$key] = $value;
            } else {
                $errors[$key] = "Поле не заполнено";
            }
        } else {
            $errors[$key] = "Поле отсутствует";
        }
    } 
}

if (empty($errors)) {
    if (!($pwds['pwd_new1'] === $pwds['pwd_new2'])) {
        $errors['pwd_new1'] = "Пароли не совпадают";
        $errors['pwd_new2'] = errors['pwd_new1'];
    }
}

if (empty($errors) && !empty($pwds['pwd_old'])) {
    //проверим действующий пароль
    $sql = "SELECT password FROM users WHERE key_id = $user_id";
    $result = $yetiCave->query($sql);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $passwordHash = $row['password'];
        if (!password_verify($pwds['pwd_old'], $passwordHash)) {
            $errors['pwd_old'] = "Введен неверный пароль";
        }
    } else {
        $error = $yetiCave->error();
        header("Location:_404.php?hdr=SQL error&msg=" . $error);
    }
}

if (empty($errors) && !empty($pwds['pwd_new1'])) {
    //поменяем пароль
    $passwordHash = password_hash($pwds['pwd_new1'], PASSWORD_DEFAULT);
    //записываем его в базу
    $sql = "UPDATE users SET password = '$passwordHash' WHERE key_id = $user_id";
    $result = $yetiCave->query($sql);
    if (!$result) {
        $error = $yetiCave->error();
        header("Location:_404.php?hdr=SQL error&msg=" . $error);
    } else {
        header("Location:_404.php?hdr=Замена пароля&msg=Пароль заменен!");
    }
} 

$changepwd_content = include_template('Changetempl.php', [
    'user_id' => $user_id,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'pwds' => $pwds,
    'errors' => $errors
]);

print($changepwd_content);