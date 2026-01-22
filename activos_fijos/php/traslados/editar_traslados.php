<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5708, 2) && $oper == 'add' && $_POST['id_traslado'] == -1) ||
        (PermisosUsuario($permisos, 5708, 3) && $oper == 'add' && $_POST['id_traslado'] != -1) ||
        (PermisosUsuario($permisos, 5708, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5708, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5708, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_traslado'];
            $fec_traslado = $_POST['txt_fec_traslado'];
            $hor_traslado = $_POST['txt_hor_traslado'];            
            $id_usr_origen = $_POST['sl_responsable_origen'];
            $id_area_destino = $_POST['sl_area_destino'];
            $id_usr_destino = $_POST['sl_responsable_destino'];
            $observaciones = $_POST['txt_obs_traslado'];

            //Verifica si los datos estas activos o bloqueados en el formulario
            if (isset($_POST['sl_area_origen'])){
                $id_area_origen = $_POST['sl_area_origen'];
            }else{
                $sql = "SELECT id_area_origen FROM acf_traslado WHERE id_traslado=" . $id;
                $rs = $cmd->query($sql);
                $obj_traslado = $rs->fetch();
                $id_area_origen = $obj_traslado['id_area_origen'];    
            }

            if($id_area_origen != $id_area_destino){
                if ($id == -1) {                
                    $sql = "INSERT INTO acf_traslado(fec_traslado,hor_traslado,observaciones,id_area_origen,id_usr_origen,
                            id_area_destino,id_usr_destino,id_usr_crea,fec_crea,estado) 
                        VALUES('$fec_traslado','$hor_traslado','$observaciones',$id_area_origen,$id_usr_origen,
                            $id_area_destino,$id_usr_destino,$id_usr_ope,'$fecha_ope',1)";
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
                    $sql = "SELECT estado FROM acf_traslado WHERE id_traslado=" . $id;
                    $rs = $cmd->query($sql);
                    $obj_traslado = $rs->fetch();

                    if ($obj_traslado['estado'] == 1) {
                        $sql = "UPDATE acf_traslado SET observaciones='$observaciones',id_area_origen=$id_area_origen,id_usr_origen=$id_usr_origen,id_area_destino=$id_area_destino,id_usr_destino=$id_usr_destino
                                WHERE id_traslado=" . $id;
                        $rs = $cmd->query($sql);

                        if ($rs) {
                            $res['mensaje'] = 'ok';
                            $res['id'] = $id;
                        } else {
                            $res['mensaje'] = $cmd->errorInfo()[2];
                        }
                    } else {
                        $res['mensaje'] = 'Solo puede Modificar traslados en estado Pendiente';
                    }
                }
            } else {
                $res['mensaje'] = 'El Area Origen y el Area Destino deben ser diferentes';    
            }    
        }

        if ($oper == 'del') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM acf_traslado WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_traslado = $rs->fetch();

            if ($obj_traslado['estado'] == 1) {
                $sql = "DELETE FROM acf_traslado WHERE id_traslado=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar traslados en estado Pendiente';
            }
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = 'SELECT acf_traslado.estado,acf_traslado.id_area_destino,far_centrocosto_area.id_sede,acf_traslado.id_usr_destino 
                    FROM acf_traslado 
                    INNER JOIN far_centrocosto_area ON (far_centrocosto_area.id_area=acf_traslado.id_area_destino)
                    WHERE id_traslado=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_traslado = $rs->fetch();
            $estado = isset($obj_traslado['estado']) ? $obj_traslado['estado'] : -1;
            $id_area_destino = isset($obj_traslado['id_area_destino']) ? $obj_traslado['id_area_destino'] : 0;
            $id_sede_destino = isset($obj_traslado['id_sede']) ? $obj_traslado['id_sede'] : 0;
            $id_usr_destino = isset($obj_traslado['id_usr_destino']) ? $obj_traslado['id_usr_destino'] : 0;

            $sql = "SELECT COUNT(*) AS total FROM acf_traslado_detalle WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_traslado = $rs->fetch();
            $num_detalles = $obj_traslado['total'];

            if ($estado == 1 && $num_detalles > 0) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = "UPDATE acf_traslado SET estado=2,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope' WHERE id_traslado=$id";
                $rs1 = $cmd->query($sql);

                $sql = "UPDATE acf_hojavida SET id_area=$id_area_destino,id_sede=$id_sede_destino,id_responsable=$id_usr_destino
                        WHERE id_activo_fijo IN (SELECT id_activo_fijo FROM acf_traslado_detalle WHERE id_traslado=$id)";
                $rs2 = $cmd->query($sql);

                if ($rs1 == false || $rs2 == false || error_get_last()) {
                    $error = 1;
                }
                if ($error == 0) {
                    $cmd->commit();
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = 'Error de Ejecución de Proceso';
                    $cmd->rollBack();
                }
            } else {
                if ($estado != 1) {
                    $res['mensaje'] = 'Solo puede Cerrar traslados en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'El traslado no tiene detalles';
                }
            }
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = 'SELECT acf_traslado.estado,acf_traslado.id_area_origen,far_centrocosto_area.id_sede,acf_traslado.id_usr_origen 
                    FROM acf_traslado 
                    INNER JOIN far_centrocosto_area ON (far_centrocosto_area.id_area=acf_traslado.id_area_origen)
                    WHERE id_traslado=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_traslado = $rs->fetch();
            $estado = $obj_traslado['estado'];
            $id_area_origen = isset($obj_traslado['id_area_origen']) ? $obj_traslado['id_area_origen'] : 0;
            $id_sede_origen = isset($obj_traslado['id_sede']) ? $obj_traslado['id_sede'] : 0;
            $id_usr_origen = isset($obj_traslado['id_usr_origen']) ? $obj_traslado['id_usr_origen'] : 0;

            $sql = "SELECT IF(SUM(IF(acf_traslado.id_area_destino=acf_hojavida.id_area,1,0))=COUNT(*),1,0) AS continuar
                    FROM acf_traslado_detalle
                    INNER JOIN acf_traslado ON (acf_traslado.id_traslado=acf_traslado_detalle.id_traslado)
                    INNER JOIN acf_hojavida ON (acf_hojavida.id_activo_fijo=acf_traslado_detalle.id_activo_fijo)
                    WHERE acf_traslado.id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_traslado = $rs->fetch();
            $continuar = $obj_traslado['continuar'];

            $sql = "SELECT COUNT(*) as count
                    FROM acf_traslado_detalle
                    INNER JOIN acf_traslado ON (acf_traslado.id_traslado=acf_traslado_detalle.id_traslado)
                    WHERE acf_traslado_detalle.id_activo_fijo IN (SELECT id_activo_fijo FROM acf_traslado_detalle WHERE id_traslado=$id)
                    AND acf_traslado.id_traslado>$id AND acf_traslado.estado<>0";
            $rs = $cmd->query($sql);
            $obj_traslado = $rs->fetch();
            $tra_det = $obj_traslado['count'];

            if ($estado == 2 && $continuar == 1 && $tra_det ==0) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = "UPDATE acf_traslado SET estado=0,id_usr_anula=$id_usr_ope,fec_anula='$fecha_ope' WHERE id_traslado=$id";
                $rs1 = $cmd->query($sql);

                $sql = "UPDATE acf_hojavida SET id_area=$id_area_origen,id_sede=$id_sede_origen,id_responsable=$id_usr_origen
                        WHERE id_activo_fijo IN (SELECT id_activo_fijo FROM acf_traslado_detalle WHERE id_traslado=$id)";
                $rs2 = $cmd->query($sql);

                if ($rs1 == false || $rs2 == false || error_get_last()) {
                    $error = 1;
                }
                if ($error == 0) {
                    $cmd->commit();
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = 'Error de Ejecución de Proceso';
                    $cmd->rollBack();
                }
            } else {
                if ($estado != 2) {
                    $res['mensaje'] = 'Solo se puede anular traslados en estado cerrado';
                } else if ($continuar != 1) {
                    $res['mensaje'] = 'Los Activos del traslado ya tiene traslados posteriores';
                }else if ($tra_det != 0) {
                    $res['mensaje'] = 'Los Activos estan en traslado posteriores';
                }
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
