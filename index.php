<?php

use App\DatabaseConnection;

require_once __DIR__ . '/vendor/autoload.php';


$db = DatabaseConnection::getInstance();
$mysqli = $db->getConnection();
$sql_query = "SELECT foo FROM excel-comparison";
$result = $mysqli->query($sql_query);


