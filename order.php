<?php

use App\DatabaseConnection;
use avadim\FastExcelReader\Excel;

require_once __DIR__ . '/vendor/autoload.php';

// Соединение с базой данных
$db = DatabaseConnection::getInstance();
$mysqli = $db->getConnection();

// Создание таблицы "order", если она не существует
$query = "CREATE TABLE IF NOT EXISTS `order` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `line` INT,
    `size` VARCHAR(255),
    `article` VARCHAR(255),
    `price` VARCHAR(255)
)";
$mysqli->query($query);

// Очистка таблицы "order" от старых данных
$queryTruncateTable = "TRUNCATE TABLE `order`";
$mysqli->query($queryTruncateTable);

$fileName = '18. Nike 2024 зима-весна.xlsx';
$inputFileName = __DIR__ . './upload/' . $fileName;

$excel = Excel::open($inputFileName);
// Читаем все значения как плоский массив с текущего листа
$result = $excel->readRows();

// Определение границ цикла
$totalRows = count($result);

$firstRowWithData = 4;

// Перебор массива строк с учетом проверки наличия ключа и использованием подготовленного запроса
for ($i = $firstRowWithData; $i <= $totalRows; $i++) {
    $rowData = $result[$i];

    $line = $i; // номер строки
    $size = isset($rowData['A']) ? $mysqli->real_escape_string($rowData['A']) : null;
    $article = isset($rowData['C']) ? $mysqli->real_escape_string($rowData['C']) : null;
    $price = isset($rowData['AL']) ? $mysqli->real_escape_string($rowData['AL']) : null;

    // Подготовка SQL-запроса с использованием подготовленных запросов
    $query = "INSERT INTO `order`(`line`, `size`, `article`, `price`) VALUES (?, ?, ?, ?)";
    $statement = $mysqli->prepare($query);

    // Привязка параметров к значениям данных
    $statement->bind_param("isss", $line, $size, $article, $price);

    // Выполнение запроса
    $statement->execute();

    // Закрытие запроса
    $statement->close();
}

// Вывод сообщения об успешном завершении операции
echo "Данные успешно загружены в базу данных.<br>";
?>

<a href="index.php">Вернуться на главную</a>

