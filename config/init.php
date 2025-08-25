<?php
// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración de base de datos
require_once __DIR__ . '/db.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

// Verificar si el usuario tiene el rol necesario para acceder
function verificarRol($rolRequerido) {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != $rolRequerido) {
        die("Acceso denegado. No tienes permisos suficientes.");
    }
}