<?php
require_once '../config/conexion.php';

if (!isset($_SESSION['id_usuario']) || empty($_SESSION['carrito']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: carrito.php");
    exit;
}

$idCliente       = (int)$_SESSION['id_usuario'];
$idDistrito      = (int)($_POST['id_distrito'] ?? 0);
$direccionExacta = trim($_POST['direccion_exacta'] ?? '');
$referencia      = trim($_POST['referencia'] ?? '');
$metodoPago      = $_POST['metodo_pago'] ?? 'Contra entrega';

// Validar distrito de Arequipa seleccionado
$stmtDist = $pdo->prepare("SELECT * FROM distritos_arequipa WHERE id_distrito = ? AND estado = 1");
$stmtDist->execute([$idDistrito]);
$distrito = $stmtDist->fetch();

if (!$distrito || $direccionExacta === '') {
    header("Location: carrito.php?error=" . urlencode("Seleccione un distrito válido de Arequipa y escriba su dirección."));
    exit;
}

try {
    $pdo->beginTransaction();

    $subtotal = 0;
    $itemsValidados = [];

    foreach ($_SESSION['carrito'] as $item) {
        $stmtP = $pdo->prepare("SELECT id_producto, nombre, precio_venta, stock_actual FROM productos WHERE id_producto = ? FOR UPDATE");
        $stmtP->execute([$item['id_producto']]);
        $prodBD = $stmtP->fetch();

        if (!$prodBD || $prodBD['stock_actual'] < $item['cantidad']) {
            throw new Exception("Stock insuficiente para el producto: " . ($prodBD['nombre'] ?? 'Desconocido'));
        }

        $lineaSubtotal = $prodBD['precio_venta'] * $item['cantidad'];
        $subtotal += $lineaSubtotal;

        $itemsValidados[] = [
            'id_producto'     => $prodBD['id_producto'],
            'cantidad'        => $item['cantidad'],
            'precio_unitario' => $prodBD['precio_venta'],
            'subtotal_linea'  => $lineaSubtotal
        ];
    }

    $costoDelivery = (float)$distrito['costo_delivery'];
    $totalPagar    = $subtotal + $costoDelivery;

    // 1. Insertar cabecera de venta
    $stmtVenta = $pdo->prepare("INSERT INTO pedidos_ventas (id_cliente, id_distrito, direccion_exacta, referencia, subtotal, costo_delivery, total_pagar, metodo_pago) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtVenta->execute([$idCliente, $idDistrito, $direccionExacta, $referencia, $subtotal, $costoDelivery, $totalPagar, $metodoPago]);
    $idPedido = $pdo->lastInsertId();

    // 2. Insertar detalle y descontar stock en tiempo real
    $stmtDet   = $pdo->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal_linea) VALUES (?, ?, ?, ?, ?)");
    $stmtStock = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?");

    foreach ($itemsValidados as $det) {
        $stmtDet->execute([$idPedido, $det['id_producto'], $det['cantidad'], $det['precio_unitario'], $det['subtotal_linea']]);
        $stmtStock->execute([$det['cantidad'], $det['id_producto']]);
    }

    $pdo->commit();
    unset($_SESSION['carrito']);

    header("Location: mis_pedidos.php?exito=" . $idPedido);
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: carrito.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>