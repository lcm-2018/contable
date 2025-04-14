<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    
    if ((PermisosUsuario($permisos, 5705, 2) && $oper == 'add' && $_POST['id_pedido'] == -1) ||
        (PermisosUsuario($permisos, 5705, 3) && $oper == 'add' && $_POST['id_pedido'] != -1) ||
        (PermisosUsuario($permisos, 5705, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5705, 3) && $oper == 'aprob') ||
        (PermisosUsuario($permisos, 5705, 3) && $oper == 'ejecu') ||
        (PermisosUsuario($permisos, 5705, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5705, 5) && $oper == 'annul') || $id_rol == 1
      ){

        $id = 0;

        if ($oper == 'add') {
            $id = $_POST['id_mantenimiento'];

            if ($id == -1) {                
                $sql = "INSERT INTO acf_mantenimiento (fec_mantenimiento,hor_mantenimiento,observaciones,
                        tipo_mantenimiento,id_responsable,id_tercero,fec_ini_mantenimiento, 
                        fec_fin_mantenimiento,estado,id_usr_crea,fec_creacion) 
                    VALUES (:fec_mantenimiento,:hor_mantenimiento,:observaciones,:tipo_mantenimiento,
                        :id_responsable,:id_tercero,:fec_ini_mantenimiento,:fec_fin_mantenimiento,
                        :estado,:id_usr_crea,:fec_creacion)";        
                $sql = $cmd->prepare($sql);
                
                $sql->bindValue(':fec_mantenimiento', $_POST['txt_fec_mant']);
                $sql->bindValue(':hor_mantenimiento', $_POST['txt_hor_mant']);
                $sql->bindValue(':observaciones', $_POST['txt_observaciones_mant']);
                $sql->bindValue(':tipo_mantenimiento', $_POST['sl_tip_mant'], PDO::PARAM_INT);
                $sql->bindValue(':id_responsable', $_POST['sl_responsable'], PDO::PARAM_INT);
                $sql->bindValue(':id_tercero', $_POST['sl_tercero'] ? $_POST['sl_tercero'] : 0, PDO::PARAM_INT);
                $sql->bindValue(':fec_ini_mantenimiento', $_POST['txt_fec_ini_mant'] ? $_POST['txt_fec_ini_mant'] : null);
                $sql->bindValue(':fec_fin_mantenimiento', $_POST['txt_fec_fin_mant'] ? $_POST['txt_fec_fin_mant'] : null);
                $sql->bindValue(':estado', 1, PDO::PARAM_INT);
                $sql->bindValue(':id_usr_crea', $id_usr_ope, PDO::PARAM_INT);
                $sql->bindValue(':fec_creacion', $fecha_ope);
                $inserted = $sql->execute();

                if ($inserted) {
                    $id = $cmd->lastInsertId();
                    $res['mensaje'] = 'ok';
                    $res['id'] = $id;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }   
            } else {         
                $sql = "SELECT estado FROM acf_mantenimiento WHERE id_mantenimiento=" . $id;
                $rs = $cmd->query($sql);
                $obj_mant = $rs->fetch();
                
                if ($obj_mant['estado'] == 1) {
                    $sql = "UPDATE acf_mantenimiento SET
                                observaciones = :observaciones,
                                tipo_mantenimiento = :tipo_mantenimiento,
                                id_responsable = :id_responsable,
                                id_tercero = :id_tercero,                            
                                fec_ini_mantenimiento = :fec_ini_mantenimiento,
                                fec_fin_mantenimiento = :fec_fin_mantenimiento
                            WHERE id_mantenimiento = :id_mantenimiento";
                    $sql = $cmd->prepare($sql);

                    $sql->bindValue(':observaciones', $_POST['txt_observaciones_mant']);
                    $sql->bindValue(':tipo_mantenimiento', $_POST['sl_tip_mant'], PDO::PARAM_INT);
                    $sql->bindValue(':id_responsable', $_POST['sl_responsable'], PDO::PARAM_INT);
                    $sql->bindValue(':id_tercero', $_POST['sl_tercero'] ? $_POST['sl_tercero'] : 0, PDO::PARAM_INT);
                    $sql->bindValue(':fec_ini_mantenimiento', $_POST['txt_fec_ini_mant'] ? $_POST['txt_fec_ini_mant'] : null);
                    $sql->bindValue(':fec_fin_mantenimiento', $_POST['txt_fec_fin_mant'] ? $_POST['txt_fec_fin_mant'] : null);
                    $sql->bindValue(':id_mantenimiento', $id);
                    $updated = $sql->execute();

                    if ($updated) {
                        $res['mensaje'] = 'ok';
                        $res['id'] = $id;
                    } else {
                        $res['mensaje'] = $sql->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'Solo puede Modificar Ordenes de Mantenimiento en estado Pendiente';
                }    
            }
        }

        if ($oper == 'del') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM acf_mantenimiento WHERE id_mantenimiento=" . $id;
            $rs = $cmd->query($sql);
            $obj_mant = $rs->fetch();
                
            if ($obj_mant['estado'] == 1) {
                $sql = "DELETE FROM acf_mantenimiento WHERE id_mantenimiento=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Ordenes de Mantenimiento en estado Pendiente' ;
            }
        }

        if ($oper == 'aprob') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM acf_mantenimiento WHERE id_mantenimiento=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_mant = $rs->fetch();
            $estado = isset($obj_mant['estado']) ? $obj_mant['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM acf_mantenimiento_detalle WHERE id_mantenimiento=" . $id;
            $rs = $cmd->query($sql);
            $obj_mant = $rs->fetch();
            $num_detalles = $obj_mant['total'];

            if ($estado == 1 && $num_detalles > 0) {
                $sql = "SELECT GROUP_CONCAT(acf_hojavida.placa) as placas 
                        FROM acf_mantenimiento_detalle 
                        INNER JOIN acf_hojavida ON (acf_hojavida.id_activo_fijo = acf_mantenimiento_detalle.id_activo_fijo)
                        WHERE acf_mantenimiento_detalle.id_mantenimiento=$id AND acf_hojavida.estado IN (4,5)";
                $rs = $cmd->query($sql);
                $obj_mant = $rs->fetch();
                $placas = isset($obj_mant['placas']) ? $obj_mant['placas'] : '';

                if(!$placas){
                    $error = 0;
                    $cmd->beginTransaction();

                    $sql = "UPDATE acf_mantenimiento SET estado=2,id_usr_aprueba=$id_usr_ope,fec_aprueba='$fecha_ope' WHERE id_mantenimiento=$id";
                    $rs1 = $cmd->query($sql);
                    
                    if ($rs1 == false || error_get_last()) {
                        $error = 1;
                    }                
                    if ($error == 0) {
                        $cmd->commit();
                        $res['mensaje'] = 'ok';
                    } else {
                        $cmd->rollBack();
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'La Ordenes de Mantenimiento tiene Equipos Inactivos : ' . $placas;    
                }    
            } else {
                if ($estado != 1) {
                    $res['mensaje'] = 'Solo puede Aprobar Ordenes de Mantenimiento en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'La Orden de Mantenimiento no tiene detalles';
                }
            }   
        }

        if ($oper == 'ejecu') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM acf_mantenimiento WHERE id_mantenimiento=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_mant = $rs->fetch();
            $estado = isset($obj_mant['estado']) ? $obj_mant['estado'] : -1;

            if ($estado == 2) {
                $sql = "SELECT GROUP_CONCAT(acf_hojavida.placa) as placas 
                        FROM acf_mantenimiento_detalle 
                        INNER JOIN acf_hojavida ON (acf_hojavida.id_activo_fijo = acf_mantenimiento_detalle.id_activo_fijo)
                        WHERE acf_mantenimiento_detalle.id_mantenimiento=$id AND acf_hojavida.estado IN (4,5)";
                $rs = $cmd->query($sql);
                $obj_mant = $rs->fetch();
                $placas = isset($obj_mant['placas']) ? $obj_mant['placas'] : '';

                if(!$placas){
                    $error = 0;
                    $cmd->beginTransaction();

                    $sql = "UPDATE acf_mantenimiento SET estado=3,id_usr_ejecucion=$id_usr_ope,fec_ejecucion='$fecha_ope' WHERE id_mantenimiento=" . $id;
                    $rs1 = $cmd->query($sql);
                    
                    if ($rs1 == false || error_get_last()) {
                        $error = 1;
                    }                
                    if ($error == 0) {
                        $cmd->commit();
                        $res['mensaje'] = 'ok';
                    } else {
                        $cmd->rollBack();
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'La Ordenes de Mantenimiento tiene Equipos Inactivos : ' . $placas;    
                }     
            } else {
                $res['mensaje'] = 'Solo puede por en Ejecución Ordenes de Mantenimiento en estado Aprobado';
            }   
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM acf_mantenimiento WHERE id_mantenimiento=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_mant = $rs->fetch();
            $estado = isset($obj_mant['estado']) ? $obj_mant['estado'] : -1;

            if ($estado == 3) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = "UPDATE acf_mantenimiento SET estado=4,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope' WHERE id_mantenimiento=" . $id;
                $rs1 = $cmd->query($sql);
                
                if ($rs1 == false || error_get_last()) {
                    $error = 1;
                }                
                if ($error == 0) {
                    $cmd->commit();
                    $res['mensaje'] = 'ok';
                } else {
                    $cmd->rollBack();
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Cerrar Ordenes de Mantenimiento en estado En Ejecución';
            }   
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM acf_mantenimiento WHERE id_mantenimiento=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_mant = $rs->fetch();
            $estado = isset($obj_mant['estado']) ? $obj_mant['estado'] : -1;

            if ($estado == 2) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = "UPDATE acf_mantenimiento SET estado=0,id_usr_anula=$id_usr_ope,fec_anulacion='$fecha_ope' WHERE id_mantenimiento=" . $id;
                $rs1 = $cmd->query($sql);
                
                if ($rs1 == false || error_get_last()) {
                    $error = 1;
                }                
                if ($error == 0) {
                    $cmd->commit();
                    $res['mensaje'] = 'ok';
                } else {
                    $cmd->rollBack();
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Anular Ordenes de Mantenimiento en estado Aprobado';
            }   
        }

        //Actualiza el estado de los activo de la Orden siempre y cuando su estado actual sea 1,2 o 3
        if ($oper == 'aprob' || $oper == 'ejecu' || $oper == 'close' || $oper == 'annul') {
            $sql = "SELECT id_activo_fijo FROM acf_mantenimiento_detalle WHERE id_mantenimiento=" . $id;
            $rs = $cmd->query($sql);
            $objs = $rs->fetchAll();
            foreach ($objs as $obj) {
                $estado = estados_activo_fijo($cmd, $obj['id_activo_fijo'])['estado'];
                $sql = "UPDATE acf_hojavida SET estado=$estado WHERE estado IN (1,2,3) AND id_activo_fijo=" . $obj['id_activo_fijo'];
                $rs1 = $cmd->query($sql);
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
