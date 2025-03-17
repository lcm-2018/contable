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

    if ((PermisosUsuario($permisos, 5006, 2) && $oper == 'add' && $_POST['id_detalle'] == -1) ||
        (PermisosUsuario($permisos, 5006, 3) && $oper == 'add' && $_POST['id_detalle'] != -1) ||
        (PermisosUsuario($permisos, 5006, 4) && $oper == 'del') || $id_rol == 1
    ) {

        $id_ingreso = $_POST['id_ingreso'];

        if ($id_ingreso > 0) {

            $sql = "SELECT far_orden_ingreso.estado,far_orden_ingreso.id_pedido,far_orden_ingreso_tipo.id_tipo_ingreso,far_orden_ingreso_tipo.orden_compra
                    FROM far_orden_ingreso 
                    INNER JOIN far_orden_ingreso_tipo ON (far_orden_ingreso_tipo.id_tipo_ingreso = far_orden_ingreso.id_tipo_ingreso)
                    WHERE far_orden_ingreso.id_ingreso=" . $id_ingreso;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();
            $estado = $obj_ingreso['estado'];
            $id_tipo_ing = $obj_ingreso['id_tipo_ingreso'];
            $orden_compra = $obj_ingreso['orden_compra'];
            $id_pedido = $obj_ingreso['id_pedido'] ? $obj_ingreso['id_pedido'] : 0;

            if ($estado == 1) {
                if ($oper == 'add') {

                    //Id. Tipo Ingreso e Id. pedido del formulario
                    $id_tipo_ing2 = $_POST['id_tipo_ing'] ? $_POST['id_tipo_ing'] : 0;
                    $id_pedido2 = $_POST['id_pedido'] ? $_POST['id_pedido'] : 0;                    

                    if($id_tipo_ing2 == $id_tipo_ing && ($orden_compra == 0 || ($orden_compra == 1 && $id_pedido2 == $id_pedido))){
                        $id = $_POST['id_detalle'];
                        $id_lote = $_POST['sl_lote_art'];
                        $id_pre_lot = $_POST['id_txt_pre_lot'];
                        $cant_umpl = $_POST['txt_can_lot'];
                        $cantidad = $_POST['txt_can_ing'] ? $_POST['txt_can_ing'] : 1;
                        $vr_unidad = $_POST['txt_val_uni'] ? $_POST['txt_val_uni'] : 0;
                        $iva = $_POST['sl_por_iva'] ? $_POST['sl_por_iva'] : 0;
                        $vr_costo = $_POST['txt_val_cos'];
                        $observacion = $_POST['txt_observacion'];

                        //Verificar si es una Orden de compra que la entrada no supere lo aprobado
                        $can_aprobado=0;
                        $can_ingresada=0;
                        if ($orden_compra == 1 && $id_pedido != 0){
                            $sql = "SELECT id_med,id_bodega FROM far_medicamento_lote WHERE id_lote=" . $id_lote;
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();
                            $id_articulo = $obj['id_med'];
                            $id_bodega = $obj['id_bodega'];

                            $sql = "SELECT aprobado FROM far_alm_pedido_detalle WHERE id_pedido=$id_pedido AND id_medicamento=$id_articulo";
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();
                            $can_aprobado = $obj['aprobado'];

                            $sql = "SELECT SUM(far_orden_ingreso_detalle.cantidad*far_presentacion_comercial.cantidad) AS cantidad
                                    FROM far_orden_ingreso_detalle
                                    INNER JOIN far_orden_ingreso ON (far_orden_ingreso.id_ingreso=far_orden_ingreso_detalle.id_ingreso)
                                    INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_orden_ingreso_detalle.id_lote)
                                    INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_orden_ingreso_detalle.id_presentacion)
                                    WHERE far_orden_ingreso.estado<>0 AND far_orden_ingreso.id_bodega=$id_bodega AND far_orden_ingreso.id_pedido=$id_pedido AND 
                                        far_medicamento_lote.id_med=$id_articulo AND far_orden_ingreso_detalle.id_ing_detalle<>$id";
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();
                            $can_ingresada = $obj['cantidad'] + $cantidad * $cant_umpl;
                        }

                        if (($can_ingresada <= $can_aprobado) || $orden_compra == 0){
                            
                            $sql = "SELECT COUNT(*) AS existe FROM far_orden_ingreso_detalle WHERE id_ingreso=$id_ingreso AND id_lote=" . $id_lote . " AND id_ing_detalle<>" . $id;
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();

                            if ($obj['existe'] == 0) {
                                if ($id == -1) {                                
                                    $sql = "INSERT INTO far_orden_ingreso_detalle(id_ingreso,id_lote,id_presentacion,cantidad,valor_sin_iva,iva,valor,observacion)
                                            VALUES($id_ingreso,$id_lote,$id_pre_lot,$cantidad,$vr_unidad,$iva,$vr_costo,'$observacion')";
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
                                    $sql = "UPDATE far_orden_ingreso_detalle 
                                        SET id_lote=$id_lote,id_presentacion=$id_pre_lot,cantidad=$cantidad,valor_sin_iva=$vr_unidad,iva=$iva,valor=$vr_costo,observacion='$observacion'
                                        WHERE id_ing_detalle=" . $id;

                                    $rs = $cmd->query($sql);
                                    if ($rs) {
                                        $res['mensaje'] = 'ok';
                                        $res['id'] = $id;
                                    } else {
                                        $res['mensaje'] = $cmd->errorInfo()[2];
                                    }
                                }   
                            } else {
                                $res['mensaje'] = 'El Lote ya existe en los detalles de la Orden de Ingreso';
                            }                         
                        } else {
                            $res['mensaje'] = 'La Cantidad a ingresar supera la Cantidad aprobada';       
                        }        
                    } else {
                        $res['mensaje'] = 'Primero debe guardar la Orden de Ingreso, para continuar';
                    }
                }

                if ($oper == 'del') {
                    $id = $_POST['id'];
                    $sql = "DELETE FROM far_orden_ingreso_detalle WHERE id_ing_detalle=" . $id;
                    $rs = $cmd->query($sql);
                    if ($rs) {
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                }

                if ($rs) {
                    $sql = "UPDATE far_orden_ingreso SET val_total=(SELECT SUM(valor*cantidad) FROM far_orden_ingreso_detalle WHERE id_ingreso=$id_ingreso) WHERE id_ingreso=$id_ingreso";
                    $rs = $cmd->query($sql);

                    $sql = "SELECT val_total FROM far_orden_ingreso WHERE id_ingreso=" . $id_ingreso;
                    $rs = $cmd->query($sql);
                    $obj_ingreso = $rs->fetch();
                    $res['val_total'] = formato_valor($obj_ingreso['val_total']);
                }
            } else {
                $res['mensaje'] = 'Solo puede Modificar Ordenes de Ingreso en estado Pendiente';
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar la Orden de Ingreso';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
