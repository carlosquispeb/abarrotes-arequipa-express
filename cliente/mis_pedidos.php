<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$idCliente = (int)$_SESSION['id_usuario'];
$stmt = $pdo->prepare("
    SELECT pv.*, d.nombre_distrito
    FROM pedidos_ventas pv
    INNER JOIN distritos_arequipa d ON pv.id_distrito = d.id_distrito
    WHERE pv.id_cliente = ?
    ORDER BY pv.id_pedido DESC
");
$stmt->execute([$idCliente]);
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos | Abarrotes Arequipa Express</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-danger shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php"><i class="bi bi-shop"></i> Abarrotes Arequipa Express</a>
        <a href="../index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Volver al Catálogo</a>
    </div>
</nav>

<div class="container pb-5">
    <h3 class="fw-bold mb-4"><i class="bi bi-receipt-cutoff text-danger"></i> Historial de Mis Pedidos</h3>

    <?php if (isset($_GET['exito'])): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i> <strong>¡Pedido #<?= (int)$_GET['exito'] ?> confirmado con éxito!</strong> El stock ha sido actualizado automáticamente.
        </div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">Aún no has realizado ningún pedido.</div>
    <?php else: ?>
        <?php foreach ($pedidos as $ped): ?>
            <?php
            $stmtDet = $pdo->prepare("
                SELECT dp.*, p.nombre
                FROM detalle_pedido dp
                INNER JOIN productos p ON dp.id_producto = p.id_producto
                WHERE dp.id_pedido = ?
            ");
            $stmtDet->execute([$ped['id_pedido']]);
            $detalles = $stmtDet->fetchAll();

            $badgeColor = match ($ped['estado_pedido']) {
                'Pendiente'  => 'bg-warning text-dark',
                'En camino'  => 'bg-info text-dark',
                'Entregado'  => 'bg-success',
                'Cancelado'  => 'bg-danger',
                default      => 'bg-secondary'
            };
            ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3">
                    <div>
                        <span class="fw-bold fs-5">Pedido #<?= str_pad($ped['id_pedido'], 5, '0', STR_PAD_LEFT) ?></span>
                        <span class="text-muted small ms-2"><?= $ped['fecha_pedido'] ?></span>
                    </div>
                    <span class="badge <?= $badgeColor ?> fs-6"><?= htmlspecialchars($ped['estado_pedido']) ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-3 small">
                        <div class="col-md-6">
                            <strong>Distrito de entrega:</strong> <?= htmlspecialchars($ped['nombre_distrito']) ?><br>
                            <strong>Dirección:</strong> <?= htmlspecialchars($ped['direccion_exacta']) ?>
                            <?= $ped['referencia'] ? '(' . htmlspecialchars($ped['referencia']) . ')' : '' ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <strong>Método de pago:</strong> <?= htmlspecialchars($ped['metodo_pago']) ?><br>
                            <strong>Subtotal:</strong> S/ <?= number_format($ped['subtotal'], 2) ?> |
                            <strong>Delivery:</strong> S/ <?= number_format($ped['costo_delivery'], 2) ?><br>
                            <span class="fs-5 fw-bold text-danger">Total Pagado: S/ <?= number_format($ped['total_pagar'], 2) ?></span>
                        </div>
                    </div>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">P. Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $d): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['nombre']) ?></td>
                                    <td class="text-center"><?= $d['cantidad'] ?></td>
                                    <td class="text-end">S/ <?= number_format($d['precio_unitario'], 2) ?></td>
                                    <td class="text-end">S/ <?= number_format($d['subtotal_linea'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>