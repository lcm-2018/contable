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
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ($oper == "add") {
        $fecha = $_POST['txt_fecha'];
        $id_manu = $_POST['txt_id_manu'];
        $id_tercero_api = $_POST['hd_id_tercero_api'];
        $objeto = $_POST['txt_objeto'];
        $num_factura = 0;
        $estado = 2;
        $tipo_movimiento = 1;
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
    }

    if ($oper == "del") {
        /*$id = $_POST['id'];
        $sql = "DELETE FROM pto_cdp_detalle WHERE id_pto_cdp_det=" . $id;
        $rs = $cmd->query($sql);
        if ($rs) {
            $res['mensaje'] = 'ok';
        } else {
            $res['mensaje'] = $cmd->errorInfo()[2];
        }
        echo json_encode($res);*/
    }

    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);


// asi realiza el profe add, edit, y del
/*<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = date('Y-m-d H:i:s');
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5002, 2) && $oper == 'add' && $_POST['id_articulo'] == -1) ||
        (PermisosUsuario($permisos, 5002, 3) && $oper == 'add' && $_POST['id_articulo'] != -1) ||
        (PermisosUsuario($permisos, 5002, 4) && $oper == 'del') || $id_rol == 1) {

        if ($oper == 'add') {
            $id = $_POST['id_articulo'];
            $cod_art = $_POST['txt_cod_art'];
            $nom_art = $_POST['txt_nom_art'];
            $id_subgrp = $_POST['sl_subgrp_art'] ? $_POST['sl_subgrp_art'] : 0;
            $top_min = $_POST['txt_topmin_art'];
            $top_max = $_POST['txt_topmax_art'];
            $id_unimed = $_POST['id_txt_unimed_art'] ? $_POST['id_txt_unimed_art'] : 0;
            $es_clinic = $_POST['rdo_escli_art'];
            $id_medins = $_POST['sl_medins_art'] ? $_POST['sl_medins_art'] : 'NULL';
            $estado = $_POST['sl_estado'];

            if ($id == -1) {
                $sql = "INSERT INTO far_medicamentos(cod_medicamento,nom_medicamento,id_subgrupo,top_min,top_max,
                            id_unidadmedida_2,id_unidadmedida,id_formafarmaceutica,id_atc,es_clinico,id_tip_medicamento,estado,id_usr_crea) 
                        VALUES('$cod_art','$nom_art',$id_subgrp,$top_min,$top_max,$id_unimed,0,0,0,$es_clinic,$id_medins,$estado,$id_usr_crea)";
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
            } else {
                $sql = "UPDATE far_medicamentos SET cod_medicamento='$cod_art',nom_medicamento='$nom_art',
                            id_subgrupo=$id_subgrp,top_min=$top_min,top_max=$top_max,id_unidadmedida_2=$id_unimed,
                            es_clinico=$es_clinic,id_tip_medicamento=$id_medins,estado=$estado
                        WHERE id_med=" . $id;
                $rs = $cmd->query($sql);

                if ($rs) {
                    $res['mensaje'] = 'ok';
                    $res['id'] = $id;
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            }

        }

        if ($oper == 'del') {
            $id = $_POST['id'];
            $sql = "DELETE FROM far_medicamentos WHERE id_med=" . $id;
            $rs = $cmd->query($sql);
            if ($rs) {
                $res['mensaje'] = 'ok';
            } else {
                $res['mensaje'] = $cmd->errorInfo()[2];
            }
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
*/