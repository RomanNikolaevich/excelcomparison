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

// Очистка таблицы "supplier" от старых данных
$queryTruncateTable = "TRUNCATE TABLE `supplier`";
$mysqli->query($queryTruncateTable);

$fileName = 'NZ_Dnepr1_280124.xls';
$inputFileName = __DIR__ . './upload/' . $fileName;

// Создаем объект для чтения файла Excel
$reader = IOFactory::createReader('Xls');
$spreadsheet = $reader->load($inputFileName);

// Получаем первый лист в книге
$sheet = $spreadsheet->getActiveSheet();

// Определение границ цикла
$highestRow = $sheet->getHighestRow();
$firstRowWithData = 2;

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
echo "Данные успешно загружены в базу данных.<br>";
?>
<ul>
    <li><a href="compare.php">Сравнить таблицы заказов и поставок</a></li>
    <li><a href="index.php">Вернуться на главную</a></li>
</ul>

