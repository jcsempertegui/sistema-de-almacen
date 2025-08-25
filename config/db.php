<?php
$host = "localhost";
$user = "root";
$pass = "123456";
$db   = "almacen2";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
