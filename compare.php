<?php

use App\DatabaseConnection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

require_once __DIR__ . '/vendor/autoload.php';

// Соединение с базой данных
$db = DatabaseConnection::getInstance();
$mysqli = $db->getConnection();

// Создание таблицы "supplier", если она не существует
$query = "SELECT
    o.line AS order_line,
    s.line AS supplier_line,
    o.size,
    o.article,
    o.price AS order_price,
    s.price AS supplier_price
FROM
    `order` o
JOIN
    supplier s ON o.size = s.size AND o.article = s.article
WHERE
    o.price != s.price;
";

// Выполнение запроса
$result = $mysqli->query($query);

// Массив для хранения номеров строк с расхождениями
$discrepancyRows = [];

// Проверка на наличие результатов
if ($result->num_rows > 0) {
    // Вывод заголовка таблицы
    echo "
        <h2>Результаты сравнения двух таблиц:</h2>
        <table>
            <tr>
                <th>№<br>п/п</th>
                <th>Номер строки<br> заказа</th>
                <th>Номер строки<br> поставщика</th>
                <th>Размер</th>
                <th>Артикул</th>
                <th>Прайс<br> заказа</th>
                <th>Прайс<br> поставщика</th>
            </tr>";

    // Инициализация порядкового номера
    $counter = 1;

    // Вывод результатов
    while ($row = $result->fetch_assoc()) {
        // Извлечение данных из строки результата
        $orderLine = $row['order_line'];
        $supplierLine = $row['supplier_line'];
        $size = $row['size'];
        $article = $row['article'];
        $orderPrice = $row['order_price'];
        $supplierPrice = $row['supplier_price'];
        $discrepancyRows[] = $orderLine;

        // Вывод строки таблицы с данными
        echo "<tr>
                <td>$counter</td>
                <td>$orderLine</td>
                <td>$supplierLine</td>
                <td>$size</td>
                <td>$article</td>
                <td>$orderPrice</td>
                <td>$supplierPrice</td>
              </tr>";
        $counter++;
    }

    // Закрытие таблицы
    echo "</table>";

    sort($discrepancyRows);

    $fileName = '18. Nike 2024 зима-весна.xlsx';
    $inputFileName = __DIR__ . '/upload/' . $fileName;

    echo 'Количество расхождений в цене: ' . count($discrepancyRows) . '.';

// Создаем объект PhpSpreadsheet и загружаем файл
    $spreadsheet = IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();
    // Сохранение изменений в файле Excel
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

    foreach ($discrepancyRows as $line) {
        $sheet->getStyle('A' . $line . ':AM' . $line)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FF0000');
    }

    try {
        $writer->save($inputFileName);
        echo "Изменения сохранены в файле Excel.";
    } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
        echo "Ошибка при сохранении изменений: " . $e->getMessage();
    }
} else {
    echo "Нет расхождений в цене товаров.";
}

// Освобождение результата
$result->free();

// Закрытие соединения с базой данных
$mysqli->close();
?>

<h2>Страница сравнения товаров</h2>
<ul>
    <li>
        <a href="index.php">Вернуться на главную страницу</a>
    </li>
</ul>
