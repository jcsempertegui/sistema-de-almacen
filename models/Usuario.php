<?php
class Usuario {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function listar() {
        $res = $this->conn->query("SELECT * FROM usuario");
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function obtener($id) {
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crear($data) {
        $sql = "INSERT INTO usuario (nombre,apellido,usuario,contraseña,rol) VALUES (?,?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        $hash = password_hash($data['contraseña'], PASSWORD_BCRYPT);
        $stmt->bind_param("sssss",$data['nombre'],$data['apellido'],$data['usuario'],$hash,$data['rol']);
        return $stmt->execute();
    }

    public function editar($id,$data) {
        $sql = "UPDATE usuario SET nombre=?,apellido=?,usuario=?,rol=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi",$data['nombre'],$data['apellido'],$data['usuario'],$data['rol'],$id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM usuario WHERE id=?");
        $stmt->bind_param("i",$id);
        return $stmt->execute();
    }

    public function login($usuario,$clave) {
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE usuario=? LIMIT 1");
        $stmt->bind_param("s",$usuario);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if($res && password_verify($clave,$res['contraseña'])) { return $res; }
        return false;
    }
}
