<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../../conexion.php';
$id_ctb_doc = isset($_POST['id_pag_doc']) ? $_POST['id_pag_doc'] : exit('Accion no permitida');
$id_manu = $_POST['id_pto_rp'];
$ids_cops = $_POST['detalle'];
$tercero = $_POST['id_tercero'];
$fecha = $_POST['fecha'];
$objeto = $_POST['objeto'];
$factura = $_POST['factura'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');

$liberado = 0;
$id_vigencia = $_SESSION['id_vigencia'];

$response['status'] = 'error';
$response['msg'] = '';
$registros = 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto` FROM `pto_presupuestos` WHERE `id_vigencia` = $id_vigencia AND `id_tipo` = 1";
    $rs = $cmd->query($sql);
    $id_pto = $rs->fetchColumn();
    if (empty($id_pto)) {
        exit('No se encontró el punto de presupuesto para la vigencia actual');
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `pto_rec`
                (`id_pto`,`fecha`,`id_manu`,`id_tercero_api`,`objeto`,`num_factura`,`estado`,`id_user_reg`, `fecha_reg`,`id_ctb_doc`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $fecha, PDO::PARAM_STR);
    $sql->bindParam(3, $id_manu, PDO::PARAM_INT);
    $sql->bindParam(4, $tercero, PDO::PARAM_INT);
    $sql->bindParam(5, $objeto, PDO::PARAM_STR);
    $sql->bindParam(6, $factura, PDO::PARAM_STR);
    $sql->bindValue(7, 1, PDO::PARAM_INT); // Estado 1: Pendiente
    $sql->bindParam(8, $iduser, PDO::PARAM_INT);
    $sql->bindParam(9, $fecha2, PDO::PARAM_STR);
    $sql->bindParam(10, $id_ctb_doc, PDO::PARAM_INT);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $id_rec = $cmd->lastInsertId();
        $query = "INSERT INTO `pto_rec_detalle`
                    (`id_pto_rac`,`id_pto_rad_detalle`,`id_tercero_api`,`valor`,`valor_liberado`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_rec, PDO::PARAM_INT);
        $query->bindParam(2, $id_detalle, PDO::PARAM_INT);
        $query->bindParam(3, $tercero, PDO::PARAM_INT);
        $query->bindParam(4, $val_rec, PDO::PARAM_STR);
        $query->bindParam(5, $liberado, PDO::PARAM_STR);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2, PDO::PARAM_STR);
        foreach ($ids_cops as $key => $value) {
            $id_detalle = $key;
            $val_rec = str_replace(",", "", $value);
            if ($val_rec > 0) {
                if ($query->execute()) {
                    $registros++;
                } else {
                    $response['msg'] .= 'Error al insertar detalle: ' . implode(', ', $cmd->errorInfo()) . '<br>';
                }
            }
        }
    } else {
        $response['msg'] = $sql->errorInfo()[2];
        echo json_encode($response);
        exit();
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($registros > 0) {
    $sql = "SELECT
                SUM(IFNULL(`pto_rec_detalle`.`valor`,0) - IFNULL(`pto_rec_detalle`.`valor_liberado`,0)) AS `valor`
            FROM
                `pto_rec_detalle`
                INNER JOIN `pto_rec` 
                    ON (`pto_rec_detalle`.`id_pto_rac` = `pto_rec`.`id_pto_rec`)
            WHERE (`pto_rec`.`estado` > 0 AND `pto_rec`.`id_ctb_doc` = $id_ctb_doc)";
    $rs = $cmd->query($sql);
    $valor = $rs->fetch();
    $response['status'] = 'ok';
    $response['valor'] = $valor['valor'];
}
echo json_encode($response);
