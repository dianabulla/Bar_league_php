<?php
header('Content-Type: application/json');
include('db.php');

// Listar productos
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT * FROM productos");
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    echo json_encode($productos);
}

// Crear producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $valor = $_POST['valor_unitario'];

    $sql = "INSERT INTO productos (codigo, nombre, descripcion, valor_unitario) VALUES ('$codigo', '$nombre', '$descripcion', '$valor')";

    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}


