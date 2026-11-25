<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Indicadores resumen
$totalVentas    = $pdo->query("SELECT COALESCE(SUM(total_pagar), 0) FROM pedidos_ventas WHERE estado_pedido != 'Cancelado'")->fetchColumn();
$totalPedidos   = $pdo->query("SELECT COUNT(*) FROM pedidos_ventas")->fetchColumn();
$pendientes     = $pdo->query("SELECT COUNT(*) FROM pedidos_ventas WHERE estado_pedido = 'Pendiente'")->fetchColumn();
$totalProductos = $pdo->query("SELECT COUNT(*) FROM productos WHERE estado = 1")->fetchColumn();
$stockBajo      = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock_actual <= 20 AND estado = 1")->fetchColumn();

// Últimos 6 pedidos recibidos
$ultimosPedidos = $pdo->query("
    SELECT pv.*, u.nombres, u.apellidos, d.nombre_distrito
    FROM pedidos_ventas pv
    INNER JOIN usuarios u ON pv.id_cliente = u.id_usuario
    INNER JOIN distritos_arequipa d ON pv.id_distrito = d.id_distrito
    ORDER BY pv.id_pedido DESC
    LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador | Abarrotes Arequipa Express</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="dashboard.php">
            <i class="bi bi-speedometer2"></i> Admin | Abarrotes Arequipa Express
        </a>
        <div class="navbar-nav ms-auto align-items-lg-center gap-2">
            <a class="nav-link active" href="dashboard.php">Resumen</a>
            <a class="nav-link" href="productos.php">Productos (Inventario)</a>
            <a class="nav-link" href="pedidos.php">Pedidos</a>
            <a class="nav-link" href="distritos.php">Tarifas 16 Distritos</a>
            <a class="btn btn-outline-light btn-sm ms-2" href="../index.php">Ver Tienda</a>
            <a class="btn btn-danger btn-sm" href="../auth/logout.php">Salir</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0">Panel de Control Administrativo</h3>
            <small class="text-muted">Bienvenido, <?= htmlspecialchars($_SESSION['nombres'] . ' ' . $_SESSION['apellidos']) ?></small>
        </div>
        <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Sistema Operativo</span>
    </div>

    <!-- Tarjetas de Indicadores -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-danger text-white">
                <div class="card-body">
                    <div class="small opacity-75">Ingresos Totales</div>
                    <h3 class="fw-bold mb-0">S/ <?= number_format($totalVentas, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white">
                <div class="card-body">
                    <div class="small opacity-75">Pedidos Registrados</div>
                    <h3 class="fw-bold mb-0"><?= $totalPedidos ?> <small class="fs-6 fw-normal">(<?= $pendientes ?> pend.)</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white">
                <div class="card-body">
                    <div class="small opacity-75">Productos Activos</div>
                    <h3 class="fw-bold mb-0"><?= $totalProductos ?> ítems</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-warning text-dark">
                <div class="card-body">
                    <div class="small">Alerta Stock Bajo (≤20)</div>
                    <h3 class="fw-bold mb-0"><?= $stockBajo ?> productos</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Últimos Pedidos -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bag-check text-danger"></i> Últimos Pedidos en Arequipa Metropolitana</span>
            <a href="pedidos.php" class="btn btn-sm btn-outline-dark">Gestionar todos →</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th># Pedido</th>
                        <th>Cliente</th>
                        <th>Distrito Arequipa</th>
                        <th>Método Pago</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ultimosPedidos)): ?>
                        <tr><td colspan="7" class="text-center text-muted">Aún no hay pedidos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ultimosPedidos as $p): ?>
                            <tr>
                                <td class="fw-bold">#<?= str_pad($p['id_pedido'], 5, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?></td>
                                <td><?= htmlspecialchars($p['nombre_distrito']) ?></td>
                                <td><?= htmlspecialchars($p['metodo_pago']) ?></td>
                                <td class="fw-bold text-danger">S/ <?= number_format($p['total_pagar'], 2) ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($p['estado_pedido']) ?></span></td>
                                <td class="small text-muted"><?= $p['fecha_pedido'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>