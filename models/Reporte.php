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
        $sql = "SELECT r.fecha, 
                       r.numero, 
                       p.nombre AS producto,
                       GROUP_CONCAT(CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos,
                       dr.cantidad, 
                       u.usuario, 
                       r.campo
                FROM remito r
                INNER JOIN detalle_remito dr ON dr.remito_id = r.id
                INNER JOIN producto p ON p.id = dr.producto_id
                LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
                LEFT JOIN atributo a ON a.id = ap.atributo_id
                INNER JOIN usuario u ON u.id = r.usuario_id
                WHERE r.tipo_remito_id = 1"; // 1 = ingreso (entrada)
    
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
    
        $sql .= " GROUP BY r.fecha, r.numero, p.id, dr.cantidad, u.usuario, r.campo
                  ORDER BY r.fecha DESC";
    
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

    public function ListarTrabajadores() {
        $sql = "SELECT id, nombre, apellido_paterno, apellido_materno FROM trabajador ORDER BY nombre ASC";
        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // 📌 Listar todas las categorías de productos
    public function listarCategorias() {
        $sql = "SELECT id, nombre FROM categoria ORDER BY nombre ASC";
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

    public function salidas($fechaInicio = '', $fechaFin = '', $usuarioId = '', $productoId = '', $numero = '') {
        $sql = "SELECT r.fecha, 
                       r.numero, 
                       p.nombre AS producto,
                       GROUP_CONCAT(CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos,
                       dr.cantidad, 
                       u.usuario, 
                       r.campo
                FROM remito r
                INNER JOIN detalle_remito dr ON dr.remito_id = r.id
                INNER JOIN producto p ON p.id = dr.producto_id
                LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
                LEFT JOIN atributo a ON a.id = ap.atributo_id
                INNER JOIN usuario u ON u.id = r.usuario_id
                WHERE r.tipo_remito_id = 2"; // 2 = salida (egreso)
    
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
    
        $sql .= " GROUP BY r.fecha, r.numero, p.id, dr.cantidad, u.usuario, r.campo
                  ORDER BY r.fecha DESC";
    
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function entregas($fechaInicio = '', $fechaFin = '', $trabajadorId = '', $usuarioId = '', $productoId = '') {
        $sql = "SELECT e.fecha,
                       CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', t.apellido_materno) AS trabajador,
                       e.inspector,
                       p.nombre AS producto,
                       GROUP_CONCAT(CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos,
                       de.cantidad,
                       u.usuario,
                       e.campo
                FROM entrega e
                INNER JOIN detalle_entrega de ON de.entrega_id = e.id
                INNER JOIN producto p ON p.id = de.producto_id
                LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
                LEFT JOIN atributo a ON a.id = ap.atributo_id
                INNER JOIN usuario u ON u.id = e.usuario_id
                INNER JOIN trabajador t ON t.id = e.trabajador_id
                WHERE 1=1";
    
        $params = [];
        $types  = "";
    
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
        if (!empty($productoId)) {
            $sql .= " AND p.id = ?";
            $params[] = $productoId;
            $types .= "i";
        }
    
        $sql .= " GROUP BY e.id, p.id, de.cantidad, u.usuario, e.campo, t.id, e.fecha
                  ORDER BY e.fecha DESC";
    
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function movimientos($fechaInicio = '', $fechaFin = '', $categoriaId = '', $productoId = '') {
        $sql = "
        SELECT
            p.id AS producto_id,
            p.nombre AS producto,
            c.nombre AS categoria,
            COALESCE(attr.atributos, '') AS atributos,
            COALESCE((
                SELECT SUM(dr2.cantidad) 
                FROM detalle_remito dr2 
                LEFT JOIN remito r2 ON r2.id = dr2.remito_id 
                WHERE dr2.producto_id = p.id 
                AND r2.tipo_remito_id = 1
                AND (? = '' OR r2.fecha BETWEEN ? AND ?)
            ), 0) AS total_entradas,
            COALESCE((
                SELECT SUM(dr3.cantidad) 
                FROM detalle_remito dr3 
                LEFT JOIN remito r3 ON r3.id = dr3.remito_id 
                WHERE dr3.producto_id = p.id 
                AND r3.tipo_remito_id = 2
                AND (? = '' OR r3.fecha BETWEEN ? AND ?)
            ), 0) AS total_salidas,
            COALESCE((
                SELECT SUM(de2.cantidad) 
                FROM detalle_entrega de2 
                LEFT JOIN entrega e2 ON e2.id = de2.entrega_id 
                WHERE de2.producto_id = p.id 
                AND (? = '' OR e2.fecha BETWEEN ? AND ?)
            ), 0) AS total_entregas,
            p.stock AS stock_actual
        FROM producto p
        LEFT JOIN categoria c ON p.categoria_id = c.id
        LEFT JOIN (
            SELECT ap.producto_id,
                   GROUP_CONCAT(CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos
            FROM atributo_producto ap
            LEFT JOIN atributo a ON a.id = ap.atributo_id
            GROUP BY ap.producto_id
        ) attr ON attr.producto_id = p.id
        WHERE 1=1
        ";
    
        $types = '';
        $params = [];
    
        // Parámetros para entradas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $types .= 'sss';
            $params = array_merge($params, [$fechaInicio, $fechaInicio, $fechaFin]);
        } else {
            $types .= 'sss';
            $params = array_merge($params, ['', '', '']);
        }
    
        // Parámetros para salidas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $types .= 'sss';
            $params = array_merge($params, [$fechaInicio, $fechaInicio, $fechaFin]);
        } else {
            $types .= 'sss';
            $params = array_merge($params, ['', '', '']);
        }
    
        // Parámetros para entregas
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            $types .= 'sss';
            $params = array_merge($params, [$fechaInicio, $fechaInicio, $fechaFin]);
        } else {
            $types .= 'sss';
            $params = array_merge($params, ['', '', '']);
        }
    
        if (!empty($categoriaId)) {
            $sql .= " AND p.categoria_id = ?";
            $types .= 'i';
            $params[] = $categoriaId;
        }
    
        if (!empty($productoId)) {
            $sql .= " AND p.id = ?";
            $types .= 'i';
            $params[] = $productoId;
        }
    
        $sql .= " GROUP BY p.id, p.nombre, c.nombre, attr.atributos, p.stock ORDER BY p.nombre ASC";
    
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $bind_names[] = $types;
            for ($i = 0; $i < count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }
    
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }    
}
?>
