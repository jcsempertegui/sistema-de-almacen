<?php
class Entrega {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 📌 Listar entregas con filtros avanzados
    public function listar($fechaInicio = '', $fechaFin = '', $trabajadorId = '', $usuarioId = '') {
        $sql = "SELECT e.id, e.fecha, CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', t.apellido_materno) as trabajador, 
                       u.usuario as registrado_por, e.campo, e.inspector
                FROM entrega e
                INNER JOIN trabajador t ON e.trabajador_id = t.id
                INNER JOIN usuario u ON e.usuario_id = u.id
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($fechaInicio)) {
            $sql .= " AND DATE(e.fecha) >= ?";
            $params[] = $fechaInicio;
            $types .= "s";
        }

        if (!empty($fechaFin)) {
            $sql .= " AND DATE(e.fecha) <= ?";
            $params[] = $fechaFin;
            $types .= "s";
        }

        if (!empty($trabajadorId)) {
            $sql .= " AND e.trabajador_id = ?";
            $params[] = $trabajadorId;
            $types .= "i";
        }

        if (!empty($usuarioId)) {
            $sql .= " AND e.usuario_id = ?";
            $params[] = $usuarioId;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 📌 Obtener entrega con sus detalles
    public function obtener($id) {
        $sql = "SELECT e.*, 
                       CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', t.apellido_materno) as trabajador_nombre
                FROM entrega e
                INNER JOIN trabajador t ON e.trabajador_id = t.id
                WHERE e.id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $entrega = $stmt->get_result()->fetch_assoc();

        $sqlDet = "SELECT de.id, de.producto_id, p.nombre,
                          GROUP_CONCAT(CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos,
                          de.cantidad, de.motivo
                   FROM detalle_entrega de
                   INNER JOIN producto p ON de.producto_id = p.id
                   LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
                   LEFT JOIN atributo a ON a.id = ap.atributo_id
                   WHERE de.entrega_id=?
                   GROUP BY de.id";
        $stmtD = $this->conn->prepare($sqlDet);
        $stmtD->bind_param("i", $id);
        $stmtD->execute();
        $entrega['detalles'] = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);

        return $entrega;
    }

    // 📌 Crear entrega (validando stock)
    public function crear($data, $detalles) {
        $this->conn->begin_transaction();

        try {
            // Validar stock antes de insertar
            foreach ($detalles as $d) {
                $stmtCheck = $this->conn->prepare("SELECT nombre, stock FROM producto WHERE id = ?");
                $stmtCheck->bind_param("i", $d['producto_id']);
                $stmtCheck->execute();
                $producto = $stmtCheck->get_result()->fetch_assoc();

                if (!$producto) {
                    throw new Exception("Producto con ID {$d['producto_id']} no encontrado.");
                }
                if ($producto['stock'] < $d['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto '{$producto['nombre']}'. Stock actual: {$producto['stock']}, solicitado: {$d['cantidad']}.");
                }
            }

            // Insertar entrega
            $sql = "INSERT INTO entrega (trabajador_id, usuario_id, fecha, campo, inspector)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iisss", $data['trabajador_id'], $data['usuario_id'], $data['fecha'], $data['campo'], $data['inspector']);
            $stmt->execute();
            $entregaId = $this->conn->insert_id;

            // Insertar detalles y reducir stock
            foreach ($detalles as $d) {
                $stmtD = $this->conn->prepare("INSERT INTO detalle_entrega (entrega_id, producto_id, cantidad, motivo) VALUES (?, ?, ?, ?)");
                $stmtD->bind_param("iiis", $entregaId, $d['producto_id'], $d['cantidad'], $d['motivo']);
                $stmtD->execute();

                $updateStock = $this->conn->prepare("UPDATE producto SET stock = stock - ? WHERE id = ?");
                $updateStock->bind_param("ii", $d['cantidad'], $d['producto_id']);
                $updateStock->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Error al crear la entrega: " . $e->getMessage());
        }
    }

    // 📌 Editar entrega (con validación de stock)
    public function editar($id, $data, $detalles) {
        $this->conn->begin_transaction();

        try {
            // Revertir stock previo
            $sqlOld = "SELECT producto_id, cantidad FROM detalle_entrega WHERE entrega_id=?";
            $stmtOld = $this->conn->prepare($sqlOld);
            $stmtOld->bind_param("i", $id);
            $stmtOld->execute();
            $oldDetalles = $stmtOld->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($oldDetalles as $od) {
                $updateStock = $this->conn->prepare("UPDATE producto SET stock = stock + ? WHERE id = ?");
                $updateStock->bind_param("ii", $od['cantidad'], $od['producto_id']);
                $updateStock->execute();
            }

            // Validar stock
            foreach ($detalles as $d) {
                $stmtCheck = $this->conn->prepare("SELECT nombre, stock FROM producto WHERE id = ?");
                $stmtCheck->bind_param("i", $d['producto_id']);
                $stmtCheck->execute();
                $producto = $stmtCheck->get_result()->fetch_assoc();

                if (!$producto) {
                    throw new Exception("Producto con ID {$d['producto_id']} no encontrado.");
                }
                if ($producto['stock'] < $d['cantidad']) {
                    throw new Exception("Stock insuficiente para el producto '{$producto['nombre']}'. Stock actual: {$producto['stock']}, solicitado: {$d['cantidad']}.");
                }
            }

            // Actualizar cabecera
            $sql = "UPDATE entrega SET trabajador_id=?, fecha=?, campo=?, inspector=? WHERE id=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("isssi", $data['trabajador_id'], $data['fecha'], $data['campo'], $data['inspector'], $id);
            $stmt->execute();

            // Eliminar detalles previos
            $deleteDetalles = $this->conn->prepare("DELETE FROM detalle_entrega WHERE entrega_id=?");
            $deleteDetalles->bind_param("i", $id);
            $deleteDetalles->execute();

            // Insertar nuevos detalles
            foreach ($detalles as $d) {
                $stmtD = $this->conn->prepare("INSERT INTO detalle_entrega (entrega_id, producto_id, cantidad, motivo) VALUES (?, ?, ?, ?)");
                $stmtD->bind_param("iiis", $id, $d['producto_id'], $d['cantidad'], $d['motivo']);
                $stmtD->execute();

                $updateStock = $this->conn->prepare("UPDATE producto SET stock = stock - ? WHERE id = ?");
                $updateStock->bind_param("ii", $d['cantidad'], $d['producto_id']);
                $updateStock->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw new Exception("Error al editar la entrega: " . $e->getMessage());
        }
    }

    // 📌 Eliminar entrega
    public function eliminar($id) {
        $sqlOld = "SELECT producto_id, cantidad FROM detalle_entrega WHERE entrega_id=?";
        $stmtOld = $this->conn->prepare($sqlOld);
        $stmtOld->bind_param("i", $id);
        $stmtOld->execute();
        $oldDetalles = $stmtOld->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($oldDetalles as $od) {
            $this->conn->query("UPDATE producto SET stock = stock + {$od['cantidad']} WHERE id={$od['producto_id']}");
        }

        $this->conn->query("DELETE FROM detalle_entrega WHERE entrega_id=$id");
        $stmt = $this->conn->prepare("DELETE FROM entrega WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Helpers
    public function listarProductos() {
        $sql = "SELECT p.id, p.nombre, p.stock,
                       GROUP_CONCAT(DISTINCT CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos
                FROM producto p
                LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
                LEFT JOIN atributo a ON a.id = ap.atributo_id
                GROUP BY p.id";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function listarTrabajadores() {
        return $this->conn->query("SELECT id, nombre, apellido_paterno, apellido_materno FROM trabajador ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
    }

    public function listarUsuarios() {
        return $this->conn->query("SELECT id, usuario FROM usuario ORDER BY usuario ASC")->fetch_all(MYSQLI_ASSOC);
    }
}
