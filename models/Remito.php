<?php
class Remito {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 📌 Listar remitos con filtro avanzado
    public function listarAvanzado($inicio = '', $fin = '', $numero = '', $tipo = '', $usuario = '') {
        $sql = "SELECT r.id, r.numero, tr.nombre as tipo, u.usuario as registrado_por, r.fecha
                FROM remito r
                INNER JOIN tipo_remito tr ON r.tipo_remito_id = tr.id
                INNER JOIN usuario u ON r.usuario_id = u.id
                WHERE 1=1";

        $params = [];
        $types  = "";

        if (!empty($inicio)) {
            $sql .= " AND DATE(r.fecha) >= ?";
            $params[] = $inicio;
            $types .= "s";
        }
        if (!empty($fin)) {
            $sql .= " AND DATE(r.fecha) <= ?";
            $params[] = $fin;
            $types .= "s";
        }
        if (!empty($numero)) {
            $sql .= " AND r.numero LIKE ?";
            $params[] = "%$numero%";
            $types .= "s";
        }
        if (!empty($tipo)) {
            $sql .= " AND r.tipo_remito_id = ?";
            $params[] = $tipo;
            $types .= "i";
        }
        if (!empty($usuario)) {
            $sql .= " AND r.usuario_id = ?";
            $params[] = $usuario;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 📌 Obtener un remito con sus detalles (ya con nombres)
    public function obtener($id) {
        $sql = "SELECT r.*, tr.nombre as tipo_nombre, u.usuario as usuario_nombre
                FROM remito r
                INNER JOIN tipo_remito tr ON r.tipo_remito_id = tr.id
                INNER JOIN usuario u ON r.usuario_id = u.id
                WHERE r.id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $remito = $stmt->get_result()->fetch_assoc();

        $sqlDet = "SELECT dr.id, dr.producto_id, p.nombre,
                          GROUP_CONCAT(DISTINCT CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos,
                          dr.cantidad
                   FROM detalle_remito dr
                   INNER JOIN producto p ON dr.producto_id = p.id
                   LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
                   LEFT JOIN atributo a ON a.id = ap.atributo_id
                   WHERE dr.remito_id=?
                   GROUP BY dr.id, dr.producto_id, p.nombre, dr.cantidad";
        $stmtD = $this->conn->prepare($sqlDet);
        $stmtD->bind_param("i", $id);
        $stmtD->execute();
        $remito['detalles'] = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);

        return $remito;
    }

    // 📌 Crear remito
    public function crear($data, $detalles) {
        $sql = "INSERT INTO remito (tipo_remito_id, usuario_id, numero, señores, atencion, contrato, campo, orden, observaciones, despachado, transportado, placa, recibido, fecha)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisssssssssss",
            $data['tipo_remito_id'], $data['usuario_id'], $data['numero'], $data['señores'],
            $data['atencion'], $data['contrato'], $data['campo'], $data['orden'], $data['observaciones'],
            $data['despachado'], $data['transportado'], $data['placa'], $data['recibido']
        );
        $stmt->execute();
        $remitoId = $this->conn->insert_id;

        foreach ($detalles as $d) {
            // Validación para egreso (no dejar stock negativo)
            if ($data['tipo_remito_id'] == 2) {
                $check = $this->conn->query("SELECT stock FROM producto WHERE id={$d['producto_id']}")->fetch_assoc();
                if ($check['stock'] < $d['cantidad']) {
                    throw new Exception("No hay suficiente stock para el producto ID {$d['producto_id']}");
                }
            }

            $stmtD = $this->conn->prepare("INSERT INTO detalle_remito (remito_id, producto_id, cantidad) VALUES (?, ?, ?)");
            $stmtD->bind_param("iii", $remitoId, $d['producto_id'], $d['cantidad']);
            $stmtD->execute();

            // actualizar stock
            if ($data['tipo_remito_id'] == 1) {
                $this->conn->query("UPDATE producto SET stock = stock + {$d['cantidad']} WHERE id={$d['producto_id']}");
            } else {
                $this->conn->query("UPDATE producto SET stock = stock - {$d['cantidad']} WHERE id={$d['producto_id']}");
            }
        }

        return true;
    }

    // 📌 Editar remito
    public function editar($id, $data, $detalles) {
        // 1. Obtener datos del remito actual
        $sql = "SELECT tipo_remito_id FROM remito WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $remitoActual = $stmt->get_result()->fetch_assoc();

        // 2. Revertir stock de detalles anteriores
        $sqlOld = "SELECT producto_id, cantidad FROM detalle_remito WHERE remito_id=?";
        $stmtOld = $this->conn->prepare($sqlOld);
        $stmtOld->bind_param("i", $id);
        $stmtOld->execute();
        $oldDetalles = $stmtOld->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($oldDetalles as $od) {
            if ($remitoActual['tipo_remito_id'] == 1) { 
                $this->conn->query("UPDATE producto SET stock = stock - {$od['cantidad']} WHERE id={$od['producto_id']}");
            } else {
                $this->conn->query("UPDATE producto SET stock = stock + {$od['cantidad']} WHERE id={$od['producto_id']}");
            }
        }

        // 3. Eliminar detalles previos
        $this->conn->query("DELETE FROM detalle_remito WHERE remito_id=$id");

        // 4. Actualizar datos del remito
        $sql = "UPDATE remito SET tipo_remito_id=?, numero=?, señores=?, atencion=?, contrato=?, campo=?, orden=?, observaciones=?, despachado=?, transportado=?, placa=?, recibido=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssssssssssi",
            $data['tipo_remito_id'], $data['numero'], $data['señores'],
            $data['atencion'], $data['contrato'], $data['campo'], $data['orden'], $data['observaciones'],
            $data['despachado'], $data['transportado'], $data['placa'], $data['recibido'], $id
        );
        $stmt->execute();

        // 5. Insertar nuevos detalles y actualizar stock
        foreach ($detalles as $d) {
            if ($data['tipo_remito_id'] == 2) {
                $check = $this->conn->query("SELECT stock FROM producto WHERE id={$d['producto_id']}")->fetch_assoc();
                if ($check['stock'] < $d['cantidad']) {
                    throw new Exception("No hay suficiente stock para el producto ID {$d['producto_id']}");
                }
            }

            $stmtD = $this->conn->prepare("INSERT INTO detalle_remito (remito_id, producto_id, cantidad) VALUES (?, ?, ?)");
            $stmtD->bind_param("iii", $id, $d['producto_id'], $d['cantidad']);
            $stmtD->execute();

            if ($data['tipo_remito_id'] == 1) {
                $this->conn->query("UPDATE producto SET stock = stock + {$d['cantidad']} WHERE id={$d['producto_id']}");
            } else {
                $this->conn->query("UPDATE producto SET stock = stock - {$d['cantidad']} WHERE id={$d['producto_id']}");
            }
        }

        return true;
    }
        
    // 📌 Eliminar remito con reversión de stock
    public function eliminar($id) {
        $sql = "SELECT tipo_remito_id FROM remito WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $remito = $stmt->get_result()->fetch_assoc();

        $sqlDet = "SELECT producto_id, cantidad FROM detalle_remito WHERE remito_id=?";
        $stmtD = $this->conn->prepare($sqlDet);
        $stmtD->bind_param("i", $id);
        $stmtD->execute();
        $detalles = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($detalles as $d) {
            if ($remito['tipo_remito_id'] == 1) {
                $this->conn->query("UPDATE producto SET stock = stock - {$d['cantidad']} WHERE id={$d['producto_id']}");
            } else {
                $this->conn->query("UPDATE producto SET stock = stock + {$d['cantidad']} WHERE id={$d['producto_id']}");
            }
        }

        $this->conn->query("DELETE FROM detalle_remito WHERE remito_id=$id");
        $stmt = $this->conn->prepare("DELETE FROM remito WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // 📌 Listar tipos de remito
    public function listarTipos() {
        return $this->conn->query("SELECT * FROM tipo_remito")->fetch_all(MYSQLI_ASSOC);
    }

    // 📌 Listar productos con atributos (sin duplicados)
    public function listarProductos() {
        $sql = "SELECT p.id, p.nombre,
                       GROUP_CONCAT(DISTINCT CONCAT(a.nombre, ': ', ap.valor) SEPARATOR ', ') AS atributos
                FROM producto p
                LEFT JOIN atributo_producto ap ON ap.producto_id = p.id
                LEFT JOIN atributo a ON a.id = ap.atributo_id
                GROUP BY p.id";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    // 📌 Listar usuarios
    public function listarUsuarios() {
        return $this->conn->query("SELECT id, usuario FROM usuario ORDER BY usuario ASC")->fetch_all(MYSQLI_ASSOC);
    }
}
