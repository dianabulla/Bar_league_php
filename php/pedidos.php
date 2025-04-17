<?php
header('Content-Type: application/json');
include('db.php');

// Mostrar errores (debug)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ACTUALIZAR ESTADO Y GENERAR VENTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'actualizar_estado') {
    $id_pedido = $_POST['id_pedido'];
    $estado = $_POST['estado'];
    $venta_generada = false;
    $id_venta = null;

    $conn->query("UPDATE pedidos SET estado = '$estado' WHERE id_pedido = $id_pedido");

    if ($estado === 'pagado') {
        $result = $conn->query("SELECT SUM(subtotal) AS total FROM detalle_pedido WHERE id_pedido = $id_pedido");
        $total = $result->fetch_assoc()['total'];

        $conn->query("INSERT INTO ventas (id_pedido, total_venta) VALUES ($id_pedido, $total)");
        $id_venta = $conn->insert_id;
        $venta_generada = true;
    }

    echo json_encode([
        "success" => true,
        "venta_generada" => $venta_generada,
        "id_venta" => $id_venta
    ]);
    exit;
}

// LISTAR PEDIDOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'listar') {
    $fecha = $_POST['fecha'] ?? '';
    $where = $fecha ? "WHERE DATE(p.fecha) = '$fecha'" : "";

    $query = "SELECT p.id_pedido, p.fecha, p.estado, u.nombre AS usuario
              FROM pedidos p
              JOIN usuarios u ON p.id_usuario = u.id_usuario
              $where
              ORDER BY p.fecha DESC";

    $result = $conn->query($query);
    $pedidos = [];

    while ($row = $result->fetch_assoc()) {
        $id = $row['id_pedido'];
        $row['detalles'] = [];

        $det = $conn->query("SELECT dp.*, pr.nombre FROM detalle_pedido dp
                             JOIN productos pr ON dp.id_producto = pr.id_producto
                             WHERE dp.id_pedido = $id");

        while ($d = $det->fetch_assoc()) {
            $row['detalles'][] = $d;
        }

        $pedidos[] = $row;
    }

    echo json_encode($pedidos);
    exit;
}

// INSERTAR NUEVO PEDIDO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'insertar') {
    $id_usuario = $_POST['id_usuario'];
    $estado = $_POST['estado'];
    $detalle = json_decode($_POST['detalle'], true);

    $conn->begin_transaction();

    try {
        $conn->query("INSERT INTO pedidos (id_usuario, estado) VALUES ($id_usuario, '$estado')");
        $id_pedido = $conn->insert_id;

        foreach ($detalle as $item) {
            $id_producto = $item['id_producto'];
            $cantidad = $item['cantidad'];
            $subtotal = $item['subtotal'];

            $conn->query("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, subtotal)
                          VALUES ($id_pedido, $id_producto, $cantidad, $subtotal)");
        }

        $conn->commit();
        echo json_encode(["success" => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    exit;
}

// Cualquier otro caso inválido
echo json_encode(["success" => false, "error" => "Acción no válida"]);
exit;
