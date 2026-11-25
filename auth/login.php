<?php
require_once '../config/conexion.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND estado = 1 LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombres']    = $usuario['nombres'];
        $_SESSION['apellidos']  = $usuario['apellidos'];
        $_SESSION['rol']        = $usuario['rol'];

        if ($usuario['rol'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        $error = "Correo electrónico o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Abarrotes Arequipa Express</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-danger text-white text-center py-3 rounded-top-4">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-shop"></i> Abarrotes Arequipa Express</h4>
                    <small>Acceso al Sistema de Ventas y Pedidos</small>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_GET['registrado'])): ?>
                        <div class="alert alert-success">¡Cuenta creada con éxito! Ya puedes iniciar sesión.</div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold py-2">
                            <i class="bi bi-box-arrow-in-right"></i> Ingresar
                        </button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <p class="mb-1 small">¿Aún no tienes cuenta de cliente?</p>
                        <a href="registro.php" class="btn btn-outline-dark btn-sm">Crear cuenta nueva</a>
                        <a href="../index.php" class="btn btn-link btn-sm text-secondary">Volver al catálogo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>