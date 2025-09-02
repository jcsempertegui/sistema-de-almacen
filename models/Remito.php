<?php
class Remito {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Listar remitos con filtros (fecha desde/hasta, usuario, tipo, numero_remito, campo)
    public function listar($filtros = []) {
        $where = [];
        $params = [];
        $types  = '';

        if (!empty($filtros['desde'])) { $where[] = "r.fecha >= ?"; $types .= 's'; $params[] = $filtros['desde']; }
        if (!empty($filtros['hasta'])) { $where[] = "r.fecha <= ?"; $types .= 's'; $params[] = $filtros['hasta']; }
        if (!empty($filtros['usuario_id'])) { $where[] = "r.usuario_id = ?"; $types .= 'i'; $params[] = (int)$filtros['usuario_id']; }
        if (!empty($filtros['tipo_remito_id'])) { $where[] = "r.tipo_remito_id = ?"; $types .= 'i'; $params[] = (int)$filtros['tipo_remito_id']; }
        if (!empty($filtros['numero_remito'])) { $where[] = "r.numero_remito LIKE ?"; $types .= 's'; $params[] = "%{$filtros['numero_remito']}%"; }
        if (!empty($filtros['campo'])) { $where[] = "r.campo LIKE ?"; $types .= 's'; $params[] = "%{$filtros['campo']}%"; }

        $sql = "
            SELECT 
                r.id, r.fecha, r.numero_remito, r.campo, r.orden, r.observaciones,
                r.señores, r.atencion, r.contrato, r.despachado, r.transportado, r.placa, r.recibido,
                r.usuario_id, u.usuario AS usuario, r.tipo_remito_id, tr.nombre AS tipo
            FROM remito r
            INNER JOIN usuario u ON u.id = r.usuario_id
            INNER JOIN tipo_remito tr ON tr.id = r.tipo_remito_id
        ";
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY r.fecha DESC, r.id DESC";

        $stmt = $this->conn->prepare($sql);
        if ($where) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $remitos = $res->fetch_all(MYSQLI_ASSOC);

        // Resumen rápido de cantidad de ítems por remito
        foreach ($remitos as &$r) {
            $r['items'] = $this->contarItems($r['id']);
        }
        return $remitos;
    }

    private function contarItems($remito_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS c FROM detalle_remito WHERE remito_id = ?");
        $stmt->bind_param("i", $remito_id);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['c'];
    }

    public function obtener($id) {
        $sql = "
            SELECT 
                r.*, u.usuario AS usuario, tr.nombre AS tipo
            FROM remito r
            INNER JOIN usuario u ON u.id = r.usuario_id
            INNER JOIN tipo_remito tr ON tr.id = r.tipo_remito_id
            WHERE r.id = ?
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $remito = $stmt->get_result()->fetch_assoc();

        $sqlD = "
            SELECT dr.id, dr.cantidad, p.nombre AS producto, p.unidad, p.id AS producto_id
            FROM detalle_remito dr
            INNER JOIN producto p ON p.id = dr.producto_id
            WHERE dr.remito_id = ?
        ";
        $stmtD = $this->conn->prepare($sqlD);
        $stmtD->bind_param("i", $id);
        $stmtD->execute();
        $remito['detalles'] = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);

        return $remito;
    }

    // Crear remito con detalles, ajustando stock
    public function crear($data, $detalles) {
        $this->conn->begin_transaction();
        try {
            $sql = "
                INSERT INTO remito
                (usuario_id, tipo_remito_id, fecha, señores, atencion, contrato, numero_remito,
                 campo, orden, observaciones, despachado, transportado, placa, recibido)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "iissssssssssss",
                $data['usuario_id'], $data['tipo_remito_id'], $data['fecha'],
                $data['señores'], $data['atencion'], $data['contrato'], $data['numero_remito'],
                $data['campo'], $data['orden'], $data['observaciones'], $data['despachado'],
                $data['transportado'], $data['placa'], $data['recibido']
            );
            $stmt->execute();
            $remito_id = $this->conn->insert_id;

            foreach ($detalles as $d) {
                if (empty($d['producto_id']) || empty($d['cantidad'])) continue;

                $stmtD = $this->conn->prepare("
                    INSERT INTO detalle_remito (producto_id, remito_id, cantidad) VALUES (?,?,?)
                ");
                $stmtD->bind_param("iii", $d['producto_id'], $remito_id, $d['cantidad']);
                $stmtD->execute();

                // Ajuste de stock
                $this->ajustarStock($d['producto_id'], $d['cantidad'], (int)$data['tipo_remito_id']);
            }

            $this->conn->commit();
            return $remito_id;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // Editar remito (recalcula stock: revierte detalles anteriores y aplica nuevos)
    public function editar($id, $data, $detalles) {
        $this->conn->begin_transaction();
        try {
            // Revertir stock por los detalles existentes
            $exist = $this->obtener($id);
            foreach ($exist['detalles'] as $d) {
                // Revertimos el ajuste original
                $this->ajustarStock($d['producto_id'], $d['cantidad'], $exist['tipo_remito_id'] == 1 ? 2 : 1);
            }

            // Actualizar cabecera
            $sql = "
                UPDATE remito
                SET tipo_remito_id=?, fecha=?, señores=?, atencion=?, contrato=?, numero_remito=?,
                    campo=?, orden=?, observaciones=?, despachado=?, transportado=?, placa=?, recibido=?
                WHERE id=?
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "issssssssssssi",
                $data['tipo_remito_id'], $data['fecha'], $data['señores'], $data['atencion'],
                $data['contrato'], $data['numero_remito'], $data['campo'], $data['orden'],
                $data['observaciones'], $data['despachado'], $data['transportado'], $data['placa'],
                $data['recibido'], $id
            );
            $stmt->execute();

            // Eliminar y reinsertar detalles
            $stmtDel = $this->conn->prepare("DELETE FROM detalle_remito WHERE remito_id=?");
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();

            foreach ($detalles as $d) {
                if (empty($d['producto_id']) || empty($d['cantidad'])) continue;

                $stmtD = $this->conn->prepare("
                    INSERT INTO detalle_remito (producto_id, remito_id, cantidad) VALUES (?,?,?)
                ");
                $stmtD->bind_param("iii", $d['producto_id'], $id, $d['cantidad']);
                $stmtD->execute();

                // Nuevo ajuste de stock con el tipo actual
                $this->ajustarStock($d['producto_id'], $d['cantidad'], (int)$data['tipo_remito_id']);
            }

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    // Eliminar remito (revirtiendo stock)
    public function eliminar($id) {
        $this->conn->begin_transaction();
        try {
            $exist = $this->obtener($id);
            foreach ($exist['detalles'] as $d) {
                // Revertimos el ajuste original
                $this->ajustarStock($d['producto_id'], $d['cantidad'], $exist['tipo_remito_id'] == 1 ? 2 : 1);
            }
            $stmtDel = $this->conn->prepare("DELETE FROM detalle_remito WHERE remito_id = ?");
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();

            $stmt = $this->conn->prepare("DELETE FROM remito WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    private function ajustarStock($producto_id, $cantidad, $tipo_remito_id) {
        if ($tipo_remito_id == 1) { // Ingreso
            $sql = "UPDATE producto SET stock = stock + ? WHERE id = ?";
        } else { // Egreso
            $sql = "UPDATE producto SET stock = stock - ? WHERE id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $cantidad, $producto_id);
        $stmt->execute();
    }

    // Auxiliares para selects
    public function listarUsuarios() {
        $res = $this->conn->query("SELECT id, usuario FROM usuario ORDER BY usuario");
        return $res->fetch_all(MYSQLI_ASSOC);
    }
    public function listarTipos() {
        $res = $this->conn->query("SELECT id, nombre FROM tipo_remito ORDER BY id");
        return $res->fetch_all(MYSQLI_ASSOC);
    }
    public function listarProductos() {
        $res = $this->conn->query("SELECT id, nombre, unidad FROM producto ORDER BY nombre");
        return $res->fetch_all(MYSQLI_ASSOC);
    }
}
