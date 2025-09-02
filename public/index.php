<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<?php include_once __DIR__ . '/../includes/header.php'; ?>

<h1>Bienvenido al Sistema de Almacén</h1>
<p>Desde aquí puede gestionar productos, categorías, atributos y usuarios.</p>

<div class="row mt-4">
  <div class="col-md-4">
    <div class="card text-white bg-primary mb-3">
      <div class="card-body">
        <h5 class="card-title">Productos</h5>
        <p class="card-text">Gestione el inventario y consulte stock disponible.</p>
        <a href="../views/productos/listar.php" class="btn btn-light">Ir a Productos</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card text-white bg-success mb-3">
      <div class="card-body">
        <h5 class="card-title">Categorías</h5>
        <p class="card-text">Administre categorías de productos.</p>
        <a href="../views/categorias/listar.php" class="btn btn-light">Ir a Categorías</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card text-white bg-warning mb-3">
      <div class="card-body">
        <h5 class="card-title">Atributos</h5>
        <p class="card-text">Defina atributos vinculados a categorías.</p>
        <a href="../views/atributos/listar.php" class="btn btn-dark">Ir a Atributos</a>
      </div>
    </div>
  </div>
</div>

<!-- Tarjeta Remitos -->
<div class="col-md-4">
  <div class="card text-white bg-info mb-3">
    <div class="card-body">
      <h5 class="card-title">Remitos</h5>
      <p class="card-text">Entradas/Egresos y ajustes de stock.</p>
      <a href="<?= BASE_URL ?>/views/remitos/listar.php" class="btn btn-light">Ir a Remitos</a>
    </div>
  </div>
</div>


<?php include_once __DIR__ . '/../includes/footer.php'; ?>
