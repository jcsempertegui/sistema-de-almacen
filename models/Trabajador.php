<?php
class Trabajador {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM trabajador ORDER BY nombre, apellido_paterno ASC";
        $res = $this->conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function obtener($id) {
        $sql = "SELECT * FROM trabajador WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crear($data) {
        $sql = "INSERT INTO trabajador (nombre, apellido_paterno, apellido_materno, cargo, nacimiento, telefono)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssss",
            $data['nombre'],
            $data['apellido_paterno'],
            $data['apellido_materno'],
            $data['cargo'],
            $data['nacimiento'],
            $data['telefono']
        );
        return $stmt->execute();
    }

    public function editar($id, $data) {
        $sql = "UPDATE trabajador SET nombre=?, apellido_paterno=?, apellido_materno=?, cargo=?, nacimiento=?, telefono=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssi",
            $data['nombre'],
            $data['apellido_paterno'],
            $data['apellido_materno'],
            $data['cargo'],
            $data['nacimiento'],
            $data['telefono'],
            $id
        );
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM trabajador WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
