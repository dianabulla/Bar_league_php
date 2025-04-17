<?php
header('Content-Type: application/json');
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'listar') {
    $fecha = $_POST['fecha'] ?? '';
    $where = $fecha ? "WHERE DATE(fecha_venta) = '$fecha'" : "";

    $query = "SELECT * FROM ventas $where ORDER BY fecha_venta DESC";
    $result = $conn->query($query);

    $ventas = [];
    while ($row = $result->fetch_assoc()) {
        $ventas[] = $row;
    }

    echo json_encode($ventas);
}
?>
