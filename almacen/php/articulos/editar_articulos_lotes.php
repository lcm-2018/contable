<?php
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

    $bodega = bodega_principal($cmd);
    $bodega_pri = $bodega['id_bodega'];

    if ((PermisosUsuario($permisos, 5002, 2) && $oper == 'add' && $_POST['id_lote'] == -1) ||
        (PermisosUsuario($permisos, 5002, 3) && $oper == 'add' && $_POST['id_lote'] != -1) ||
        (PermisosUsuario($permisos, 5002, 4) && $oper == 'del') || $id_rol == 1
    ) {

        $id_articulo = $_POST['id_articulo'];

        if ($id_articulo > 0) {

            if ($oper == 'add') {
                $id = $_POST['id_lote'];
                $num_lot = strip_tags(trim($_POST['txt_num_lot']));
                $fec_ven = $_POST['txt_fec_ven'];
                $id_pres = $_POST['id_txt_pre_lote'] ? $_POST['id_txt_pre_lote'] : 0;
                $id_cum = $_POST['sl_cum_lot'] ? $_POST['sl_cum_lot'] : 0;
                $reg_inv = $_POST['txt_reg_inv'];
                $ser_ref = $_POST['txt_ser_ref'];                
                $id_marca = $_POST['sl_marca_lot'] ? $_POST['sl_marca_lot'] : 0;
                $id_bodega = $_POST['id_txt_nom_bod'];
                $estado = $_POST['sl_estado_lot'];

                if ($id == -1) {

                    $sql = "SELECT COUNT(*) AS count FROM far_medicamento_lote WHERE lote='$num_lot' AND id_med=$id_articulo AND id_bodega=$id_bodega";
                    $rs = $cmd->query($sql);
                    $obj = $rs->fetch();

                    if ($obj['count'] == 0) {
                        $sql = "INSERT INTO far_medicamento_lote(lote,fec_vencimiento,id_presentacion,id_cum,reg_invima,serie,id_marca,id_bodega,estado,id_usr_crea,id_med)  
                                VALUES('$num_lot','$fec_ven',$id_pres,$id_cum,'$reg_inv','$ser_ref',$id_marca,$id_bodega,$estado,$id_usr_crea,$id_articulo)";
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
                        $res['mensaje'] = 'El lote Ingresado ya existe para el Articulo Seleccionado';
                    }    
                } else {
                    if ($id_bodega == $bodega_pri) {
                        $sql = "SELECT COUNT(*) AS count FROM far_medicamento_lote WHERE lote='$num_lot' AND id_med=$id_articulo AND id_bodega=$id_bodega AND id_lote<>$id";
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();

                        if ($obj['count'] == 0) {
                            $arr = array();
                            array_push($arr,$id);
                            do{
                                $id_lote = array_shift($arr);
                                $sql = "UPDATE far_medicamento_lote SET lote='$num_lot',fec_vencimiento='$fec_ven',id_presentacion=$id_pres,id_cum=$id_cum,
                                                reg_invima='$reg_inv',serie='$ser_ref',id_marca=$id_marca,estado=$estado
                                        WHERE id_lote=" . $id_lote;
                                $rs = $cmd->query($sql);

                                $sql = "SELECT GROUP_CONCAT(id_lote) as lotes FROM far_medicamento_lote WHERE id_lote_pri=" . $id_lote;
                                $rs = $cmd->query($sql);
                                $obj_lote = $rs->fetch();
                                if($obj_lote['lotes']){
                                    $lotes = explode(',',$obj_lote['lotes']);
                                    foreach ($lotes as $lote) {
                                        array_push($arr, $lote);
                                    }
                                }    
                            }while($arr);

                            if ($rs) {
                                $res['mensaje'] = 'ok';
                                $res['id'] = $id;
                            } else {
                                $res['mensaje'] = $cmd->errorInfo()[2];
                            }
                        } else {
                            $res['mensaje'] = 'El lote ingresado ya existe para el Articulo Seleccionado';
                        }
                    } else {
                        $res['mensaje'] = 'Solo se puede Modificar un Lote Principal';
                    }        
                }
            }

            if ($oper == 'del') {
                $id = $_POST['id'];
                $sql = "DELETE FROM far_medicamento_lote WHERE id_lote=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar el Articulo';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
