<?php

use App\DatabaseConnection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

require_once __DIR__ . '/vendor/autoload.php';

// Соединение с базой данных
$db = DatabaseConnection::getInstance();
$mysqli = $db->getConnection();

// Создание таблицы "supplier", если она не существует
$query = "SELECT o.line
FROM `order` o
JOIN supplier s ON o.article = s.article AND o.size = s.size
WHERE o.price != s.price
";

// Выполнение запроса
$result = $mysqli->query($query);

// Массив для хранения номеров строк с расхождениями
$discrepancyRows = [];

// Проверка на наличие результатов
if ($result->num_rows > 0) {
    // Вывод результатов
    while ($row = $result->fetch_assoc()) {
        $line = $row['line'];

        $discrepancyRows[] = $line;
    }

    sort($discrepancyRows);

    // Путь к файлу Excel
    $fileName = '18. Nike 2024 зима-весна.xlsx';
    $inputFileName = __DIR__ . '/upload/' . $fileName;

    // Отчет по сравнению
    echo 'Список строк с несоотвествием цены в файле: ' . $fileName . ': <br>' . implode(', ', $discrepancyRows) . '.<br>';

    echo 'Количество расхождений в цене: ' . count($discrepancyRows) . '.';

// Создаем объект PhpSpreadsheet и загружаем файл
    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();
    // Сохранение изменений в файле Excel
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

    foreach ($discrepancyRows as $line) {
        $sheet->getStyle('A' . $line . ':AM' . $line)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FF0000');
    }

    try {
        $writer->save($inputFileName);
        echo "Изменения сохранены в файле Excel.";
    } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
        echo "Ошибка при сохранении изменений: " . $e->getMessage();
    }

} else {
    echo "Расхождений в цене товара не найдено.";
}

// Освобождение результата
$result->free();

// Закрытие соединения с базой данных
$mysqli->close();
?>

<h2>Страница сравнения товаров</h2>
<ul>
    <li>
        <a href="order.php">Внести заказ в базу данных</a>
    </li>
    <li>
        <a href="supplier.php">Внести данные от поставщика</a>
    </li>
    <li>
        <a href="index.php">Вернуться на главную страницу</a>
    </li>
</ul>
