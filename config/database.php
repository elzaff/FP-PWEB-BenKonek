<?php
function getDB(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli('localhost', 'root', '', 'benkonek');
        if ($conn->connect_error) {
            die('Database connection failed: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
