<?php
require_once('dbinit.php');
require_once('functions.php');

$lot_info = [];  //данные полей формы
$required_fields = ['category', 'message', 'lot-name', 'lot-rate', 'lot-step', 'lot-date', 'lot-img'];
$dictionary = [
    'category' => 'Категория',
    'message' => 'Описание',
    'lot-name' => 'Наименование',
    'lot-rate' => 'Начальная цена',
    'lot-step' => 'Шаг ставки',
    'lot-date' => 'Дата окончания',
    'lot-img' => 'Изображение'
];
$errors = [];   //перечень ошибок для полей формы
$add_content = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Проверка полей на заполненность
    if (isset($_POST['category'])) {
        //это плейсхолдер - категория не выбрана
        if ($_POST['category'] == 'Выберите категорию')
        $_POST['category'] = "";
    }
    
    foreach ($required_fields as $field) {
        if (isset($_POST[$field])) {
            if (empty($_POST[$field])) {
                $errors[$field] = 'Поле не заполнено!';
                $lot_info[$field] = "";
            }
            else {
                $flag = true;
                if ($field == 'lot-rate' || ($field == 'lot-step')) {
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
                }
                if ($flag) {
                    //обезопасимся от XSS-уязвимости
                    $lot_info[$field] = htmlspecialchars($_POST[$field]);
                    
                }
            }
        }
    }
    //обработка графических данных
    if (isset($_FILES['lot-img'])) {
        if (empty($_FILES['lot-img']['name'])) {
            $errors['lot-img'] = 'Не выбран файл изображения';
        }
        else {
            $file_path = __DIR__ . '\\uploads\\';
            $file_name = $_FILES['lot-img']['name'];
            move_uploaded_file($_FILES['lot-img']['tmp_name'], $file_path . $file_name);
            //проверка на ожидаемый графический формат
            $type = "";
            $ext = "";
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $ftype = finfo_file($finfo, $file_path . $file_name);
                $pos = strpos($ftype, '/');
                $type = substr($ftype, 0, $pos);
                $ext = substr($ftype, $pos + 1);
                finfo_close($finfo);
            }
            else {
                //Открытие базы данных fileinfo не удалось;
                $pos = strpos($file_name, '.');
                $ext = substr($file_name, $pos + 1);
                $type = 'image';
            }
            //замена оригинального имени на случайное
            $new_file_name = uniqid() . '.' . $ext;
            if ($type == 'image') {
                rename($file_path . $file_name, $file_path . $new_file_name);
                $lot_info['lot-img'] =  "uploads/" . $new_file_name;
            }
            else {
                $errors['lot-img'] = 'Укажите файл с графическими данными';
            }
        }
    }
    else {
        $errors['lot-img'] = 'Не выбран файл изображения';
    }

    //Проверка полей на ожидаемый формат
    //проверка формата поля с датой
    if (!is_date_valid($lot_info['lot-date'])) {
        $errors['lot-date'] = "Неверный формат даты";
    }
    else {
        //дата должна быть новее сегодняшней
        $now = date_create('now');
        $lot = date_create_from_format('Y-m-d', $lot_info['lot-date']);
        $diff = date_diff($now, $lot);
        $days = date_interval_format($diff, "%r%d");
        if ($days < 0)  {
            $errors['lot-date'] = 'Укажите более новую дату';
        }
    }
    if (count($errors) == 0) {
        $cat_id = 0;
        foreach ($catsArray as $cat) {
            if ($cat['name'] == $lot_info['category']) {
                $cat_id = $cat['id'];
                break;
            }
        }
        //запишем данные лота в базу
        $sql = "INSERT INTO lots (dt_add, name, descr, img_url, price, dt_fin, rate_step, cat_id, autor_id)"
        . " VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = db_get_prepare_stmt($link, $sql, [$lot_info['lot-name'], $lot_info['message'],
        $lot_info['lot-img'], $lot_info['lot-rate'], $lot_info['lot-date'], $lot_info['lot-step'], $cat_id]);
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            $last_id = mysqli_insert_id($link);
            header("Location: lot.php?lot_id=" . $last_id);
        }
        else {
            $error = mysqli_error($link);
            $add_content = include_template('error.php', ['error' => $error]);
            print("Ошибка MySQL: " . $error);
        }
    }
}

if (empty($error)) {
    $add_content = include_template('Addtempl.php', [
        'catsInfo' => $catsArray,
        'lotInfo' => $lot_info,
        'errors' => $errors,
        'dictionary' => $dictionary
]);
}

print($add_content);
