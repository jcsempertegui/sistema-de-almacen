<?php
require_once __DIR__ . '/../config/db.php';

$nombre = 'Admin';
$apellido = 'Admin';
$usuario = 'admin';
$contraseña = '12345';
$rol = 'admin';

// Generar el hash de la contraseña
$hash = password_hash($contraseña, PASSWORD_DEFAULT);

// Preparar la consulta
$stmt = $conn->prepare("INSERT INTO usuario (nombre, apellido, usuario, contraseña, rol) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nombre, $apellido, $usuario, $hash, $rol);

// Ejecutar la consulta
if ($stmt->execute()) {
    echo "Usuario creado exitosamente.";
} else {
    echo "Error al crear el usuario: " . $stmt->error;
}
?>
