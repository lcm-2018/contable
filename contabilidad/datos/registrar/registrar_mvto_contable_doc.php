<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$fecha = $_POST['fecha'];
$id_tipo_doc = $_POST['id_ctb_doc'];
$id_tercero = $_POST['id_tercero'];
$detalle = $_POST['objeto'];
$opcion = $_POST['opcion'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$id_vigencia = $_SESSION['id_vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu` 
            FROM
                `ctb_doc`
            WHERE (`id_vigencia` = $id_vigencia AND `id_tipo_doc` = $id_tipo_doc)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($opcion == 1) {
        $estado = 1;
        $query = "INSERT INTO `ctb_doc`
                    (`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_tercero`,`fecha`,`detalle`,`estado`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $query->bindParam(2, $id_tipo_doc, PDO::PARAM_INT);
        $query->bindParam(3, $id_manu, PDO::PARAM_INT);
        $query->bindParam(4, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(5, $fecha, PDO::PARAM_STR);
        $query->bindParam(6, $detalle, PDO::PARAM_STR);
        $query->bindParam(7, $estado, PDO::PARAM_INT);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $query->errorInfo()[2] . $query->queryString . 'id_tipo_doc: ' . $id_tipo_doc;
        }
    } else {
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getMessage();
}
