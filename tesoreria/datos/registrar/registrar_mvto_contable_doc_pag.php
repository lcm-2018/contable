<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$fecha = $_POST['fecha'];
$id_tipo_doc = $_POST['id_ctb_doc'];
$tipodato = isset($_POST['tipodato']) ? $_POST['tipodato'] : $_POST['id_ctb_doc'];
$doc_soporte = isset($_POST['chDocSoporte']) ? 1 : 0;
$id_tercero = $_POST['id_tercero'];
$detalle = $_POST['objeto'];
$referencia = isset($_POST['referencia']) && $_POST['referencia'] > 0 ? $_POST['referencia'] : NULL;
$id_ref_ctb = isset($_POST['ref_mov']) && $_POST['ref_mov'] > 0 ? $_POST['ref_mov'] : NULL;
$id_reg = $_POST['id'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$id_vigencia = $_SESSION['id_vigencia'];
$id_manu = $_POST['numDoc'];
$id_cop = isset($_POST['id_cop_pag']) ? $_POST['id_cop_pag'] : 0;
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_manu` 
            FROM
                `ctb_doc`
            WHERE (`id_vigencia` = $id_vigencia AND `id_tipo_doc` = $tipodato AND `id_manu` = $id_manu AND `id_ctb_doc` <> $id_reg)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    if (!empty($consecutivo)) {
        $response['msg'] = 'El número de documento ya existe';
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
                    (`id_vigencia`,`id_tipo_doc`,`id_manu`,`id_tercero`,`fecha`,`detalle`,`estado`,`id_user_reg`,`fecha_reg`,`id_ref`,`id_ref_ctb`,`doc_soporte`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_vigencia, PDO::PARAM_INT);
        $query->bindParam(2, $tipodato, PDO::PARAM_INT);
        $query->bindParam(3, $id_manu, PDO::PARAM_INT);
        $query->bindParam(4, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(5, $fecha, PDO::PARAM_STR);
        $query->bindParam(6, $detalle, PDO::PARAM_STR);
        $query->bindParam(7, $estado, PDO::PARAM_INT);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2);
        $query->bindParam(10, $referencia, PDO::PARAM_INT);
        $query->bindParam(11, $id_ref_ctb, PDO::PARAM_INT);
        $query->bindParam(12, $doc_soporte, PDO::PARAM_INT);
        $query->execute();
        $id_pag = $cmd->lastInsertId();
        if ($id_pag > 0) {
            $sql = "INSERT INTO `tes_rel_pag_cop`
                        (`id_doc_cop`,`id_doc_pag`)
                    VALUES (?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_cop, PDO::PARAM_INT);
            $sql->bindParam(2, $id_pag, PDO::PARAM_INT);
            $sql->execute();
            if (isset($_POST['id_caja'])) {
                $id = $cmd->lastInsertId();
                $id_caja = $_POST['id_caja'];
                $query = "INSERT INTO `tes_caja_doc` (`id_ctb_doc`, `id_caja`) VALUES (?, ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $id, PDO::PARAM_INT);
                $query->bindParam(2, $id_caja, PDO::PARAM_INT);
                $query->execute();
            }
            $response['status'] = 'ok';
            $response['id'] = $id_pag;
        } else {
            $response['msg'] = $query->errorInfo()[2] . $query->queryString . 'id_tipo_doc: ' . $id_tipo_doc;
        }
    } else {
        $query = "UPDATE `ctb_doc`
                    SET `id_tercero` = ?, `fecha` = ?, `detalle` = ?, `id_ref`= ?,`id_ref_ctb` = ?, `doc_soporte` = ?, `id_manu` = ?
                WHERE (`id_ctb_doc` = ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(2, $fecha, PDO::PARAM_STR);
        $query->bindParam(3, $detalle, PDO::PARAM_STR);
        $query->bindParam(4, $referencia, PDO::PARAM_INT);
        $query->bindParam(5, $id_ref_ctb, PDO::PARAM_INT);
        $query->bindParam(6, $doc_soporte, PDO::PARAM_INT);
        $query->bindParam(7, $id_manu, PDO::PARAM_INT);
        $query->bindParam(8, $id_reg, PDO::PARAM_INT);
        if (!($query->execute())) {
            $response['msg'] = $query->errorInfo()[2] . $query->queryString;
        } else {
            $up = false;
            if (isset($_POST['id_caja'])) {
                $sql = "UPDATE `tes_caja_doc` SET `id_caja` = ? WHERE (`id_ctb_doc` = ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $_POST['id_caja'], PDO::PARAM_INT);
                $sql->bindParam(2, $id_reg, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    $up = true;
                }
            }
            if ($query->rowCount() > 0 || $up) {
                $query = "UPDATE `ctb_doc` SET `id_user_act` = ?, `fecha_act` = ? WHERE (`id_ctb_doc` = ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $iduser, PDO::PARAM_INT);
                $query->bindParam(2, $fecha2, PDO::PARAM_STR);
                $query->bindParam(3, $id_reg, PDO::PARAM_INT);
                $query->execute();
                $response['status'] = 'ok';
                $response['id'] = $id_reg;
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
