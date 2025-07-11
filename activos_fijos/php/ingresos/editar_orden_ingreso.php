<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
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

    if ((PermisosUsuario($permisos, 5703, 2) && $oper == 'add' && $_POST['id_ingreso'] == -1) ||
        (PermisosUsuario($permisos, 5703, 3) && $oper == 'add' && $_POST['id_ingreso'] != -1) ||
        (PermisosUsuario($permisos, 5703, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5703, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5703, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_ingreso'];
            $id_sede = $_POST['id_txt_sede'];
            $fec_ing = $_POST['txt_fec_ing'];
            $hor_ing = $_POST['txt_hor_ing'];
            $num_fac = $_POST['txt_num_fac'];
            $fec_fac = $_POST['txt_fec_fac'];
            $id_tiping = $_POST['sl_tip_ing'];
            $id_tercero = $_POST['id_txt_tercero'];
            $detalle = $_POST['txt_det_ing'];

            if ($id == -1) {
                $sql = "INSERT INTO acf_orden_ingreso(fec_ingreso,hor_ingreso,num_factura,fec_factura,id_tipo_ingreso,
                        id_provedor,detalle,val_total,id_sede,id_usr_crea,fec_creacion,estado)
                    VALUES('$fec_ing','$hor_ing','$num_fac','$fec_fac',$id_tiping,
                        $id_tercero,'$detalle',0,$id_sede,$id_usr_ope,'$fecha_ope',1)";
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
                $sql = "SELECT estado FROM acf_orden_ingreso WHERE id_ingreso=" . $id;
                $rs = $cmd->query($sql);
                $obj_ingreso = $rs->fetch();

                if ($obj_ingreso['estado'] == 1) {
                    $sql = "UPDATE acf_orden_ingreso 
                        SET num_factura='$num_fac',fec_factura='$fec_fac',id_tipo_ingreso=$id_tiping,id_provedor=$id_tercero,detalle='$detalle'
                        WHERE id_ingreso=" . $id;
                    $rs = $cmd->query($sql);

                    if ($rs) {
                        $res['mensaje'] = 'ok';
                        $res['id'] = $id;
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'Solo puede Modificar Ordenes de Ingreso en estado Pendiente';
                }
            }
        }

        if ($oper == 'del') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM acf_orden_ingreso WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();

            if ($obj_ingreso['estado'] == 1) {
                $sql = "DELETE FROM acf_orden_ingreso WHERE id_ingreso=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Ordenes de Ingreso en estado Pendiente';
            }
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM acf_orden_ingreso WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();
            $estado = isset($obj_ingreso['estado']) ? $obj_ingreso['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM acf_orden_ingreso_detalle WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();
            $num_detalles = $obj_ingreso['total'];

            if ($estado == 1 && $num_detalles > 0) {

                //Verificar si ya estan regisdtados los datos basicos de los activos fijos
                $sql = "SELECT GROUP_CONCAT(nom_medicamento) AS articulos 
                        FROM (SELECT far_medicamentos.nom_medicamento,
                                    acf_orden_ingreso_detalle.cantidad,
                                    COUNT(acf_orden_ingreso_acfs.id_act_fij) AS registros
                                FROM acf_orden_ingreso_detalle
                                INNER JOIN far_medicamentos ON (far_medicamentos.id_med=acf_orden_ingreso_detalle.id_articulo)
                                LEFT JOIN acf_orden_ingreso_acfs ON (acf_orden_ingreso_acfs.id_ing_detalle=acf_orden_ingreso_detalle.id_ing_detalle)
                                WHERE id_ingreso=$id
                                GROUP BY acf_orden_ingreso_detalle.id_ing_detalle) AS c
                        WHERE cantidad>registros";
                $rs = $cmd->query($sql);
                $obj_ingreso = $rs->fetch();
                $articulos_pen = $obj_ingreso['articulos'] ? $obj_ingreso['articulos'] : "";

                if (!$articulos_pen) {
                    $error = 0;
                    $cmd->beginTransaction();
                    
                    $sql = 'SELECT num_ingresoactual FROM tb_datos_ips LIMIT 1';
                    $rs = $cmd->query($sql);
                    $obj = $rs->fetch();
                    $num_ingreso = $obj['num_ingresoactual'];
                    $res['num_ingreso'] = $num_ingreso;

                    $sql = "UPDATE acf_orden_ingreso SET num_ingreso=$num_ingreso,estado=2,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope' WHERE id_ingreso=$id";
                    $rs1 = $cmd->query($sql);
                    $sql = 'UPDATE tb_datos_ips SET num_ingresoactual=num_ingresoactual+1';
                    $rs2 = $cmd->query($sql);

                    //Traer el area y responsable de almacen
                    $area = area_principal($cmd);
                    $id_area = $area['id_area'];
                    $id_responsable = $area['id_responsable'];

                    //Crear la hojas de vida de los activos fijos
                    $sql = "INSERT INTO acf_hojavida(id_ingreso,id_articulo,des_activo,placa,num_serial,id_marca,valor,tipo_activo,id_proveedor,id_tipo_ingreso,id_sede,id_area,id_responsable,id_usr_crea,fec_creacion,estado_general,estado) 
                            SELECT $id,acf_orden_ingreso_acfs.id_articulo,
                                CONCAT_WS(' ',far_medicamentos.nom_medicamento,acf_marca.descripcion) AS nom_activo,
                                acf_orden_ingreso_acfs.placa,acf_orden_ingreso_acfs.num_serial,acf_orden_ingreso_acfs.id_marca,
                                acf_orden_ingreso_acfs.valor,acf_orden_ingreso_acfs.tipo_activo,acf_orden_ingreso.id_provedor,
                                acf_orden_ingreso.id_tipo_ingreso,acf_orden_ingreso.id_sede,$id_area,$id_responsable,$id_usr_ope,'$fecha_ope',1,1
                            FROM acf_orden_ingreso_acfs
                            INNER JOIN acf_orden_ingreso_detalle ON (acf_orden_ingreso_detalle.id_ing_detalle=acf_orden_ingreso_acfs.id_ing_detalle)
                            INNER JOIN acf_orden_ingreso ON (acf_orden_ingreso.id_ingreso=acf_orden_ingreso_detalle.id_ingreso)
                            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=acf_orden_ingreso_acfs.id_articulo)
                            INNER JOIN acf_marca ON (acf_marca.id=acf_orden_ingreso_acfs.id_marca)
                            WHERE acf_orden_ingreso_detalle.id_ingreso=" . $id;
                    $rs3 = $cmd->query($sql);
                    
                    if ($rs1 == false || $rs2 == false || $rs3 == false || error_get_last()) {
                        $error = 1;
                    }                
                    if ($error == 0) {
                        $cmd->commit();
                        $res['mensaje'] = 'ok';
                    } else {
                        $cmd->rollBack();
                        $res['mensaje'] = 'Error de Ejecución de Proceso';                        
                    }
                } else {
                    $res['mensaje'] = 'Debe registar los datos básicos de los Articulos: ' . $articulos_pen;                
                }
            } else {         
                if ($estado != 1) {
                    $res['mensaje'] = 'Solo puede Cerrar Ordenes de Ingreso en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'La Ordenes de Ingreso no tiene detalles';                
                }
            }    
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM acf_orden_ingreso WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();
            $estado = $obj_ingreso['estado'];

            if ($estado == 2) {

                //Verificar si se puede anular, siempre que los Activos no se hayan modificado
                $sql = "SELECT COUNT(*) AS total FROM acf_hojavida WHERE id_usr_actualiza IS NOT NULL AND id_ingreso=" . $id;
                $rs = $cmd->query($sql);
                $obj = $rs->fetch();

                IF ($obj['total'] == 0){
                    
                    $error = 0;
                    $cmd->beginTransaction();

                    $sql = "UPDATE acf_orden_ingreso SET id_usr_anula=$id_usr_ope,fec_anulacion='$fecha_ope',estado=0 WHERE id_ingreso=$id";
                    $rs1 = $cmd->query($sql);
                    $sql = 'DELETE FROM acf_hojavida WHERE id_ingreso=' . $id;
                    $rs2 = $cmd->query($sql);

                    if ($rs1 == false || $rs2 == false || error_get_last()) {
                        $error = 1;
                    }                
                    if ($error == 0) {
                        $cmd->commit();
                        $res['mensaje'] = 'ok';
                        $accion = 'Anular';
                        $opcion = 'Orden de Ingreso Activos Fijos';
                        $detalle = 'Anulo Orden Ingreso Id: ' . $id;
                        bitacora($accion, $opcion, $detalle, $id_usr_ope, $_SESSION['user']);
                    } else {
                        $res['mensaje'] = 'Error de Ejecución de Proceso';
                        $cmd->rollBack();
                    }
                } else {
                    $res['mensaje'] = 'No puede Anular la Ordenes de Ingreso, los Activos Fijos ya fueron Actualizados';
                }   
            } else {
                $res['mensaje'] = 'Solo puede Anular Ordenes de Ingreso en estado Cerrado';
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
