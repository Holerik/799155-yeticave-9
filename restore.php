<?php
require_once('dbinit.php');

$email = "";

$errors = [];   //перечень ошибок для полей формы
$user_name = "";
$user_id = 0;
$password = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Проверка полей на заполненность
    if (isset($_POST['email'])) {
        if (empty($_POST['email'])) {
            $errors['email'] = 'Поле не заполнено!';
            $email = "";
        } else {
            //обезопасимся от XSS-уязвимости
            $email= htmlspecialchars($_POST['email']);
        }
    }
    if (!isset($_POST['password'])) {
        $password = "";
        $errors['password'] = "";
    } else {
        $password = htmlspecialchars($_POST['password']);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Поле не соответствует email!';
    }
    if (count($errors) == 0) {
        //проверим почту по базе
        $sql = "SELECT key_id, u.name FROM users u WHERE u.email = '$email'";
        $result = $yetiCave->query($sql);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
                $user_id = $rows[0]['key_id'];
                $user_name = $rows[0]['name'];
            } else {
                $msg = "Адреса " . $email . " в базе нет";
                header("Location:_404.php?hdr=Ошибка&msg=$msg");
            }
        } else {
            $error = $yetiCave->error();
            header("Location:_404.php?hdr=SQL error&msg=" . $error . " 1");
        }
        if (empty($password)) {
            //генерим пароль
            $password = uniqid();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            //записываем его в базу
            $sql = "UPDATE users SET password = '$passwordHash' WHERE email = '$email'";
            $result = $yetiCave->query($sql);
            if (!$result) {
                $error = $yetiCave->error();
                 header("Location:_404.php?hdr=SQL error&msg=" . $error . " 2");
            }
            //генерим письмо и отсылаем его пользователю
            $email_content = include_template('Email2templ.php', [
                'user_name' => $user_name,
                'email' => $email,
                'code' => $password
            ]);
            try {
                restoreinfo($user_name, $email_content, $email);
                $msg = "Письмо с кодом восстановления выслано в адрес " . $email;
                header("Location:_404.php?hdr=Восстановление пароля&msg=".$msg);
            } catch (Exception $e) {
                $msg = "Письмо не отправлено: " . $e->getMessage();
                header("Location:_404.php?hdr=Восстановление пароля&msg=" . $msg);
            }
        } else {
            //записываем новый пароль в базу и на авторизацию
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = '$passwordHash' WHERE email = '$email'";
            $result = $yetiCave->query($sql);
            if (!$result) {
                $error = $yetiCave->error();
                header("Location:_404.php?hdr=SQL error&msg=" . $error . " 3");
            }
            header("Location:login.php");
            //header("Location:_404.php?hdr=Password&msg=" . $password);
        }
    } else {
        $restore_content = include_template('Restempl.php', [
            'catsInfo' => $catsArray,
            'email' => $email,
            'password' => $password,
            'user_name' => $user_name,
            'user_id' => $user_id,
            'errors' => $errors
        ]);
        print($restore_content);
    }
} //$_POST

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['code'])) {
        $password = htmlspecialchars($_GET['code']);
    } else {
        header("Location:_404.php?hdr=Ошибка&msg=Не указан код восстановления пароля");
    }
    if (isset($_GET['email'])) {
        $email = htmlspecialchars($_GET['email']);
    } else {
        header("Location:_404.php?hdr=Ошибка&msg=Не указан адрес эл.почты");
    }
    $sql = "SELECT key_id, name, password FROM users WHERE email = '$email'";
    $result = $yetiCave->query($sql);
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
            //проверим пароль
            if (password_verify($password, $rows[0]['password'])) {
                $user_id = $rows[0]['key_id'];
                $user_name = $rows[0]['name'];
            } else {
                $msg = "Ошибка при восстановлении пароля";
                header("Location:_404.php?hdr=Ошибка&msg=$msg");
            }
        } 
    } else {
        $error = $yetiCave->error();
        header("Location:_404.php?hdr=SQL error&msg=" . $error . " 4");
    }
    $restore_content = include_template('Restempl.php', [
        'catsInfo' => $catsArray,
        'email' => $email,
        'password' => $password,
        'user_name' => $user_name,
        'user_id' => $user_id,
        'errors' => $errors
    ]);
    print($restore_content);
}
