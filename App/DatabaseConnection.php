<?php

namespace App;

use App\DatabaseConnectionInterface;
use mysqli;

class DatabaseConnection implements DatabaseConnectionInterface
{
    private $connection;
    private static $instance;

    private function __construct() {
        $config = include('config.php');
        $this->connection = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }

        if(mysqli_connect_error()) {
            trigger_error("Failed to conencto to MySQL: " . mysql_connect_error(),
                E_USER_ERROR);
        }
    }

    private function __clone() {}

    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}