<?php
require_once __DIR__ . '/../models/Entrega.php';

class EntregaController {
    private $model;

    public function __construct($conn) {
        $this->model = new Entrega($conn);
    }

    public function listar($fechaInicio = '', $fechaFin = '', $trabajadorId = '', $usuarioId = '') {
        return $this->model->listar($fechaInicio, $fechaFin, $trabajadorId, $usuarioId);
    }

    public function obtener($id) {
        return $this->model->obtener($id);
    }

    public function crear($data, $detalles) {
        try {
            return $this->model->crear($data, $detalles);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function editar($id, $data, $detalles) {
        try {
            return $this->model->editar($id, $data, $detalles);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
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
