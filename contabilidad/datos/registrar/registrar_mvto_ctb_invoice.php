<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_tipo_doc = isset($_POST['id_doc_fuente']) ? $_POST['id_doc_fuente'] : exit('Acceso no permitido');
$fecha = $_POST['fecha'];
$referencia = $_POST['slcReferencia'];
$id_tercero = $_POST['id_tercero'];
$detalle = $_POST['objeto'];
$id_reg = $_POST['id'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$id_vigencia = $_SESSION['id_vigencia'];
$id_rad = isset($_POST['id_rad']) ? $_POST['id_rad'] : NULL;
$id_manu = isset($_POST['id_manu']) ? $_POST['id_manu'] : $_POST['numDoc'];

$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_manu` 
            FROM
                `ctb_doc`
            WHERE (`id_vigencia` = $id_vigencia AND `id_tipo_doc` = $id_tipo_doc AND `id_manu` = $id_manu AND `id_ctb_doc` <> $id_reg)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    if (!empty($consecutivo)) {
        $response['msg'] = 'El consecutivo para este documento <b>' . $id_manu . '</b> ya se encuentra registrado';
        echo json_encode($response);
        exit();
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_reg == 0) {
        $estado = 1;
        $query = "INSERT INTO `ctb_doc`
                    (`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_tercero`,`fecha`,`detalle`,`estado`,`id_user_reg`,`fecha_reg`,`id_rad`,`id_ref_ctb`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
        $query->bindParam(10, $id_rad, PDO::PARAM_INT);
        $query->bindParam(11, $referencia, PDO::PARAM_INT);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
            $response['id_doc'] = $cmd->lastInsertId();
            $response['t_dato'] = $id_tipo_doc;
        } else {
            $response['msg'] = $query->errorInfo()[2] . $query->queryString . 'id_tipo_doc: ' . $id_tipo_doc;
        }
    } else {
        $query = "UPDATE `ctb_doc`
                    SET `id_tercero` = ?, `fecha` = ?, `detalle` = ?, `id_ref_ctb` = ?, `id_manu` = ?
                WHERE (`id_ctb_doc` = ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(2, $fecha, PDO::PARAM_STR);
        $query->bindParam(3, $detalle, PDO::PARAM_STR);
        $query->bindParam(4, $referencia, PDO::PARAM_INT);
        $query->bindParam(5, $id_manu, PDO::PARAM_INT);
        $query->bindParam(6, $id_reg, PDO::PARAM_INT);
        if (!($query->execute())) {
            $response['msg'] = $query->errorInfo()[2] . $query->queryString;
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `ctb_doc` SET `id_user_act` = ?, `fecha_act` = ? WHERE (`id_ctb_doc` = ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $iduser, PDO::PARAM_INT);
                $query->bindParam(2, $fecha2, PDO::PARAM_STR);
                $query->bindParam(3, $id_reg, PDO::PARAM_INT);
                $query->execute();
                $response['status'] = 'ok';
                $response['id_doc'] = $id_reg;
                $response['t_dato'] = $id_tipo_doc;
            } else {
                $response['msg'] = 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getMessage();
}

echo json_encode($response);
