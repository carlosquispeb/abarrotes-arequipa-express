<?php
$esLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']);

if ($esLocal) {
    // Entorno local (XAMPP)
    $host = 'localhost';
    $db   = 'bd_abarrotes_arequipa';
    $user = 'root';
    $pass = '';
} else {
    // Entorno de producción (InfinityFree)
    $host = 'sql112.infinityfree.com';
    $db   = 'if0_43528052_abarrotes';
    $user = 'if0_43528052';
    $pass = 'teamodaniel02';
}

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error de conexión con la base de datos: " . $e->getMessage());
}