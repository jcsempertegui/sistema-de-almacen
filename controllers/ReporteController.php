<?php
require_once __DIR__ . '/../models/Reporte.php';

class ReporteController {
    private $model;

    public function __construct($db) {
        $this->model = new Reporte($db);
    }

    public function entradas($fechaInicio = '', $fechaFin = '', $usuarioId = '', $productoId = '', $numero = '') {
        return $this->model->entradas($fechaInicio, $fechaFin, $usuarioId, $productoId, $numero);
    }

    public function listarUsuarios() {
        return $this->model->listarUsuarios();
    }

    public function listarProductos() {
        return $this->model->listarProductos();
    }
}
?>
