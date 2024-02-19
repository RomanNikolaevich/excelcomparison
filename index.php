<?php

require_once __DIR__ . '/vendor/autoload.php';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Сравнение файлов</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Страница загрузки файлов для сравнения</h2>

<h3>1. Загрузите файла заказа</h3>
<form action="order.php" method="post" enctype="multipart/form-data">
    <label for="orderUpload" class="upload-btn">Выберите файл заказа</label>
    <input type="file" name="orderUpload" id="orderUpload" class="file-input">
    <input type="submit" value="Загрузить" name="orderSubmit" class="submit-btn">
</form>
<span class="warning-text">Это может занять некоторое время, дождитесь окончания загрузки</span>

<h3>2. Загрузите файл от поставщика</h3>
<form action="supplier.php" method="post" enctype="multipart/form-data">
    <label for="supplierUpload" class="upload-btn">Выберите файл поставки</label>
    <input type="file" name="supplierUpload" id="supplierUpload" class="file-input">
    <input type="submit" value="Загрузить" name="supplierSubmit" class="submit-btn">
</form>
<span class="warning-text">Это может занять некоторое время, дождитесь окончания загрузки</span>

<h3>3. Нажмите кнопку сравнить, чтобы провести сравнение таблиц</h3>
<a href="compare.php" class="submit-btn">Сравнить</a><br>
<span class="warning-text last">
    Это может занять некоторое время, примерно до 4-х минут, дождитесь окончания загрузки
</span>
</body>
</html>


