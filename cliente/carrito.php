<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Actualizar cantidades o eliminar producto del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_id'])) {
        $idDel = (int)$_POST['eliminar_id'];
        unset($_SESSION['carrito'][$idDel]);
    } elseif (isset($_POST['cantidades']) && is_array($_POST['cantidades'])) {
        foreach ($_POST['cantidades'] as $idProd => $cant) {
            $idProd = (int)$idProd;
            $cant   = (int)$cant;
            if (isset($_SESSION['carrito'][$idProd])) {
                if ($cant <= 0) {
                    unset($_SESSION['carrito'][$idProd]);
                } else {
                    $maxStock = $_SESSION['carrito'][$idProd]['stock'];
                    $_SESSION['carrito'][$idProd]['cantidad'] = min($cant, $maxStock);
                }
            }
        }
    }
    header("Location: carrito.php");
    exit;
}

// Obtener los 16 distritos activos de Arequipa Metropolitana
$distritos = $pdo->query("SELECT * FROM distritos_arequipa WHERE estado = 1 ORDER BY id_distrito ASC")->fetchAll();

$subtotal = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras | Abarrotes Arequipa Express</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-danger shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php"><i class="bi bi-shop"></i> Abarrotes Arequipa Express</a>
        <a href="../index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
    </div>
</nav>

<div class="container pb-5">
    <h3 class="fw-bold mb-4"><i class="bi bi-cart3 text-danger"></i> Mi Carrito de Compras y Entrega Distrital</h3>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (empty($_SESSION['carrito'])): ?>
        <div class="card border-0 shadow-sm p-5 text-center rounded-4">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <h4 class="mt-3">Tu carrito está vacío</h4>
            <p class="text-muted">Explora nuestro catálogo de abarrotes con reparto en toda Arequipa.</p>
            <div>
                <a href="../index.php" class="btn btn-danger fw-bold px-4">Ir al Catálogo</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Tabla de productos en carrito -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <form method="POST">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center" style="width: 110px;">Cantidad</th>
                                            <th class="text-end">Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['carrito'] as $item): ?>
                                            <tr>
                                                <td class="fw-semibold"><?= htmlspecialchars($item['nombre']) ?></td>
                                                <td class="text-center">S/ <?= number_format($item['precio'], 2) ?></td>
                                                <td>
                                                    <input type="number" name="cantidades[<?= $item['id_producto'] ?>]"
                                                           value="<?= $item['cantidad'] ?>" min="1" max="<?= $item['stock'] ?>"
                                                           class="form-control form-control-sm text-center">
                                                </td>
                                                <td class="text-end fw-bold">S/ <?= number_format($item['precio'] * $item['cantidad'], 2) ?></td>
                                                <td class="text-end">
                                                    <button type="submit" name="eliminar_id" value="<?= $item['id_producto'] ?>"
                                                            class="btn btn-outline-danger btn-sm" title="Quitar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="../index.php" class="btn btn-outline-secondary btn-sm">+ Agregar más productos</a>
                                <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-arrow-clockwise"></i> Actualizar cantidades</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Resumen y Selector de los 16 Distritos de Arequipa -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-dark text-white fw-bold py-3 rounded-top-4">
                        <i class="bi bi-geo-alt-fill text-warning"></i> Datos de Entrega en Arequipa
                    </div>
                    <div class="card-body p-4">
                        <?php if (!isset($_SESSION['id_usuario'])): ?>
                            <div class="alert alert-warning">
                                Para confirmar tu pedido debes <a href="../auth/login.php" class="fw-bold">iniciar sesión</a> o
                                <a href="../auth/registro.php" class="fw-bold">registrarte gratis</a>.
                            </div>
                        <?php else: ?>
                            <form action="procesar_pedido.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Distrito de entrega (Arequipa Metropolitana)</label>
                                    <select name="id_distrito" id="selectDistrito" class="form-select" required>
                                        <option value="" data-delivery="0">-- Seleccione su distrito --</option>
                                        <?php foreach ($distritos as $d): ?>
                                            <option value="<?= $d['id_distrito'] ?>" data-delivery="<?= $d['costo_delivery'] ?>">
                                                <?= htmlspecialchars($d['nombre_distrito']) ?> (Delivery: S/ <?= number_format($d['costo_delivery'], 2) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Dirección exacta</label>
                                    <input type="text" name="direccion_exacta" class="form-control"
                                           placeholder="Ej. Av. Ejército 412, Urb. Orrantia" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Referencia de ubicación</label>
                                    <input type="text" name="referencia" class="form-control"
                                           placeholder="Ej. Frente al parque / portón verde">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Método de pago</label>
                                    <select name="metodo_pago" class="form-select" required>
                                        <option value="Yape/Plin">Yape / Plin (Al recibir o por QR)</option>
                                        <option value="Contra entrega">Contra entrega (Efectivo)</option>
                                    </select>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Subtotal productos:</span>
                                    <strong id="txtSubtotal" data-val="<?= $subtotal ?>">S/ <?= number_format($subtotal, 2) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-primary">
                                    <span>Costo de delivery distrital:</span>
                                    <strong id="txtDelivery">S/ 0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between fs-5 fw-bold text-danger border-top pt-2 mb-3">
                                    <span>TOTAL A PAGAR:</span>
                                    <span id="txtTotal">S/ <?= number_format($subtotal, 2) ?></span>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 fw-bold py-2">
                                    <i class="bi bi-check2-circle"></i> Confirmar Pedido
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
const selectDistrito = document.getElementById('selectDistrito');
if (selectDistrito) {
    selectDistrito.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const delivery = parseFloat(opt.getAttribute('data-delivery')) || 0;
        const subtotal = parseFloat(document.getElementById('txtSubtotal').getAttribute('data-val')) || 0;
        const total = subtotal + delivery;

        document.getElementById('txtDelivery').textContent = 'S/ ' + delivery.toFixed(2);
        document.getElementById('txtTotal').textContent = 'S/ ' + total.toFixed(2);
    });
}
</script>
</body>
</html>