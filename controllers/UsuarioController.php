<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $model;
    public function __construct($conn) { $this->model = new Usuario($conn); }
    public function listar() { return $this->model->listar(); }
    public function obtener($id) { return $this->model->obtener($id); }
    public function crear($data) { return $this->model->crear($data); }
    public function editar($id,$data) { return $this->model->editar($id,$data); }
    public function eliminar($id) { return $this->model->eliminar($id); }
    public function login($u,$c) { return $this->model->login($u,$c); }
}
