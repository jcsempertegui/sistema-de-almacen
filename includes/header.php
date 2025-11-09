<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/BackupController.php';

// opcional: ejecutar verificación automática de backup (no rompe la app si falla)
try {
  require_once __DIR__ . '/../controllers/BackupController.php';
  $backupCtl = new BackupController($conn);
  $auto = $backupCtl->verificarAutomatico();
  // $auto puede ser false o array con success/mensaje
  if (is_array($auto) && $auto['success']) {
      // opcional: guardar mensaje en session para mostrar en UI
      $_SESSION['backup_msg'] = $auto['mensaje'];
  }
} catch (Exception $e) {
  error_log("Error verif backup automático: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sistema de Almacén</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <script src="https://kit.fontawesome.com/42c8b8b4c8.js" crossorigin="anonymous"></script> <!-- FontAwesome -->
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color:rgba(5, 115, 189, 0.9);">
    <div class="container-fluid">
    <!-- 🔗 Logo clickeable que redirige al index -->
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/public/index.php">
      <img src="<?= BASE_URL ?>/public/img/confi2.png" 
           alt="Logo del Sistema" 
           class="mb-3" 
           style="max-width:180px; height:auto; cursor:pointer;">
    </a>      
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
    </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-bold">

          <!-- Inventario -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="inventarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:rgb(255, 255, 255);">
              <i class="fas fa-boxes" ></i> Inventario
            </a>
            <ul class="dropdown-menu"  style="background-color:rgb(255, 160, 71);">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/productos/listar.php">📦 Productos</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/categorias/listar.php">🗂 Categorías</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/atributos/listar.php">🏷 Atributos</a></li>
            </ul>
          </li>

          <!-- Movimientos -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="movimientosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:rgb(255, 255, 255);">
              <i class="fas fa-exchange-alt"></i> Movimientos
            </a>
            <ul class="dropdown-menu" style="background-color:rgb(255, 160, 71);">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/remitos/listar.php">📥/📤 Remitos</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/entregas/listar.php">📦 Entregas</a></li>
            </ul>
          </li>

          <!-- Trabajadores -->
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/views/trabajadores/listar.php" style="color:rgb(255, 255, 255);"><i class="fas fa-users"></i> Trabajadores</a>
          </li>

          <!-- Reportes -->

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="reportesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color:rgb(255, 255, 255);">
                <i class="fas fa-chart-line"></i> Reportes
              </a>
              <ul class="dropdown-menu" aria-labelledby="reportesDropdown" style="background-color:rgb(255, 160, 71);">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/entradas.php">📥 Entradas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/salidas.php">📤 Salidas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/entregas.php">📦 Entregas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/views/reportes/movimientos.php">🔄 Movimientos</a></li>
              </ul>
            </li>
            <?php if ($_SESSION['rol'] == 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= BASE_URL ?>/views/usuarios/listar.php" style="color:rgb(255, 255, 255);"><i class="fas fa-user-cog"></i> Usuarios</a>
            </li>
            <?php endif; ?>
            <?php if ($_SESSION['rol'] == 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/views/backup/index.php" style="color:rgb(255, 255, 255);"><i class="fas fa-users"></i>Back-Up</a></li>
            <?php endif; ?>
        </ul>

        <!-- Usuario -->
        <div class="dropdown">
          <a class="btn btn-outline-dark dropdown-toggle fw-bold" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="color:rgb(255, 255, 255);">
            👤 <?= $_SESSION['usuario'] ?> (<?= $_SESSION['rol'] ?>)
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/public/logout.php"><i class="fas fa-sign-out-alt" ></i> Salir</a></li>
          </ul>
        </div>

      </div>
    </div>
  </nav>

  <div class="container mt-4">
