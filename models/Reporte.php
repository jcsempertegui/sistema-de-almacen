<?php
class Reporte {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * 📦 Obtener todas las ENTRADAS (remitos de tipo ingreso = tipo_remito_id = 1)
     * Permite filtrar por: fecha inicio, fecha fin, usuario, producto y número de remito.
     */
    public function entradas($fechaInicio = '', $fechaFin = '', $usuarioId = '', $productoId = '', $numero = '') {
        $sql = "SELECT r.id AS remito_id,
                       r.fecha,
                       r.numero,
                       p.id AS producto_id,
                       p.nombre AS producto,
                       dr.cantidad,
                       u.id AS usuario_id,
                       u.usuario AS usuario,
                       r.campo
                FROM remito r
                INNER JOIN detalle_remito dr ON r.id = dr.remito_id
                INNER JOIN producto p ON dr.producto_id = p.id
                INNER JOIN usuario u ON r.usuario_id = u.id
                WHERE r.tipo_remito_id = 1";

        $params = [];
        $types  = "";

        if (!empty($fechaInicio)) {
            $sql .= " AND DATE(r.fecha) >= ?";
            $params[] = $fechaInicio;
            $types .= "s";
        }
        if (!empty($fechaFin)) {
            $sql .= " AND DATE(r.fecha) <= ?";
            $params[] = $fechaFin;
            $types .= "s";
        }
        if (!empty($usuarioId)) {
            $sql .= " AND r.usuario_id = ?";
            $params[] = $usuarioId;
            $types .= "i";
        }
        if (!empty($productoId)) {
            $sql .= " AND p.id = ?";
            $params[] = $productoId;
            $types .= "i";
        }
        if (!empty($numero)) {
            $sql .= " AND r.numero LIKE ?";
            $params[] = "%$numero%";
            $types .= "s";
        }

        $sql .= " ORDER BY r.fecha DESC, r.id DESC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 📋 Listar usuarios
    public function listarUsuarios() {
        $sql = "SELECT id, usuario FROM usuario ORDER BY usuario ASC";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // 📋 Listar productos con sus atributos
    public function listarProductos() {
        $sql = "SELECT p.id, p.nombre,
                       GROUP_CONCAT(DISTINCT CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos
                FROM producto p
                LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
                LEFT JOIN atributo a ON a.id = ap.atributo_id
                GROUP BY p.id
                ORDER BY p.nombre ASC";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>
