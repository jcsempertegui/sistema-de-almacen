<?php
require_once __DIR__ . '/../models/Categoria.php';

class CategoriaController {
    private $model;

    public function __construct($db) {
        $this->model = new Categoria($db);
    }

    public function listar() {
        return $this->model->listar();
    }

    public function obtener($id) {
        return $this->model->obtener($id);
    }

    public function crear($data) {
        return $this->model->crear($data);
    }

    public function editar($id, $data) {
        return $this->model->editar($id, $data);
    }

    public function eliminar($id) {
        return $this->model->eliminar($id);
    }
}
