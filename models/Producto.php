<?php
class Producto {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar productos con atributos
    public function listar() {
        $sql = "SELECT p.id, p.nombre, c.nombre as categoria, p.unidad, p.stock
                FROM producto p
                INNER JOIN categoria c ON p.categoria_id = c.id";
        $result = $this->conn->query($sql);
        $productos = $result->fetch_all(MYSQLI_ASSOC);

        // Traer atributos de cada producto
        foreach ($productos as &$prod) {
            $stmt = $this->conn->prepare("
                SELECT a.nombre as atributo, ap.valor
                FROM atributo_producto ap
                INNER JOIN atributo a ON ap.atributo_id = a.id
                WHERE ap.producto_id = ?
            ");
            $stmt->bind_param("i", $prod['id']);
            $stmt->execute();
            $atributos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            if (!empty($atributos)) {
                $textoAtributos = [];
                foreach ($atributos as $at) {
                    $textoAtributos[] = $at['atributo'] . ": " . $at['valor'];
                }
                $prod['atributos'] = implode(", ", $textoAtributos);
            } else {
                $prod['atributos'] = "-";
            }
        }

        return $productos;
    }

    // Obtener un producto con atributos
    public function obtener($id) {
        $sql = "SELECT * FROM producto WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();

        // Traemos los atributos
        $sqlA = "SELECT ap.atributo_id, a.nombre, ap.valor
                 FROM atributo_producto ap
                 INNER JOIN atributo a ON ap.atributo_id = a.id
                 WHERE ap.producto_id = ?";
        $stmtA = $this->conn->prepare($sqlA);
        $stmtA->bind_param("i", $id);
        $stmtA->execute();
        $producto['atributos'] = $stmtA->get_result()->fetch_all(MYSQLI_ASSOC);

        return $producto;
    }

    // Crear producto (con validación de duplicados exactos)
    public function crear($data, $atributos = []) {
        // Buscar todos los productos con el mismo nombre/categoría/unidad
        $stmt = $this->conn->prepare("SELECT id FROM producto WHERE nombre=? AND categoria_id=? AND unidad=?");
        $stmt->bind_param("sis", $data['nombre'], $data['categoria_id'], $data['unidad']);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $productoId = $row['id'];

            // Obtener atributos existentes
            $stmt2 = $this->conn->prepare("SELECT atributo_id, valor FROM atributo_producto WHERE producto_id=?");
            $stmt2->bind_param("i", $productoId);
            $stmt2->execute();
            $atributosExistentes = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

            // Comparar con los nuevos
            if ($this->compararAtributos($atributosExistentes, $atributos)) {
                return false; // 🚫 Producto duplicado exacto
            }
        }

        // Insertamos producto
        $stmt = $this->conn->prepare("INSERT INTO producto (nombre, categoria_id, unidad, stock) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisi", $data['nombre'], $data['categoria_id'], $data['unidad'], $data['stock']);
        if ($stmt->execute()) {
            $productoId = $this->conn->insert_id;

            // Insertar atributos
            foreach ($atributos as $attr) {
                $stmt2 = $this->conn->prepare("INSERT INTO atributo_producto (producto_id, atributo_id, valor) VALUES (?, ?, ?)");
                $stmt2->bind_param("iis", $productoId, $attr['atributo_id'], $attr['valor']);
                $stmt2->execute();
            }

            return true;
        }
        return false;
    }

    // Comparar arrays de atributos
    private function compararAtributos($existentes, $nuevos) {
        if (count($existentes) !== count($nuevos)) {
            return false;
        }

        foreach ($existentes as $e) {
            $found = false;
            foreach ($nuevos as $n) {
                if ($e['atributo_id'] == $n['atributo_id'] && $e['valor'] == $n['valor']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }
        return true;
    }

    // Editar producto
    public function editar($id, $data) {
        $sql = "UPDATE producto SET nombre=?, categoria_id=?, unidad=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisi", $data['nombre'], $data['categoria_id'], $data['unidad'], $id);
        $stmt->execute();

        // Borramos atributos previos
        $this->conn->query("DELETE FROM atributo_producto WHERE producto_id = $id");

        // Insertamos nuevamente
        if (!empty($data['atributos'])) {
            foreach ($data['atributos'] as $atributo_id => $valor) {
                $sqlA = "INSERT INTO atributo_producto (producto_id, atributo_id, valor) VALUES (?, ?, ?)";
                $stmtA = $this->conn->prepare($sqlA);
                $stmtA->bind_param("iis", $id, $atributo_id, $valor);
                $stmtA->execute();
            }
        }

        return true;
    }

    // Eliminar producto
    public function eliminar($id) {
        $this->conn->query("DELETE FROM atributo_producto WHERE producto_id = $id");
        $sql = "DELETE FROM producto WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Listar categorías
    public function listarCategorias() {
        $sql = "SELECT * FROM categoria ORDER BY nombre ASC";
        $res = $this->conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Listar atributos por categoría
    public function listarAtributosPorCategoria($categoria_id) {
        $sql = "SELECT * FROM atributo WHERE categoria_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
