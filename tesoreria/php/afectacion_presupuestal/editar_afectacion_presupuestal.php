<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = date('Y-m-d H:i:s');
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ($oper == "add") {
        $fecha = $_POST['txt_fecha'];
        $id_manu = $_POST['txt_id_manu'];
        $id_tercero_api = $_POST['hd_id_tercero_api'];
        $objeto = $_POST['txt_objeto'];
        $num_factura = 0;
        $estado = 2;
        $tipo_movimiento = 4;
        $id_ctb_doc = $_POST['hd_id_ctb_doc'];

        $sql = "INSERT INTO pto_rad (fecha, id_manu, id_tercero_api, objeto, num_factura, estado, id_user_reg, fecha_reg, tipo_movimiento, id_ctb_doc) 
                  VALUES ('$fecha', $id_manu, $id_tercero_api, '$objeto', '$num_factura', $estado, $id_usr_crea, '$fecha_crea', $tipo_movimiento, $id_ctb_doc)";

        $rs = $cmd->query($sql);

        if ($rs) {
            $res['mensaje'] = 'ok';
            $sql_i = 'SELECT LAST_INSERT_ID() AS id';
            $rs = $cmd->query($sql_i);
            $obj = $rs->fetch();
            $res['id'] = $obj['id'];
        } else {
            $res['mensaje'] = $cmd->errorInfo()[2];
        }

        $sql = "INSERT INTO pto_rec (fecha, id_manu, id_tercero_api, objeto, num_factura, estado, id_user_reg, fecha_reg, tipo_movimiento, id_ctb_doc) 
                  VALUES ('$fecha', $id_manu, $id_tercero_api, '$objeto', '$num_factura', $estado, $id_usr_crea, '$fecha_crea', $tipo_movimiento, $id_ctb_doc)";

        $rs = $cmd->query($sql);

        if ($rs) {
            $res['mensaje2'] = 'ok';
            $sql_i = 'SELECT LAST_INSERT_ID() AS id';
            $rs = $cmd->query($sql_i);
            $obj2 = $rs->fetch();
            $res['id2'] = $obj2['id'];
        } else {
            $res['mensaje2'] = $cmd->errorInfo()[2];
        }
    }

    if ($oper == "edit") {
        $id_pto_rad = $_POST['hd_id_pto_rad'];
        $id_pto_rec = $_POST['hd_id_pto_rec'];
        $fecha = $_POST['txt_fecha'];
        $id_manu = $_POST['txt_id_manu'];
        $id_tercero_api = $_POST['hd_id_tercero_api'];
        $objeto = $_POST['txt_objeto'];
        $num_factura = 0;
        $estado = 2;
        $tipo_movimiento = 4;
        $id_ctb_doc = $_POST['hd_id_ctb_doc'];

        $sql = "UPDATE pto_rad set fecha='$fecha', id_manu=$id_manu, id_tercero_api=$id_tercero_api, objeto='$objeto', num_factura='$num_factura', estado=$estado, 
                                   id_user_act=$id_usr_crea, fecha_act='$fecha_crea', tipo_movimiento=$tipo_movimiento, id_ctb_doc=$id_ctb_doc 
                WHERE id_pto_rad = $id_pto_rad";

        $rs = $cmd->query($sql);

        if ($rs) {
            $res['mensaje'] = 'ok';
        } else {
            $res['mensaje'] = $cmd->errorInfo()[2];
        }

        $sql = "UPDATE pto_rec set fecha='$fecha', id_manu=$id_manu, id_tercero_api=$id_tercero_api, objeto='$objeto', num_factura='$num_factura', estado=$estado, 
                                   id_user_act=$id_usr_crea, fecha_act='$fecha_crea', tipo_movimiento=$tipo_movimiento, id_ctb_doc=$id_ctb_doc 
                WHERE id_pto_rec = $id_pto_rec";

        $rs = $cmd->query($sql);
    }

    if ($oper == "del") {
        /*$id = $_POST['id'];
        $sql = "DELETE FROM pto_cdp_detalle WHERE id_pto_cdp_det=" . $id;
        $rs = $cmd->query($sql);
        if ($rs) {
            $res['mensaje'] = 'ok';
        } else {
            $res['mensaje'] = $cmd->errorInfo()[2];
        }*/
    }

    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);