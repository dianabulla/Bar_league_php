<?php
header('Content-Type: application/json');
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Soporte para JSON
    if (strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $codigo = $input['codigo'] ?? null;
        $nombre = $input['nombre'] ?? null;
        $contrasena = $input['contrasena'] ?? null;
        $tipo = $input['tipo_usuario'] ?? null;
    } else {
        // Soporte para form-data y x-www-form-urlencoded
        $codigo = $_POST['codigo'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        $contrasena = $_POST['contrasena'] ?? null;
        $tipo = $_POST['tipo_usuario'] ?? null;
    }

    // Validación
    if (!$codigo || !$nombre || !$contrasena || !$tipo) {
        echo json_encode(["success" => false, "error" => "Faltan campos obligatorios"]);
        exit;
    }

    // Insertar en la BD
    $sql = "INSERT INTO usuarios (codigo, nombre, contrasena, tipo_usuario) 
            VALUES ('$codigo', '$nombre', '$contrasena', '$tipo')";

    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
    exit;
}

// GET para listar usuarios
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM usuarios";
    $result = $conn->query($sql);

    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }

    echo json_encode($usuarios);
}
