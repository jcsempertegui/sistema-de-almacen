<?php
require_once __DIR__ . '/../models/Remito.php';

class RemitoController {
    private $model;

    public function __construct($conn) {
        $this->model = new Remito($conn);
    }

    public function listar($filtros = []) {
        return $this->model->listar($filtros);
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

    // Selects
    public function listarUsuarios()   { return $this->model->listarUsuarios(); }
    public function listarTipos()      { return $this->model->listarTipos(); }
    public function listarProductos()  { return $this->model->listarProductos(); }
}
