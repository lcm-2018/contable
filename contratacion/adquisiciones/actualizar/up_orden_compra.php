<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_orden = isset($_POST['id_orden']) ? $_POST['id_orden'] : exit('Accion no permitida');
$aprobados = $_POST['aprobado'];
$cantidades = $_POST['cantidad'];
$val_unitarios = $_POST['val_unid'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$c = 0;
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "UPDATE `far_alm_pedido_detalle`
                SET `aprobado` = ?,`valor` = ?
            WHERE `id_ped_detalle` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $cnt, PDO::PARAM_INT);
    $sql->bindParam(2, $val_un, PDO::PARAM_STR);
    $sql->bindParam(3, $id_detalle, PDO::PARAM_INT);
    foreach ($aprobados as $key => $value) {
        $id_detalle = $key;
        $cnt = $cantidades[$key];
        $val_un = $val_unitarios[$key];
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $c++;
            }
        }
    }
    if ($c > 0) {
        echo 'ok';
    } else {
        echo 'No se ha actualizado ningún registro';
    }
    $cmd = NULL;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
