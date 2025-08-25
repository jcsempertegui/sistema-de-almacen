<?php
require_once __DIR__ . '/../models/Atributo.php';

class AtributoController {
    private $model;

    public function __construct($db) {
        $this->model = new Atributo($db);
    }

    public function listar() {
        return $this->model->listar();
    }

    public function crear($data) {
        return $this->model->crear($data);
    }

    public function obtener($id) {
        return $this->model->obtener($id);
    }

    public function editar($id, $data) {
        return $this->model->editar($id, $data);
    }

    public function eliminar($id) {
        return $this->model->eliminar($id);
    }
    
    public function listarCategorias() {
        return $this->model->listarCategorias();
    }
}
