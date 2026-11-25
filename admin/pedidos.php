<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pedido'], $_POST['estado_pedido'])) {
    $stmt = $pdo->prepare("UPDATE pedidos_ventas SET estado_pedido = ? WHERE id_pedido = ?");
    $stmt->execute([$_POST['estado_pedido'], (int)$_POST['id_pedido']]);
    header("Location: pedidos.php?actualizado=1");
    exit;
}

$pedidos = $pdo->query("
    SELECT pv.*, u.nombres, u.apellidos, u.telefono, d.nombre_distrito
    FROM pedidos_ventas pv
    INNER JOIN usuarios u ON pv.id_cliente = u.id_usuario
    INNER JOIN distritos_arequipa d ON pv.id_distrito = d.id_distrito
    ORDER BY pv.id_pedido DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos | Admin</title>
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
            <a class="nav-link active" href="pedidos.php">Pedidos</a>
            <a class="nav-link" href="distritos.php">Tarifas Distritos</a>
            <a class="btn btn-danger btn-sm" href="../auth/logout.php">Salir</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <h3 class="fw-bold mb-3"><i class="bi bi-truck text-danger"></i> Control de Despacho y Pedidos Distritales</h3>

    <?php if (isset($_GET['actualizado'])): ?>
        <div class="alert alert-success">Estado del pedido actualizado correctamente.</div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th># Pedido</th>
                        <th>Cliente / Teléfono</th>
                        <th>Distrito y Dirección</th>
                        <th>Pago</th>
                        <th>Total</th>
                        <th>Estado de Despacho</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $ped): ?>
                        <tr>
                            <td class="fw-bold">#<?= str_pad($ped['id_pedido'], 5, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($ped['nombres'] . ' ' . $ped['apellidos']) ?></strong><br>
                                <small class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($ped['telefono']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-dark"><?= htmlspecialchars($ped['nombre_distrito']) ?></span><br>
                                <small><?= htmlspecialchars($ped['direccion_exacta']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($ped['metodo_pago']) ?></td>
                            <td class="fw-bold text-danger">S/ <?= number_format($ped['total_pagar'], 2) ?></td>
                            <td>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="id_pedido" value="<?= $ped['id_pedido'] ?>">
                                    <select name="estado_pedido" class="form-select form-select-sm">
                                        <?php foreach (['Pendiente', 'En camino', 'Entregado', 'Cancelado'] as $est): ?>
                                            <option value="<?= $est ?>" <?= $ped['estado_pedido'] === $est ? 'selected' : '' ?>><?= $est ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>