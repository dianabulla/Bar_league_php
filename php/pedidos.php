<?php
header('Content-Type: application/json');
include('db.php');

// Detectar si viene en JSON (raw) o como form-data
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['accion'])) {
    $_POST = $input;
}

// Insertar nuevo pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    if (!isset($_POST['id_usuario'], $_POST['estado'], $_POST['detalle'])) {
        echo json_encode([
            "success" => false,
            "error" => "Faltan campos requeridos"
        ]);
        exit;
    }

    $id_usuario = $_POST['id_usuario'];
    $estado = $_POST['estado'];
    $detalle = $_POST['detalle'];

    // Si viene como string, convertirlo a array
    if (is_string($detalle)) {
        $detalle = json_decode($detalle, true);
    }

    $conn->begin_transaction();

    try {
        // Insertar pedido
        $conn->query("INSERT INTO pedidos (id_usuario, estado) VALUES ($id_usuario, '$estado')");
        $id_pedido = $conn->insert_id;

        // Insertar detalles
        foreach ($detalle as $item) {
            $id_producto = $item['id_producto'];
            $cantidad = $item['cantidad'];
            $subtotal = $item['subtotal'];

            $conn->query("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, subtotal) 
                          VALUES ($id_pedido, $id_producto, $cantidad, $subtotal)");
        }

        $conn->commit();
        echo json_encode(["success" => true, "id_pedido" => $id_pedido]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    exit;
}

// Consultar pedidos por fecha (opcional)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'listar') {
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

        $det = $conn->query("SELECT dp.*, pr.nombre 
                             FROM detalle_pedido dp
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

// Si no hay acción válida
echo json_encode([
    "success" => false,
    "error" => "Acción no válida"
]);


// Actualizar estado del pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_estado') {
    $id_pedido = $_POST['id_pedido'];
    $estado = $_POST['estado'];
    $venta_generada = false;
    $id_venta = null;

    // Actualizar estado del pedido
    $conn->query("UPDATE pedidos SET estado = '$estado' WHERE id_pedido = $id_pedido");

    // Si es "pagado", generar venta
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
