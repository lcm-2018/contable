<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
if ($id_rol != 1) {
    exit('Usuario no autorizado');
}
$data = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no autorizado');
$data = explode('|', base64_decode($data));
$id_modulo = $data[0];
$mes = $numero_con_ceros = str_pad($data[1], 2, "0", STR_PAD_LEFT);
$vigencia = $_SESSION['vigencia'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
//funcion para obtener el ultimo dia del mes
function ultimoDiaMes($mes, $anio)
{
    return date("d", mktime(0, 0, 0, $mes + 1, 0, $anio));
}
$cierre = $vigencia . '-' . $mes . '-' . ultimoDiaMes($mes, $vigencia);

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO `tb_fin_periodos`
                (`vigencia`,`mes`,`id_modulo`,`fecha_cierre`,`id_user_reg`,`fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $vigencia, PDO::PARAM_INT);
    $sql->bindParam(2, $mes, PDO::PARAM_STR);
    $sql->bindParam(3, $id_modulo, PDO::PARAM_INT);
    $sql->bindParam(4, $cierre, PDO::PARAM_STR);
    $sql->bindParam(5, $id_user, PDO::PARAM_INT);
    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    //verificar si hay un ultimo id insertado
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
