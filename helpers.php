<?php
/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * is_date_valid('2019-01-01'); // true
 * is_date_valid('2016-02-29'); // true
 * is_date_valid('2019-04-31'); // false
 * is_date_valid('10.10.2010'); // false
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function is_date_valid(string $date) : bool {
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = []) {
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . 
                    mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . 
                        mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function get_noun_plural_form (int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
    case ($mod100 >= 11 && $mod100 <= 20):
        return $many;

    case ($mod10 > 5):
        return $many;

    case ($mod10 === 1):
        return $one;

    case ($mod10 >= 2 && $mod10 <= 4):
        return $two;

    default:
        return $many;
    }
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 *
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array  $data Ассоциативный массив с данными для шаблона
 * 
 * @return string Итоговый HTML
 */
function include_template($name, array $data = []) {
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        print(__DIR__.'file '.$name.' not found! ');
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Класс для работы с базой MySQLi
 */
class MySqliBase
{
    private $_link = null;
    private $_error = null;
    private $_connect = false;

    public function __construct($host, $user, $password, $dbname) 
    {
        $this->_link = mysqli_connect($host, $user, $password, $dbname);
        if ($this->_link) {
            mysqli_set_charset($this->_link, "utf8");
            $this->_connect = true;
        } else {
            $this->_error = mysqli_connect_error();
        }
    }

    public function error() 
    {
        return $this->_error;
    }

    public function query($sql) 
    {
        if ($this->_connect) {
            unset($this->_error);
            $res = mysqli_query($this->_link, $sql);
            if (!$res) {
                $this->_error = mysqli_error($this->_link);
            }
            return $res;
        }
        return null;
    }

    public function ok()
    {
        return $this->_connect;
    }

    public function prepare_stmt($sql, $data = []) 
    {
        return db_get_prepare_stmt($this->_link, $sql, $data);
    }

    public function last_id() 
    {
        if ($this->_connect && !isset($this->_error)) {
            return mysqli_insert_id($this->_link);
        }
        return 0;
    }

    public function escape_str($str)
    {   
        if ($this->_connect) {
            return mysqli_real_escape_string($this->_link, $str);
        }
        return $str;
    }
}
