<?php
function getDB(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $host = 'localhost';
        $user = 'root';
        $password = 'root';
        $database = 'benkonek';

        $conn = new mysqli($host, $user, $password, $database);
        if ($conn->connect_error) {
            error_log('BenKonek DB: ' . $conn->connect_error);
            http_response_code(503);
            die('Database connection failed.');
        }

        $conn->set_charset('utf8mb4');
    }

    return $conn;
}
