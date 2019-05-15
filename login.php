<?php
require_once('functions.php');
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$user_info = [];
$errors = [];   //перечень ошибок для полей формы

$required_fields = ['email', 'password'];
$dictionary = [
    'email' => 'Адрес эл.почты',
    'password' => 'Пароль авторизации'
];

$user_id = 0; // id пользователя
$cookie = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Проверка полей на заполненность
    foreach ($required_fields as $field) {
        if (isset($_POST[$field])) {
            if (empty($_POST[$field])) {
                $errors[$field] = 'Поле не заполнено!';
                $user_info[$field] = "";
            }
            else {
                //обезопасимся от XSS-уязвимости
                $user_info[$field] = htmlspecialchars($_POST[$field]);
            }
        }
    }
    if (!filter_var($user_info['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Поле не соответствует email!';
    }
    if (count($errors) == 0) {
        //ищем в базе id пользователя по email
        $passwordHash = password_hash($user_info['password'], PASSWORD_DEFAULT);
        $sql = "SELECT key_id, email, password, name FROM users";
        $result = mysqli_query($link, $sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach ($rows as $row) {
                if ($row['email'] == $user_info['email']) {
                    $user_id = $row['key_id'];
                    $passwordHash = $row['password'];
                    $user_info['name'] = $row['name'];
                    break;
                }
            }
        }
        else {
            $error = mysqli_error($link);
        }
        if (empty($error) && $user_id == 0) {
            $error = "Пользователя с такой почтой не зарегистрировано";
        }
        else {
            if (!password_verify($user_info['password'], $passwordHash)) {
                $error = "Введен неверный пароль";
            }
        }
        if (empty($error) && $user_id > 0) {
            //если куки есть - увеличиваем счетчик посещений
            //имя куки:  visit . $user_id
            //состав куки: пары цифр, через двоеточие, разделенные запятыми
            //первая цифра в паре - номер категории
            //вторая цифра - количество посещений лотов этой категории
            //для первой пары - это 0 и количество посещений сайта
            //функция updatecookie($cookie, $cat) увеличивает соответствующий 
            //счетчик для заданной куки и категории
            //функция initcookie($category) возврашает строку соответствующих пар цифр 
            $visit_cookie = 'visit_' . $user_id;
            $path = "/";
            if (isset($_COOKIE[$visit_cookie])) {
                $cookie = $_COOKIE[$visit_cookie];
                $cookie = updatecookie($cookie, 0);
                $expire = time() + 3600;
                setcookie($visit_cookie, $cookie, $expire, $path, "", false, true);
            }
            else {
                //если нет - создаем для него куки
                $cookie = initcookie($catsArray);
                $expire = time() + 3600;
                setcookie($visit_cookie, $cookie, $expire, $path, "", false, true);
            }
            //открываем сессию для пользователя
            $_SESSION[$visit_cookie] = $user_info['name'];           
            //переходим на главную страницу
            header("Location: index.php?user_id=" . $user_id);
        }
    }
} //POST

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $user_id = $_GET['user_id'];
    //ищем в базе данные пользователя по его id
    $sql = "SELECT key_id, email, name FROM users";
    $result = mysqli_query($link, $sql);
    if ($result) {
        $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach ($rows as $row) {
            if ($row['key_id'] == $user_id) {
                $user_info['key_id'] = $row['key_id'];
                $user_info['email'] = $row['email'];
                $user_info['name'] = $row['name'];
                break;
            }
        }
    }
    else {
        $error = mysqli_error($link);
    }
} //GET

if (empty($error)) {
    $login_content = include_template('Logintempl.php', [
    'catsInfo' => $catsArray,
    'userInfo' => $user_info,
    'errors' => $errors,
    'dictionary' => $dictionary
    ]);
}
else {
    $login_content = include_template('error.php', ['error' => $error]);
}
    
print($login_content);