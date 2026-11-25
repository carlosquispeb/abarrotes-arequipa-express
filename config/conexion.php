<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'bd_abarrotes_arequipa');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);

    $stmtCheck = $pdo->prepare("SELECT id_usuario, password FROM usuarios WHERE email = ?");
    $stmtCheck->execute(['admin@abarrotesarequipa.pe']);
    $adminRow = $stmtCheck->fetch();
    if ($adminRow && !password_verify('Admin2026*', $adminRow['password'])) {
        $nuevoHash = password_hash('Admin2026*', PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?")->execute([$nuevoHash, $adminRow['id_usuario']]);
    }
} catch (PDOException $e) {
    die("Error de conexión con la base de datos: " . $e->getMessage());
}
?>