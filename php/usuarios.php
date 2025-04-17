<?php
header('Content-Type: application/json');
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM usuarios";
    $result = $conn->query($sql);

    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }

    echo json_encode($usuarios);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Detecta si los datos vienen como JSON (Postman)
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if ($data !== null) {
        // Datos en formato JSON
        $codigo = $data['codigo'];
        $nombre = $data['nombre'];
        $contrasena = $data['contrasena'];
        $tipo = $data['tipo_usuario'];
    } else {
        // Datos en formato FormData (desde formulario web)
        $codigo = $_POST['codigo'];
        $nombre = $_POST['nombre'];
        $contrasena = $_POST['contrasena'];
        $tipo = $_POST['tipo_usuario'];
    }

    $sql = "INSERT INTO usuarios (codigo, nombre, contrasena, tipo_usuario) 
            VALUES ('$codigo', '$nombre', '$contrasena', '$tipo')";

    if ($conn->query($sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $conn->error]);
    }
}

?>
