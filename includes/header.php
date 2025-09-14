<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Almacén</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="<?= BASE_URL ?>/public/index.php">📦 Almacén</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/views/productos/listar.php">Productos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/views/categorias/listar.php">Categorías</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/views/atributos/listar.php">Atributos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/views/remitos/listar.php">Remitos</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/views/entregas/listar.php">Entregas</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/views/trabajadores/listar.php">Trabajadores</a></li>
          <?php if ($_SESSION['rol'] == 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/views/usuarios/listar.php">Usuarios</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="reportesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Reportes
              </a>
              <ul class="dropdown-menu" aria-labelledby="reportesDropdown">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/entradas.php">📥 Entradas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/salidas.php">📤 Salidas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/entregas.php">📦 Entregas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/stock.php">📊 Stock</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/movimientos.php">🔄 Movimientos</a></li>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
        <span class="navbar-text text-light">
          <?= $_SESSION['usuario'] ?> (<?= $_SESSION['rol'] ?>) |
          <a href="<?= BASE_URL ?>/public/logout.php" class="text-danger">Salir</a>
        </span>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
