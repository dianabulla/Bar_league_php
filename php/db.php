<?php
$host = "localhost";
$port = 3307;
$user = "root";
$pass = "Diana2025"; // coloca tu contraseña si tienes
$dbname = "bar_league"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>

