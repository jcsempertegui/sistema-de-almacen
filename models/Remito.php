<?php
class Remito {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 📌 Listar remitos (con filtros opcionales)
    public function listar($filtroFecha = '', $filtroUsuario = '') {
        $sql = "SELECT r.id, r.fecha, r.numero_remito, r.observaciones,
                       tr.nombre AS tipo, u.nombre AS usuario
                FROM remito r
                INNER JOIN tipo_remito tr ON r.tipo_remito_id = tr.id
                INNER JOIN usuario u ON r.usuario_id = u.id
                WHERE 1=1";

        if (!empty($filtroFecha)) {
            $sql .= " AND r.fecha = ?";
        }
        if (!empty($filtroUsuario)) {
            $sql .= " AND r.usuario_id = ?";
        }

        $stmt = $this->conn->prepare($sql);

        if (!empty($filtroFecha) && !empty($filtroUsuario)) {
            $stmt->bind_param("si", $filtroFecha, $filtroUsuario);
        } elseif (!empty($filtroFecha)) {
            $stmt->bind_param("s", $filtroFecha);
        } elseif (!empty($filtroUsuario)) {
            $stmt->bind_param("i", $filtroUsuario);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 📌 Obtener un remito con sus detalles
    public function obtener($id) {
        $sql = "SELECT r.*, tr.nombre AS tipo, u.nombre AS usuario
                FROM remito r
                INNER JOIN tipo_remito tr ON r.tipo_remito_id = tr.id
                INNER JOIN usuario u ON r.usuario_id = u.id
                WHERE r.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $remito = $stmt->get_result()->fetch_assoc();

        // Obtener productos del remito
        $sqlD = "SELECT dr.producto_id, p.nombre AS producto, dr.cantidad
                 FROM detalle_remito dr
                 INNER JOIN producto p ON dr.producto_id = p.id
                 WHERE dr.remito_id = ?";
        $stmtD = $this->conn->prepare($sqlD);
        $stmtD->bind_param("i", $id);
        $stmtD->execute();
        $remito['detalles'] = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);

        return $remito;
    }

    // 📌 Crear remito con sus productos
    public function crear($data, $detalles) {
        $sql = "INSERT INTO remito (usuario_id, tipo_remito_id, fecha, numero_remito, observaciones) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisss", $data['usuario_id'], $data['tipo_remito_id'], $data['fecha'], $data['numero_remito'], $data['observaciones']);

        if ($stmt->execute()) {
            $remitoId = $this->conn->insert_id;

            foreach ($detalles as $d) {
                $sqlD = "INSERT INTO detalle_remito (remito_id, producto_id, cantidad) VALUES (?, ?, ?)";
                $stmtD = $this->conn->prepare($sqlD);
                $stmtD->bind_param("iii", $remitoId, $d['producto_id'], $d['cantidad']);
                $stmtD->execute();

                // 📦 Actualizar stock
                if ($data['tipo_remito_id'] == 1) { // ingreso
                    $this->conn->query("UPDATE producto SET stock = stock + {$d['cantidad']} WHERE id = {$d['producto_id']}");
                } elseif ($data['tipo_remito_id'] == 2) { // egreso
                    $this->conn->query("UPDATE producto SET stock = stock - {$d['cantidad']} WHERE id = {$d['producto_id']}");
                }
            }
            return true;
        }
        return false;
    }

    // 📌 Editar remito y sus productos
    public function editar($id, $data, $detalles) {
        $sql = "UPDATE remito SET tipo_remito_id=?, fecha=?, numero_remito=?, observaciones=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssi", $data['tipo_remito_id'], $data['fecha'], $data['numero_remito'], $data['observaciones'], $id);
        $stmt->execute();

        // Revertir stock previo
        $sqlPrev = "SELECT producto_id, cantidad FROM detalle_remito WHERE remito_id=?";
        $stmtPrev = $this->conn->prepare($sqlPrev);
        $stmtPrev->bind_param("i", $id);
        $stmtPrev->execute();
        $previos = $stmtPrev->get_result()->fetch_all(MYSQLI_ASSOC);

        $tipoAnterior = $data['tipo_remito_id'];

        foreach ($previos as $p) {
            if ($tipoAnterior == 1) {
                $this->conn->query("UPDATE producto SET stock = stock - {$p['cantidad']} WHERE id = {$p['producto_id']}");
            } elseif ($tipoAnterior == 2) {
                $this->conn->query("UPDATE producto SET stock = stock + {$p['cantidad']} WHERE id = {$p['producto_id']}");
            }
        }

        // Eliminar detalles viejos
        $this->conn->query("DELETE FROM detalle_remito WHERE remito_id = $id");

        // Insertar nuevos detalles
        foreach ($detalles as $d) {
            $sqlD = "INSERT INTO detalle_remito (remito_id, producto_id, cantidad) VALUES (?, ?, ?)";
            $stmtD = $this->conn->prepare($sqlD);
            $stmtD->bind_param("iii", $id, $d['producto_id'], $d['cantidad']);
            $stmtD->execute();

            // Actualizar stock según el nuevo detalle
            if ($data['tipo_remito_id'] == 1) {
                $this->conn->query("UPDATE producto SET stock = stock + {$d['cantidad']} WHERE id = {$d['producto_id']}");
            } elseif ($data['tipo_remito_id'] == 2) {
                $this->conn->query("UPDATE producto SET stock = stock - {$d['cantidad']} WHERE id = {$d['producto_id']}");
            }
        }

        return true;
    }

    // 📌 Eliminar remito y revertir stock
    public function eliminar($id) {
        $sqlR = "SELECT tipo_remito_id FROM remito WHERE id=?";
        $stmtR = $this->conn->prepare($sqlR);
        $stmtR->bind_param("i", $id);
        $stmtR->execute();
        $tipo = $stmtR->get_result()->fetch_assoc()['tipo_remito_id'];

        // Recuperar detalles
        $sqlD = "SELECT producto_id, cantidad FROM detalle_remito WHERE remito_id=?";
        $stmtD = $this->conn->prepare($sqlD);
        $stmtD->bind_param("i", $id);
        $stmtD->execute();
        $detalles = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);

        // Revertir stock
        foreach ($detalles as $d) {
            if ($tipo == 1) {
                $this->conn->query("UPDATE producto SET stock = stock - {$d['cantidad']} WHERE id = {$d['producto_id']}");
            } elseif ($tipo == 2) {
                $this->conn->query("UPDATE producto SET stock = stock + {$d['cantidad']} WHERE id = {$d['producto_id']}");
            }
        }

        // Eliminar detalle + remito
        $this->conn->query("DELETE FROM detalle_remito WHERE remito_id = $id");
        $sql = "DELETE FROM remito WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // 📌 Listar tipos de remito
    public function listarTipos() {
        $res = $this->conn->query("SELECT * FROM tipo_remito ORDER BY nombre ASC");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // 📌 Listar productos
    public function listarProductos() {
        $res = $this->conn->query("SELECT * FROM producto ORDER BY nombre ASC");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    // 📌 Listar usuarios
    public function listarUsuarios() {
        $res = $this->conn->query("SELECT id, nombre FROM usuario ORDER BY nombre ASC");
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
