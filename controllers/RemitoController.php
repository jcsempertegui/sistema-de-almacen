<?php
require_once __DIR__ . '/../models/Remito.php';

class RemitoController {
    private $model;

    public function __construct($db) {
        $this->model = new Remito($db);
    }

    public function listarAvanzado($inicio = '', $fin = '', $numero = '', $tipo = '', $usuario = '') {
        return $this->model->listarAvanzado($inicio, $fin, $numero, $tipo, $usuario);
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

    public function listarTipos() {
        return $this->model->listarTipos();
    }

    public function listarProductos() {
        return $this->model->listarProductos();
    }

    public function listarUsuarios() {
        return $this->model->listarUsuarios();
    }
}
