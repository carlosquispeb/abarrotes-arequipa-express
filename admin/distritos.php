<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_distrito'], $_POST['costo_delivery'])) {
    $stmt = $pdo->prepare("UPDATE distritos_arequipa SET costo_delivery = ? WHERE id_distrito = ?");
    $stmt->execute([(float)$_POST['costo_delivery'], (int)$_POST['id_distrito']]);
    header("Location: distritos.php?ok=1");
    exit;
}

$distritos = $pdo->query("SELECT * FROM distritos_arequipa ORDER BY id_distrito ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarifas 16 Distritos Arequipa | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="dashboard.php"><i class="bi bi-speedometer2"></i> Admin | Abarrotes Arequipa</a>
        <div class="navbar-nav ms-auto gap-2">
            <a class="nav-link" href="dashboard.php">Resumen</a>
            <a class="nav-link" href="productos.php">Productos</a>
            <a class="nav-link" href="pedidos.php">Pedidos</a>
            <a class="nav-link active" href="distritos.php">Tarifas Distritos</a>
            <a class="btn btn-danger btn-sm" href="../auth/logout.php">Salir</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <h3 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-danger"></i> Tarifario de Delivery — 16 Distritos de Arequipa Metropolitana</h3>

    <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success">Tarifa distrital actualizada correctamente.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 col-lg-8">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Distrito de Arequipa</th>
                        <th style="width: 150px;">Costo Delivery (S/)</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($distritos as $d): ?>
                        <tr>
                            <form method="POST">
                                <input type="hidden" name="id_distrito" value="<?= $d['id_distrito'] ?>">
                                <td><?= $d['id_distrito'] ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($d['nombre_distrito']) ?></td>
                                <td>
                                    <input type="number" step="0.50" name="costo_delivery" value="<?= $d['costo_delivery'] ?>" class="form-control form-control-sm">
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-dark btn-sm">Guardar tarifa</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>