<?php
class Atributo {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $sql = "SELECT a.id, a.nombre, c.nombre as categoria
                FROM atributo a
                INNER JOIN categoria c ON a.categoria_id = c.id";
        $result = $this->conn->query($sql);
        $atributos = [];
        while ($row = $result->fetch_assoc()) {
            $atributos[] = $row;
        }
        return $atributos;
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO atributo (nombre, categoria_id) VALUES (?, ?)");
        $stmt->bind_param("si", $data['nombre'], $data['categoria_id']);
        return $stmt->execute();
    }

    public function obtener($id) {
        $stmt = $this->conn->prepare("SELECT * FROM atributo WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function editar($id, $data) {
        $stmt = $this->conn->prepare("UPDATE atributo SET nombre=?, categoria_id=? WHERE id=?");
        $stmt->bind_param("sii", $data['nombre'], $data['categoria_id'], $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        // Verificar si el atributo está en uso
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM atributo_producto WHERE atributo_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
    
        if ($result['total'] > 0) {
            return "en_uso"; // está en uso, no se elimina
        }
    
        $stmt = $this->conn->prepare("DELETE FROM atributo WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function listarCategorias() {
        $sql = "SELECT * FROM categoria";
        $result = $this->conn->query($sql);
        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        return $categorias;
    }
}
