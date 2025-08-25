<?php
class Categoria {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT * FROM categoria";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtener($id) {
        $stmt = $this->conn->prepare("SELECT * FROM categoria WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO categoria (nombre) VALUES (?)");
        $stmt->bind_param("s", $data['nombre']);
        return $stmt->execute();
    }

    public function editar($id, $data) {
        $stmt = $this->conn->prepare("UPDATE categoria SET nombre=? WHERE id=?");
        $stmt->bind_param("si", $data['nombre'], $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        // 🔎 Verificar si la categoría tiene atributos asociados
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM atributo WHERE categoria_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res['total'] > 0) {
            return "atributos";
        }

        // 🔎 Verificar si la categoría tiene productos asociados
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM producto WHERE categoria_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res['total'] > 0) {
            return "productos";
        }

        // ✅ Eliminar si no está en uso
        $stmt = $this->conn->prepare("DELETE FROM categoria WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute() ? true : false;
    }
}
