<?php
// Database configuration file

// Read credentials from Railway environment variables, with local fallbacks for development
define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'edoctor');
define('DB_PORT', (int)(getenv('MYSQLPORT') ?: 3306));

// Enable error reporting to catch issues clearly
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Pass DB_PORT as the 5th parameter
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Connection failed: " . $e->getMessage());
}

function closeConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>