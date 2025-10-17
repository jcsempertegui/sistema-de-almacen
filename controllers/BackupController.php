<?php
require_once __DIR__ . '/../models/Backup.php';

class BackupController {
    public $model;

    public function __construct($db) {
        $this->model = new Backup($db);
    }

    public function index() {
        $backups = $this->model->listarBackups();
        $frecuencia = $this->model->obtenerConfiguracion();
        include __DIR__ . '/../views/backup/index.php';
    }

    public function generar() {
        try {
            $nombre = $this->model->generarBackup();
            header("Location: index.php?mensaje=Backup generado correctamente ($nombre)");
        } catch (Exception $e) {
            header("Location: index.php?error=" . urlencode($e->getMessage()));
        }
        exit;
    }

    public function restaurar($archivo) {
        try {
            $this->model->restaurarBackup($archivo);
            header("Location: index.php?mensaje=Backup restaurado correctamente");
        } catch (Exception $e) {
            header("Location: index.php?error=" . urlencode($e->getMessage()));
        }
        exit;
    }

    public function eliminar($archivo) {
        try {
            $this->model->eliminarBackup($archivo);
            header("Location: index.php?mensaje=Backup eliminado");
        } catch (Exception $e) {
            header("Location: index.php?error=" . urlencode($e->getMessage()));
        }
        exit;
    }

    public function guardarConfiguracion($frecuencia) {
        $this->model->guardarConfiguracion($frecuencia);
        header("Location: index.php?mensaje=Configuración guardada");
        exit;
    }

    // 🔹 Llamado desde header (opcional)
    public function verificarAutomatico() {
        $this->model->verificarBackupAutomatico();
    }
}
?>
