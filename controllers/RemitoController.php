<?php
require_once __DIR__ . '/../models/Remito.php';

class RemitoController {
    private $model;

    public function __construct($db) {
        $this->model = new Remito($db);
    }

    // 📌 Listar con filtros
    public function listar($filtroFecha = '', $filtroUsuario = '') {
        return $this->model->listar($filtroFecha, $filtroUsuario);
    }

    // 📌 Obtener un remito por ID
    public function obtener($id) {
        return $this->model->obtener($id);
    }

    // 📌 Crear remito + detalles
    public function crear($data, $detalles) {
        return $this->model->crear($data, $detalles);
    }

    // 📌 Editar remito + detalles
    public function editar($id, $data, $detalles) {
        return $this->model->editar($id, $data, $detalles);
    }

    // 📌 Eliminar remito + revertir stock
    public function eliminar($id) {
        return $this->model->eliminar($id);
    }

    // 📌 Listar tipos de remito
    public function listarTipos() {
        return $this->model->listarTipos();
    }

    // 📌 Listar productos
    public function listarProductos() {
        return $this->model->listarProductos();
    }

    // 📌 Listar usuarios
    public function listarUsuarios() {
        return $this->model->listarUsuarios();
    }
}
