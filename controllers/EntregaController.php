<?php
require_once __DIR__ . '/../models/Entrega.php';

class EntregaController {
    private $model;

    public function __construct($db) {
        $this->model = new Entrega($db);
    }

    public function listar($fechaInicio = '',$fechaFin = '', $trabajadorId = '', $usuarioId = '') {
        return $this->model->listar($fechaInicio, $fechaFin, $trabajadorId, $usuarioId);
    }

    public function obtener($id) {
        return $this->model->obtener($id);
    }

    public function crear($data, $detalles) {
        return $this->model->crear($data, $detalles);
    }

    public function editar($id, $data, $detalles) {
        return $this->model->editar($id, $data, $detalles);
    }

    public function eliminar($id) {
        return $this->model->eliminar($id);
    }

    public function listarProductos() {
        return $this->model->listarProductos();
    }

    public function listarTrabajadores() {
        return $this->model->listarTrabajadores();
    }

    public function listarUsuarios() {
        return $this->model->listarUsuarios();
    }

}
