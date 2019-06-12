<?php
require_once('dbinit.php');
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);  
session_start();

$user_info = [];
$user_info['key_id'] = 0;
$user_info['email'] = "";
$user_info['name'] = "";
$user_info['password'] = "";
$user_info['avatar'] = "";

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
            } else {
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
        $safe_email = $yetiCave->escape_str($user_info['email']);
        $passwordHash = "";
        $sql = "SELECT key_id, email, u.password, u.name, avatar_path FROM users u WHERE email = '$safe_email'";
        $result = $yetiCave->query($sql);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
                $user_id = $rows[0]['key_id'];
                $passwordHash = $rows[0]['password'];
                $user_info['name'] = $rows[0]['name'];
                if (isset($rows[0]['avatar_path'])) {
                    $user_info['avatar'] = $rows[0]['avatar_path'];
                } else {
                    $user_info['avatar'] = "";
                    if (isset($_SESSION['avatar'])) {
                        $_SESSION['avatar'] = "";
                    }
                }
            }
        } else {
            $error = $yetiCave->error();
        }
        if (empty($error) && $user_id == 0) {
            $errors['email'] = "Пользователя с такой почтой не зарегистрировано";
        } else {
            if (!password_verify($user_info['password'], $passwordHash)) {
                $errors['password'] = "Введен неверный пароль ";
            }
        }
    }
    if (empty($errors) && $user_id > 0) {
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
        } else {
            //если нет - создаем для него куки
            $cookie = initcookie($catsArray);
            $expire = time() + 3600;
            setcookie($visit_cookie, $cookie, $expire, $path, "", false, true);
        }
        //открываем сессию для пользователя
        $_SESSION['sess_id'] = $user_id;
        $_SESSION['sess_name'] = $user_info['name'];   
        if (!empty($user_info['avatar'])) {
            $_SESSION['avatar'] = $user_info['avatar'];
        }       
        //переходим на главную страницу
        header("Location:index.php");
    }
} //POST

if (empty($error)) {
    $login_content = include_template('Logintempl.php', [
        'catsInfo' => $catsArray,
        'userInfo' => $user_info,
        'user_id' => $user_id,
        'errors' => $errors,
        'dictionary' => $dictionary
    ]);
} else {
    header("Location:_404.php?hdr=SQL error&msg=" . $error);
}
    
print($login_content);