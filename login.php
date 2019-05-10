<?php
require_once('dbinit.php');
require_once('functions.php');

$user_info = [];
$errors = [];   //перечень ошибок для полей формы

$required_fields = ['email', 'password'];
$dictionary = [
    'email' => 'Адрес эл.почты',
    'password' => 'Пароль авторизации'
];

$user_id = 0; // id пользователя

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
        $sql = "SELECT key_id, email FROM users";
        $result = mysqli_query($link, $sql);
        if ($result) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach ($rows as $row) {
                if ($row['email'] == $user_info['email']) {
                    $user_id = $row['key_id'];
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
        if ($user_id > 0) {
            //если куки есть - увеличиваем счетчик посещений
            //если нет - создаем для него куки
            //открываем сессию для пользователя
            //переходим на главную страницу
            header("Location: index.php?user_id=" . $user_id);
        }
    
    }
}

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