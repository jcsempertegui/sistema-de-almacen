<?php
class Producto {
    private $conn;

    public function __construct($db) {
        // MUY IMPORTANTE: usar $db (no $conn)
        $this->conn = $db;
    }

    // Listar productos con sus atributos
    public function listar() {
        $sql = "SELECT p.id, p.nombre, c.nombre as categoria, p.unidad, p.stock
                FROM producto p
                INNER JOIN categoria c ON p.categoria_id = c.id
                ORDER BY p.id DESC";
        $result = $this->conn->query($sql);
        $productos = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($productos as &$prod) {
            $stmt = $this->conn->prepare("
                SELECT a.nombre as atributo, ap.valor
                FROM atributo_producto ap
                INNER JOIN atributo a ON ap.atributo_id = a.id
                WHERE ap.producto_id = ?
                ORDER BY a.nombre ASC
            ");
            $stmt->bind_param("i", $prod['id']);
            $stmt->execute();
            $atributos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $textoAtributos = [];
            foreach ($atributos as $at) {
                $textoAtributos[] = $at['atributo'] . ": " . $at['valor'];
            }
            $prod['atributos'] = $textoAtributos ? implode(", ", $textoAtributos) : "-";
        }

        return $productos;
    }

    // Obtener un producto + atributos
    public function obtener($id) {
        $sql = "SELECT * FROM producto WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();

        $sqlA = "SELECT ap.atributo_id, a.nombre, ap.valor
                 FROM atributo_producto ap
                 INNER JOIN atributo a ON ap.atributo_id = a.id
                 WHERE ap.producto_id = ?
                 ORDER BY a.nombre ASC";
        $stmtA = $this->conn->prepare($sqlA);
        $stmtA->bind_param("i", $id);
        $stmtA->execute();
        $producto['atributos'] = $stmtA->get_result()->fetch_all(MYSQLI_ASSOC);

        return $producto;
    }

    public function crear($data, $atributos = []) {
        // 1) Buscar productos con mismo nombre/categoría/unidad
        $stmt = $this->conn->prepare("SELECT id FROM producto WHERE nombre=? AND categoria_id=? AND unidad=?");
        $stmt->bind_param("sis", $data['nombre'], $data['categoria_id'], $data['unidad']);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
            $productoId = $row['id'];
    
            // 2) Obtener atributos de ese producto
            $stmt2 = $this->conn->prepare("SELECT atributo_id, valor FROM atributo_producto WHERE producto_id=?");
            $stmt2->bind_param("i", $productoId);
            $stmt2->execute();
            $atributosExistentes = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    
            // 3) Comparar con los atributos nuevos
            if ($this->compararAtributos($atributosExistentes, $atributos)) {
                return false; // 🚫 Ya existe producto con mismo nombre + atributos exactos
            }
        }
    
        // 4) Insertar producto
        $stmt = $this->conn->prepare("INSERT INTO producto (nombre, categoria_id, unidad, stock) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisi", $data['nombre'], $data['categoria_id'], $data['unidad'], $data['stock']);
        if (!$stmt->execute()) {
            return false;
        }
    
        $productoId = $this->conn->insert_id;
    
        // 5) Insertar atributos
        if (!empty($atributos)) {
            foreach ($atributos as $attr) {
                $stmt2 = $this->conn->prepare("INSERT INTO atributo_producto (producto_id, atributo_id, valor) VALUES (?, ?, ?)");
                $stmt2->bind_param("iis", $productoId, $attr['atributo_id'], $attr['valor']);
                $stmt2->execute();
            }
        }
    
        return true;
    }
    
    // 🔎 Comparar dos sets de atributos
    private function compararAtributos($existentes, $nuevos) {
        if (count($existentes) !== count($nuevos)) {
            return false;
        }
    
        foreach ($existentes as $e) {
            $found = false;
            foreach ($nuevos as $n) {
                if ($e['atributo_id'] == $n['atributo_id'] && $e['valor'] === $n['valor']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }
        return true; // ✅ Todos coinciden → duplicado exacto
    }
        // Editar producto (reemplaza atributos)
    public function editar($id, $data) {
        $sql = "UPDATE producto SET nombre = ?, categoria_id = ?, unidad = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sisi", $data['nombre'], $data['categoria_id'], $data['unidad'], $id);
        $stmt->execute();

        // Borrar atributos previos
        $del = $this->conn->prepare("DELETE FROM atributo_producto WHERE producto_id = ?");
        $del->bind_param("i", $id);
        $del->execute();

        // Insertar nuevamente
        if (!empty($data['atributos'])) {
            foreach ($data['atributos'] as $atributo_id => $valor) {
                if ($valor === '' || $valor === null) continue;
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
        try {
            // Verificar si el producto está asociado a entregas o remitos
            $verificar = $this->conn->prepare("
                SELECT COUNT(*) as total FROM detalle_entrega WHERE producto_id = ?
                UNION
                SELECT COUNT(*) as total FROM detalle_remito WHERE producto_id = ?
            ");
            $verificar->bind_param("ii", $id, $id);
            $verificar->execute();
            $resultados = $verificar->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($resultados as $row) {
                if ($row['total'] > 0) {
                    throw new Exception("No se puede eliminar el producto porque está asociado a entregas o remitos.");
                }
            }

            // Si no hay relaciones, eliminar
            $stmt = $this->conn->prepare("DELETE FROM producto WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception("No se encontró el producto o no se pudo eliminar.");
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }    
    // Listar categorías
    public function listarCategorias() {
        $sql = "SELECT * FROM categoria ORDER BY nombre ASC";
        $res = $this->conn->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // Listar atributos por categoría
    public function listarAtributosPorCategoria($categoria_id) {
        $sql = "SELECT id, nombre FROM atributo WHERE categoria_id = ? ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $categoria_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
