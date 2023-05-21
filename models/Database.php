<?php

namespace models;

use mysqli;

class Database
{
    private static $instance = null;
    private $conn;

    public function __construct(mysqli $connection)
    {
        $this->conn = $connection;
    }

    public static function getInstance(mysqli $connection)
    {
        if (self::$instance === null) {
            self::$instance = new self($connection);
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
