<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_tercero_api = isset($_POST['txtIdTercero']) ? $_POST['txtIdTercero'] : exit('Acción no permitida');
$tipotercero = $_POST['slcTipoTerce'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_relacion` FROM `tb_rel_tercero` WHERE `id_tercero_api` = '$id_tercero_api' AND `id_tipo_tercero` = '$tipotercero'";
    $rs = $cmd->query($sql);
    $registrado = $rs->fetch();
    if (!empty($registrado)) {
        echo 'Tipo de tercero ya registrado';
        exit();
    } else {
        $sql = "INSERT INTO `tb_rel_tercero` (`id_tercero_api`, `id_tipo_tercero`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_tercero_api, PDO::PARAM_INT);
        $sql->bindParam(2, $tipotercero, PDO::PARAM_INT);
        $sql->bindParam(3, $iduser, PDO::PARAM_INT);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo '1';
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
