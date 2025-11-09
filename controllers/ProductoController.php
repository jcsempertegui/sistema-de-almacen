<?php
require_once __DIR__ . '/../models/Producto.php';

class ProductoController {
    private $model;

    public function __construct($db) {
        $this->model = new Producto($db);
    }

    public function listar() {
        return $this->model->listar();
    }

    public function obtener($id) {
        return $this->model->obtener($id);
    }

    public function crear($data, $atributos = []) {
        return $this->model->crear($data, $atributos);
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

    public function listarAtributosPorCategoria($categoria_id) {
        return $this->model->listarAtributosPorCategoria($categoria_id);
    }
}
