<?php
header('Content-Type: application/json');
include('db.php');



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





if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
}
