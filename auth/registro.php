<?php
require_once '../config/conexion.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres   = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $password  = $_POST['password'] ?? '';

    $check = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        $error = "Este correo electrónico ya se encuentra registrado.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombres, apellidos, email, password, telefono, rol) VALUES (?, ?, ?, ?, ?, 'cliente')");
        $stmt->execute([$nombres, $apellidos, $email, $hash, $telefono]);
        header("Location: login.php?registrado=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente | Abarrotes Arequipa Express</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-dark text-white text-center py-3 rounded-top-4">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-person-plus"></i> Registro de Nuevo Cliente</h4>
                    <small>Delivery en los 16 distritos de Arequipa Metropolitana</small>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nombres</label>
                                <input type="text" name="nombres" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Apellidos</label>
                                <input type="text" name="apellidos" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teléfono / WhatsApp</label>
                            <input type="text" name="telefono" class="form-control" placeholder="958000000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contraseña</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold py-2">Registrarme</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">¿Ya tienes cuenta? Inicia sesión aquí</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>