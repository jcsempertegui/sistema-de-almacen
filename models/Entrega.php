<?php
class Entrega {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 📌 Listar entregas con trabajador y usuario
    public function listar($filtroFecha = '', $filtroTrabajador = '') {
        $sql = "SELECT e.id, e.fecha, 
                       CONCAT(t.nombre, ' ', t.apellido_paterno, ' ', t.apellido_materno) as trabajador, 
                       u.usuario as registrado_por, e.campo, e.inspector
                FROM entrega e
                INNER JOIN trabajador t ON e.trabajador_id = t.id
                INNER JOIN usuario u ON e.usuario_id = u.id
                WHERE 1=1";

        if (!empty($filtroFecha)) {
            $sql .= " AND DATE(e.fecha) = ?";
        }
        if (!empty($filtroTrabajador)) {
            $sql .= " AND e.trabajador_id = ?";
        }

        $stmt = $this->conn->prepare($sql);

        if (!empty($filtroFecha) && !empty($filtroTrabajador)) {
            $stmt->bind_param("si", $filtroFecha, $filtroTrabajador);
        } elseif (!empty($filtroFecha)) {
            $stmt->bind_param("s", $filtroFecha);
        } elseif (!empty($filtroTrabajador)) {
            $stmt->bind_param("i", $filtroTrabajador);
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

        $sqlDet = "SELECT de.id, p.nombre,
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

    // 📌 Crear entrega
    public function crear($data, $detalles) {
        $sql = "INSERT INTO entrega (trabajador_id, usuario_id, fecha, campo, inspector)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisss", $data['trabajador_id'], $data['usuario_id'], $data['fecha'], $data['campo'], $data['inspector']);
        $stmt->execute();
        $entregaId = $this->conn->insert_id;

        foreach ($detalles as $d) {
            $stmtD = $this->conn->prepare("INSERT INTO detalle_entrega (entrega_id, producto_id, cantidad, motivo) VALUES (?, ?, ?, ?)");
            $stmtD->bind_param("iiis", $entregaId, $d['producto_id'], $d['cantidad'], $d['motivo']);
            $stmtD->execute();

            // Reducir stock
            $this->conn->query("UPDATE producto SET stock = stock - {$d['cantidad']} WHERE id={$d['producto_id']}");
        }

        return true;
    }

    // 📌 Editar entrega
    public function editar($id, $data, $detalles) {
        // Revertir stock previo
        $sqlOld = "SELECT producto_id, cantidad FROM detalle_entrega WHERE entrega_id=?";
        $stmtOld = $this->conn->prepare($sqlOld);
        $stmtOld->bind_param("i", $id);
        $stmtOld->execute();
        $oldDetalles = $stmtOld->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($oldDetalles as $od) {
            $this->conn->query("UPDATE producto SET stock = stock + {$od['cantidad']} WHERE id={$od['producto_id']}");
        }

        // Actualizar datos
        $sql = "UPDATE entrega SET trabajador_id=?, fecha=?, campo=?, inspector=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isssi", $data['trabajador_id'], $data['fecha'], $data['campo'], $data['inspector'], $id);
        $stmt->execute();

        // Eliminar detalles previos
        $this->conn->query("DELETE FROM detalle_entrega WHERE entrega_id=$id");

        // Insertar nuevos detalles
        foreach ($detalles as $d) {
            $stmtD = $this->conn->prepare("INSERT INTO detalle_entrega (entrega_id, producto_id, cantidad, motivo) VALUES (?, ?, ?, ?)");
            $stmtD->bind_param("iiis", $id, $d['producto_id'], $d['cantidad'], $d['motivo']);
            $stmtD->execute();

            $this->conn->query("UPDATE producto SET stock = stock - {$d['cantidad']} WHERE id={$d['producto_id']}");
        }

        return true;
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
        $sql = "SELECT p.id, p.nombre,
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
}