<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
$key = array_search('59', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$nombre = $_POST['txtNombreConsulta'];
$parametros  = $_POST['jsonParam'];
$consulta = $_POST['txtConsultaSQL'];
$descripcion = $_POST['txtDescripcionSQL'];
$id_opcion = $_POST['id_consulta'];
$id_user = $_SESSION['id_user'];
$tipo = 1;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT MAX(`id_consulta`) AS `id` FROM `tb_consultas_sql`";
    $rs = $cmd->query($sql);
    $id = $rs->fetch(PDO::FETCH_ASSOC);
    $id = !empty($id['id']) ? $id['id'] + 1 : 1;
    $query = "INSERT INTO `tb_consultas_sql` 
                (`id_consulta`, `nom_consulta`, `des_consulta`, `consulta`, `parametros`, `tipo`, `id_opcion`) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id, PDO::PARAM_INT);
    $query->bindParam(2, $nombre, PDO::PARAM_STR);
    $query->bindParam(3, $descripcion, PDO::PARAM_STR);
    $query->bindParam(4, $consulta, PDO::PARAM_STR);
    $query->bindParam(5, $parametros, PDO::PARAM_STR);
    $query->bindParam(6, $tipo, PDO::PARAM_INT);
    $query->bindParam(7, $id_opcion, PDO::PARAM_INT);
    $query->execute();
    if ($query->rowCount() > 0) {
        echo 'ok';
    } else {
        echo $query->errorInfo()[2] . ' Error al insertar';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
