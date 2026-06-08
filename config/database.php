<?php
function getDB(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli('localhost', 'root', 'root', 'benkonek');
        if ($conn->connect_error) {
            error_log('BenKonek DB: ' . $conn->connect_error);
            http_response_code(503);
            die('<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>503</title>'
              . '<style>body{font-family:monospace;padding:3rem;background:#efe7d3;color:#211c17}'
              . 'h1{font-size:4rem;margin:0;border-bottom:4px solid #211c17;padding-bottom:.5rem}'
              . 'p{margin-top:1rem}</style></head><body>'
              . '<h1>503</h1><p>Layanan sedang tidak tersedia. Coba beberapa saat lagi.</p>'
              . '</body></html>');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
