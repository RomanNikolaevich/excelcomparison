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

$fileName = $_FILES["orderUpload"]["name"];
$tmpName = $_FILES["orderUpload"]["tmp_name"];
$uploadPath = __DIR__ . '/upload/' . $fileName;

// Проверяем, был ли файл загружен
if (!isset($fileName) || !file_exists($uploadPath)) {
    echo "Ошибка загрузки файла.";
}

// Перемещаем файл в папку upload
move_uploaded_file($tmpName, $uploadPath);

// Очистка таблицы "order" от старых данных
try {
    $queryTruncateTable = "TRUNCATE TABLE `order`";
    if ($mysqli->query($queryTruncateTable)) {
        echo "Таблица 'order' успешно очищена от старых данных.<br>";
    } else {
        throw new Exception("Ошибка при очистке таблицы 'order': " . $mysqli->error);
    }
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}

// Загрузка данных из файла
$excel = Excel::open($uploadPath);
$result = $excel->readRows();
$totalRows = count($result);
$firstRowWithData = 4;

try {
    for ($i = $firstRowWithData; $i <= $totalRows; $i++) {
        $rowData = $result[$i];
        $line = $i;
        $size = isset($rowData['A']) ? $mysqli->real_escape_string($rowData['A']) : null;
        $article = isset($rowData['C']) ? $mysqli->real_escape_string($rowData['C']) : null;
        $price = isset($rowData['AL']) ? $mysqli->real_escape_string($rowData['AL']) : null;

        $query = "INSERT INTO `order`(`line`, `size`, `article`, `price`) VALUES (?, ?, ?, ?)";
        $statement = $mysqli->prepare($query);
        $statement->bind_param("isss", $line, $size, $article, $price);
        $statement->execute();
        $statement->close();
    }

    // Вывод сообщения об успешном завершении операции
    echo "Данные из файла <b>$fileName</b> успешно загружены в базу данных.<br>";

    // Получение номера строки с первой записью в таблице `order`
    $firstLineQuery = "SELECT MIN(`line`) AS first_line FROM `order`";
    $firstLineResult = $mysqli->query($firstLineQuery);
    $firstLine = $firstLineResult->fetch_assoc()['first_line'];

    // Получение номера строки с последней записью в таблице `order`
    $lastLineQuery = "SELECT MAX(`line`) AS last_line FROM `order`";
    $lastLineResult = $mysqli->query($lastLineQuery);
    $lastLine = $lastLineResult->fetch_assoc()['last_line'];

    // Получение количества записей в таблице `order`
    $countQuery = "SELECT COUNT(*) AS count FROM `order`";
    $countResult = $mysqli->query($countQuery);
    $count = $countResult->fetch_assoc()['count'];

    echo "Начало выборки со строки <b>№ $firstLine</b> по строку <b>№ $lastLine</b>.<br>
        Количество записей: <b>$count</b> <br>";
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}


?>
<ul>
    <li>
        <a href="index.php">Вернуться на главную</a>
    </li>
</ul>


