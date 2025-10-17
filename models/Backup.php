<?php
class Backup {
    private $conn;
    private $backupDir;
    private $configFile;

    public function __construct($db) {
        $this->conn = $db;
        $this->backupDir = __DIR__ . '/../backups/';
        $this->configFile = __DIR__ . '/../backups/config.json';

        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0777, true);
        }
    }

    // 🔹 Datos de conexión
    private function obtenerDatosConexion() {
        return [
            'host' => 'localhost',
            'user' => 'root',
            'pass' => '123456',
            'db'   => 'almacen2'
        ];
    }

    // 🧩 Generar Backup
    public function generarBackup() {
        $dbParams = $this->obtenerDatosConexion();
        $fileName = sprintf('backup_%s_%s.sql', $dbParams['db'], date('Y-m-d_H-i-s'));
        $filePath = $this->backupDir . $fileName;

        $mysqldump = 'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe';

        if (!file_exists($mysqldump)) {
            throw new Exception("No se encontró mysqldump en: $mysqldump");
        }

        $cmd = sprintf(
            '"%s" -h "%s" -u "%s" --password="%s" "%s" > "%s"',
            $mysqldump,
            $dbParams['host'],
            $dbParams['user'],
            $dbParams['pass'],
            $dbParams['db'],
            $filePath
        );

        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0 || !file_exists($filePath) || filesize($filePath) === 0) {
            throw new Exception("Error al generar backup. Comando: $cmd");
        }

        return $fileName;
    }

    // 🧩 Restaurar Backup
    public function restaurarBackup($archivo) {
        $dbParams = $this->obtenerDatosConexion();
        $filePath = $this->backupDir . basename($archivo);

        if (!file_exists($filePath) || filesize($filePath) === 0) {
            throw new Exception("El archivo de backup está vacío o no existe: $filePath");
        }

        $mysql = 'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysql.exe';

        if (!file_exists($mysql)) {
            throw new Exception("No se encontró mysql.exe en: $mysql");
        }

        $cmd = sprintf(
            '"%s" -h "%s" -u "%s" --password="%s" "%s" < "%s"',
            $mysql,
            $dbParams['host'],
            $dbParams['user'],
            $dbParams['pass'],
            $dbParams['db'],
            $filePath
        );

        exec($cmd, $output, $resultCode);

        if ($resultCode !== 0) {
            throw new Exception("Error al restaurar backup. Comando ejecutado: $cmd");
        }

        return true;
    }

    // 🔹 Listar todos los backups
    public function listarBackups() {
        $archivos = glob($this->backupDir . '*.sql');
        usort($archivos, fn($a, $b) => filemtime($b) - filemtime($a));
        return array_map('basename', $archivos);
    }

    // 🔹 Eliminar backup
    public function eliminarBackup($archivo) {
        $filePath = $this->backupDir . basename($archivo);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // 🔹 Guardar configuración (periodicidad)
    public function guardarConfiguracion($frecuencia) {
        $config = ['frecuencia' => $frecuencia];
        file_put_contents($this->configFile, json_encode($config, JSON_PRETTY_PRINT));
    }

    // 🔹 Obtener configuración
    public function obtenerConfiguracion() {
        if (file_exists($this->configFile)) {
            $data = json_decode(file_get_contents($this->configFile), true);
            return $data['frecuencia'] ?? 'manual';
        }
        return 'manual';
    }

    // 🔹 Verificar backups automáticos (opcional)
    public function verificarBackupAutomatico() {
        $frecuencia = $this->obtenerConfiguracion();
        $archivos = $this->listarBackups();

        if (empty($archivos)) {
            $ultimoBackup = 0;
        } else {
            $ultimoBackup = filemtime($this->backupDir . $archivos[0]);
        }

        $ahora = time();
        $dias = ($ahora - $ultimoBackup) / (60 * 60 * 24);

        switch ($frecuencia) {
            case 'diario':
                if ($dias >= 1) $this->generarBackup();
                break;
            case 'semanal':
                if ($dias >= 7) $this->generarBackup();
                break;
            case 'mensual':
                if ($dias >= 30) $this->generarBackup();
                break;
        }
    }
}
?>
