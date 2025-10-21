<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = date('Y-m-d H:i:s');
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5511, 2) && $oper == 'add' && $_POST['id_tipo_egreso_cta'] == -1) ||
        (PermisosUsuario($permisos, 5511, 3) && $oper == 'add' && $_POST['id_tipo_egreso_cta'] != -1) ||
        (PermisosUsuario($permisos, 5511, 4) && $oper == 'del') || $id_rol == 1
    ) {

        $id_tipo_egreso = $_POST['id_tipo_egreso'];

        if ($id_tipo_egreso > 0) {
            if ($oper == 'add') {
                $sql = "SELECT consumo FROM far_orden_egreso_tipo WHERE id_tipo_egreso=" . $id_tipo_egreso;
                $rs = $cmd->query($sql);
                $obj_tegr = $rs->fetch();

                if ($obj_tegr['consumo'] == 1) {
                    $id = $_POST['id_tipo_egreso_cta'];
                    $id_cta = $_POST['id_txt_cta_con'] ? $_POST['id_txt_cta_con'] : 'NULL';
                    $fec_vig = $_POST['txt_fec_vig'] ? "'" . $_POST['txt_fec_vig'] . "'" : 'NULL';
                    $estado = $_POST['sl_estado_cta'];

                    if ($id == -1) {
                        $sql = "INSERT INTO far_orden_egreso_tipo_cta(id_tipo_egreso,id_cuenta,fecha_vigencia,estado,id_usr_crea,fec_creacion)  
                                VALUES($id_tipo_egreso,$id_cta,$fec_vig,$estado,$id_usr_crea,'$fecha_crea')";
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
                        $sql = "UPDATE far_orden_egreso_tipo_cta 
                                SET id_cuenta=$id_cta,fecha_vigencia=$fec_vig,estado=$estado
                                WHERE id_tipo_egreso_cta=" . $id;
                        $rs = $cmd->query($sql);

                        if ($rs) {
                            $res['mensaje'] = 'ok';
                            $res['id'] = $id;
                        } else {
                            $res['mensaje'] = $cmd->errorInfo()[2];
                        }
                    }
                } else {
                    $res['mensaje'] = 'La cuenta se asigna a Tipos de Orden de Egreso de Consumo';
                }
            }

            if ($oper == 'del') {
                $id = $_POST['id'];
                $sql = "DELETE FROM far_orden_egreso_tipo_cta WHERE id_tipo_egreso_cta=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    include '../../../financiero/reg_logs.php';
                    $ruta = '../../../log';
                    $consulta = "DELETE FROM far_orden_egreso_tipo_cta WHERE id_tipo_egreso_cta = $id";
                    RegistraLogs($ruta, $consulta);
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar el Tipo de Orden de Egreso';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
