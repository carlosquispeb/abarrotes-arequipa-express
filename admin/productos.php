<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$mensaje = '';

// Operaciones CRUD (Crear, Actualizar, Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $stmt = $pdo->prepare("INSERT INTO productos (id_categoria, codigo_sku, nombre, descripcion, precio_venta, stock_actual, imagen_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            (int)$_POST['id_categoria'],
            trim($_POST['codigo_sku']),
            trim($_POST['nombre']),
            trim($_POST['descripcion']),
            (float)$_POST['precio_venta'],
            (int)$_POST['stock_actual'],
            trim($_POST['imagen_url'])
        ]);
        $mensaje = "Producto registrado correctamente (CREATE).";

    } elseif ($accion === 'actualizar_stock') {
        $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, precio_venta = ?, stock_actual = ? WHERE id_producto = ?");
        $stmt->execute([
            trim($_POST['nombre']),
            (float)$_POST['precio_venta'],
            (int)$_POST['stock_actual'],
            (int)$_POST['id_producto']
        ]);
        $mensaje = "Producto actualizado correctamente (UPDATE).";

    } elseif ($accion === 'eliminar') {
        $stmt = $pdo->prepare("UPDATE productos SET estado = 0 WHERE id_producto = ?");
        $stmt->execute([(int)$_POST['id_producto']]);
        $mensaje = "Producto eliminado del catálogo activo (DELETE).";
    }
}

// Buscador (READ / SEARCH)
$buscar = trim($_GET['buscar'] ?? '');
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id_categoria ASC")->fetchAll();

$sql = "SELECT p.*, c.nombre AS cat_nombre
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        WHERE p.estado = 1";
$params = [];

if ($buscar !== '') {
    $sql .= " AND (p.nombre LIKE ? OR p.codigo_sku LIKE ?)";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
}
$sql .= " ORDER BY p.id_producto DESC";

$stmtProd = $pdo->prepare($sql);
$stmtProd->execute($params);
$productos = $stmtProd->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos (CRUD) | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold text-warning" href="dashboard.php"><i class="bi bi-speedometer2"></i> Admin | Abarrotes Arequipa</a>
        <div class="navbar-nav ms-auto gap-2">
            <a class="nav-link" href="dashboard.php">Resumen</a>
            <a class="nav-link active" href="productos.php">Productos</a>
            <a class="nav-link" href="pedidos.php">Pedidos</a>
            <a class="nav-link" href="distritos.php">Tarifas Distritos</a>
            <a class="btn btn-danger btn-sm" href="../auth/logout.php">Salir</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h3 class="fw-bold mb-0"><i class="bi bi-box-seam text-danger"></i> Gestión de Productos e Inventario</h3>
        <!-- Buscador Admin -->
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Buscar por nombre o SKU..." value="<?= htmlspecialchars($buscar) ?>">
            <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-search"></i> Buscar</button>
            <?php if ($buscar !== ''): ?>
                <a href="productos.php" class="btn btn-outline-secondary btn-sm">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- Formulario Nuevo Producto (CREATE) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white fw-bold">Registrar Nuevo Producto</div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="accion" value="crear">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Código SKU</label>
                    <input type="text" name="codigo_sku" class="form-control" placeholder="ABR-041" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Categoría</label>
                    <select name="id_categoria" class="form-select" required>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Nombre del producto</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej. Aceite Vegetal Sao 900 ml" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small fw-semibold">Precio S/</label>
                    <input type="number" step="0.01" name="precio_venta" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Stock inicial</label>
                    <input type="number" name="stock_actual" class="form-control" value="25" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Descripción corta</label>
                    <input type="text" name="descripcion" class="form-control" placeholder="Presentación y peso del producto">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">URL de imagen</label>
                    <input type="url" name="imagen_url" class="form-control" placeholder="https://...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-danger w-100 fw-bold">+ Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Inventario (READ / UPDATE / DELETE) -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th style="width: 115px;">Precio (S/)</th>
                        <th style="width: 100px;">Stock</th>
                        <th style="width: 190px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $prod): ?>
                        <tr>
                            <form method="POST">
                                <input type="hidden" name="id_producto" value="<?= $prod['id_producto'] ?>">
                                <td class="fw-bold small"><?= htmlspecialchars($prod['codigo_sku']) ?></td>
                                <td>
                                    <input type="text" name="nombre" value="<?= htmlspecialchars($prod['nombre']) ?>" class="form-control form-control-sm">
                                </td>
                                <td><span class="badge bg-secondary-subtle text-dark"><?= htmlspecialchars($prod['cat_nombre']) ?></span></td>
                                <td>
                                    <input type="number" step="0.01" name="precio_venta" value="<?= $prod['precio_venta'] ?>" class="form-control form-control-sm">
                                </td>
                                <td>
                                    <input type="number" name="stock_actual" value="<?= $prod['stock_actual'] ?>" class="form-control form-control-sm">
                                </td>
                                <td class="d-flex gap-1">
                                    <button type="submit" name="accion" value="actualizar_stock" class="btn btn-dark btn-sm" title="Actualizar">
                                        <i class="bi bi-save"></i> Editar
                                    </button>
                                    <button type="submit" name="accion" value="eliminar" class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('¿Eliminar este producto del catálogo?');" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
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