<?php
// includes/config/config.php
define('DB_HOST', 'mysql');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fundsocial');

// includes/config/database.php
<?php
require_once 'config.php';

function connectDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Koneksi gagal: " . $conn->connect_error);
        }
        $conn->set_charset("utf8");
        return $conn;
    } catch(Exception $e) {
        die("Koneksi error: " . $e->getMessage());
    }
}

function closeDB($conn) {
    $conn->close();
}

// Function untuk mencegah SQL Injection
function escapeString($conn, $string) {
    return mysqli_real_escape_string($conn, $string);
}