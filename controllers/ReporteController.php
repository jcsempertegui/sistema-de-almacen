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
    
    public function listarTrabajadores() {
        return $this->model->listarTrabajadores();
    }

    public function salidas($fechaInicio = '', $fechaFin = '', $usuarioId = '', $productoId = '', $numero = '') {
        return $this->model->salidas($fechaInicio, $fechaFin, $usuarioId, $productoId, $numero);
    }

    public function entregas($fechaInicio = '', $fechaFin = '', $trabajadorId = '', $usuarioId = '', $productoId = '') {
    return $this->model->entregas($fechaInicio, $fechaFin, $trabajadorId, $usuarioId, $productoId);
    }

    public function stock($categoriaId = '', $productoId = '', $usuarioId = '') {
    return $this->model->stock($categoriaId, $productoId, $usuarioId);
    }

    public function listarCategorias() {
    return $this->model->listarCategorias();
    }

    public function movimientos($fechaInicio = '', $fechaFin = '', $categoriaId = '', $productoId = '') {
    return $this->model->movimientos($fechaInicio, $fechaFin, $categoriaId, $productoId);
    }
    
}
?>
