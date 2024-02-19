<?php

use App\DatabaseConnection;
use PhpOffice\PhpSpreadsheet\IOFactory;

require_once __DIR__ . '/vendor/autoload.php';

// Соединение с базой данных
$db = DatabaseConnection::getInstance();
$mysqli = $db->getConnection();

// Создание таблицы "supplier", если она не существует
$query = "CREATE TABLE IF NOT EXISTS `supplier` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `line` INT,
    `size` VARCHAR(255),
    `article` VARCHAR(255),
    `price` VARCHAR(255)
)";

$mysqli->query($query);

$fileName = $_FILES["supplierUpload"]["name"];
$tmpName = $_FILES["supplierUpload"]["tmp_name"];
$uploadPath = __DIR__ . '/upload/' . $fileName;

// Проверяем, был ли файл загружен
if (!isset($fileName) || !file_exists($uploadPath)) {
    echo "Ошибка загрузки файла.";
}

// Перемещаем файл в папку upload
move_uploaded_file($tmpName, $uploadPath);

// Очистка таблицы "supplier" от старых данных
try {
    $queryTruncateTable = "TRUNCATE TABLE `supplier`";
    if ($mysqli->query($queryTruncateTable)) {
        echo "Таблица 'supplier' успешно очищена от старых данных.<br>";
    } else {
        throw new Exception("Ошибка при очистке таблицы 'supplier': " . $mysqli->error);
    }
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}

// Создаем объект для чтения файла Excel
$reader = IOFactory::createReader('Xls');
$spreadsheet = $reader->load($uploadPath);

// Получаем первый лист в книге
$sheet = $spreadsheet->getActiveSheet();

// Определение границ цикла
$highestRow = $sheet->getHighestRow();
$firstRowWithData = 2;

try {
// Перебор строк с учетом проверки наличия данных и использованием подготовленного запроса
    for ($i = $firstRowWithData; $i <= $highestRow; $i++) {
        $line = $i; // Номер строки

        // Чтение данных из определенных столбцов
        $size = $sheet->getCell('E' . $i)->getValue();
        $article = $sheet->getCell('B' . $i)->getValue();
        $price = $sheet->getCell('G' . $i)->getValue();

// Проверка наличия данных в ячейке размера
        if (!is_null($size)) {
// Подготовка SQL-запроса с использованием подготовленных запросов
            $query = "INSERT INTO `supplier`(`line`, `size`, `article`, `price`) VALUES (?, ?, ?, ?)";
            $statement = $mysqli->prepare($query);

            // Привязка параметров к значениям данных
            $statement->bind_param("isss", $line, $size, $article, $price);

            // Выполнение запроса
            $statement->execute();

            // Закрытие запроса
            $statement->close();
        }
    }

// Вывод сообщения об успешном завершении операции
    echo "Данные из файла <b>$fileName</b> успешно загружены в базу данных.<br>";

    // Получение номера строки с первой записью в таблице `supplier`
    $firstLineQuery = "SELECT MIN(`line`) AS first_line FROM `supplier`";
    $firstLineResult = $mysqli->query($firstLineQuery);
    $firstLine = $firstLineResult->fetch_assoc()['first_line'];

    // Получение номера строки с последней записью в таблице `supplier`
    $lastLineQuery = "SELECT MAX(`line`) AS last_line FROM `supplier`";
    $lastLineResult = $mysqli->query($lastLineQuery);
    $lastLine = $lastLineResult->fetch_assoc()['last_line'];

    // Получение количества записей в таблице `supplier`
    $countQuery = "SELECT COUNT(*) AS count FROM `supplier`";
    $countResult = $mysqli->query($countQuery);
    $count = $countResult->fetch_assoc()['count'];

    echo "Начало выборки со строки <b>№ $firstLine</b> по строку <b>№ $lastLine</b>.<br>
 Количество записей: <b>$count</b> <br>";
} catch (\Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
<ul>
    <li><a href="index.php">Вернуться на главную</a></li>
</ul>

