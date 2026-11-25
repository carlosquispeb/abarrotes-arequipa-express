<?php
require_once 'config/conexion.php';

// Inicializar carrito en sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar acción de agregar producto al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_id'])) {
    $idProd = (int)$_POST['agregar_id'];
    $stmtProd = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ? AND estado = 1 AND stock_actual > 0");
    $stmtProd->execute([$idProd]);
    $prod = $stmtProd->fetch();

    if ($prod) {
        if (isset($_SESSION['carrito'][$idProd])) {
            if ($_SESSION['carrito'][$idProd]['cantidad'] < $prod['stock_actual']) {
                $_SESSION['carrito'][$idProd]['cantidad']++;
            }
        } else {
            $_SESSION['carrito'][$idProd] = [
                'id_producto' => $prod['id_producto'],
                'nombre'      => $prod['nombre'],
                'precio'      => (float)$prod['precio_venta'],
                'stock'       => (int)$prod['stock_actual'],
                'cantidad'    => 1
            ];
        }
    }
    header("Location: index.php?agregado=1");
    exit;
}

// Obtener categorías activas
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id_categoria ASC")->fetchAll();

// Filtros por categoría y búsqueda por nombre
$catFiltro = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda  = trim($_GET['buscar'] ?? '');

$sql = "SELECT p.*, c.nombre AS categoria_nombre
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE p.estado = 1";
$params = [];

if ($catFiltro > 0) {
    $sql .= " AND p.id_categoria = ?";
    $params[] = $catFiltro;
}
if ($busqueda !== '') {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
$sql .= " ORDER BY p.id_producto ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Contar ítems totales en el carrito
$totalItemsCarrito = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarrotes Arequipa Express | Tienda Online Distrital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Barra de navegación superior -->
<nav class="navbar navbar-expand-lg navbar-dark bg-danger shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-shop"></i> Abarrotes Arequipa Express
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php"><i class="bi bi-grid"></i> Catálogo</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-warning fw-bold position-relative" href="cliente/carrito.php">
                        <i class="bi bi-cart3"></i> Carrito
                        <span class="badge bg-dark ms-1"><?= $totalItemsCarrito ?></span>
                    </a>
                </li>
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="btn btn-dark btn-sm" href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Panel Admin</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="cliente/mis_pedidos.php"><i class="bi bi-receipt"></i> Mis Pedidos</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <span class="navbar-text text-white small mx-2">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nombres']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="auth/logout.php">Salir</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm" href="auth/login.php">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light text-danger fw-bold btn-sm" href="auth/registro.php">Registrarse</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Banner principal -->
<div class="bg-dark text-white py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-1">Tus abarrotes frescos hasta la puerta de tu hogar</h2>
                <p class="mb-0 text-light opacity-75">
                    <i class="bi bi-geo-alt-fill text-warning"></i> Cobertura de delivery en los <strong>16 distritos de Arequipa Metropolitana</strong>.
                </p>
            </div>
            <div class="col-md-4 mt-3 mt-md-0">
                <form method="GET" class="d-flex">
                    <?php if ($catFiltro > 0): ?>
                        <input type="hidden" name="categoria" value="<?= $catFiltro ?>">
                    <?php endif; ?>
                    <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar arroz, aceite, leche..." value="<?= htmlspecialchars($busqueda) ?>">
                    <button class="btn btn-warning fw-bold" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <?php if (isset($_GET['agregado'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill"></i> Producto añadido a tu carrito de compras.
            <a href="cliente/carrito.php" class="alert-link ms-2">Ir al carrito →</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtro por Categorías -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="index.php" class="btn <?= $catFiltro === 0 ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm fw-semibold">
            Todos los productos
        </a>
        <?php foreach ($categorias as $cat): ?>
            <a href="index.php?categoria=<?= $cat['id_categoria'] ?>"
               class="btn <?= $catFiltro === (int)$cat['id_categoria'] ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm fw-semibold">
                <?= htmlspecialchars($cat['nombre']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Cuadrícula de Productos -->
    <div class="row g-4">
        <?php if (count($productos) === 0): ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">No se encontraron productos con los filtros seleccionados.</div>
            </div>
        <?php endif; ?>

        <?php foreach ($productos as $p): ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                    <img src="<?= htmlspecialchars($p['imagen_url'] ?: 'https://via.placeholder.com/400x260?text=Abarrotes') ?>"
                         class="card-img-top" alt="<?= htmlspecialchars($p['nombre']) ?>"
                         style="height: 185px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-secondary-subtle text-dark mb-2 align-self-start">
                            <?= htmlspecialchars($p['categoria_nombre']) ?>
                        </span>
                        <h6 class="card-title fw-bold mb-1"><?= htmlspecialchars($p['nombre']) ?></h6>
                        <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($p['descripcion']) ?></p>

                        <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                            <span class="fs-5 fw-bold text-danger">S/ <?= number_format($p['precio_venta'], 2) ?></span>
                            <span class="badge <?= $p['stock_actual'] > 10 ? 'bg-success' : 'bg-warning text-dark' ?>">
                                Stock: <?= $p['stock_actual'] ?>
                            </span>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="agregar_id" value="<?= $p['id_producto'] ?>">
                            <button type="submit" class="btn btn-danger w-100 fw-semibold" <?= $p['stock_actual'] <= 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>