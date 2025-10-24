<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5511, 2) && $oper == 'add' && $_POST['id_tipo_egreso'] == -1) ||
        (PermisosUsuario($permisos, 5511, 3) && $oper == 'add' && $_POST['id_tipo_egreso'] != -1) ||
        (PermisosUsuario($permisos, 5511, 4) && $oper == 'del') || $id_rol == 1
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_tipo_egreso'];
            $nom_tipo_egreso = $_POST['txt_nom_tipoegreso'];
            $es_int_ext = $_POST['sl_esintext'];
            $con_pedido = $_POST['sl_conpedido'];
            $dev_fianza = $_POST['sl_devfianza'];
            $consumo = $_POST['sl_consumo'];
            $farmacia = $_POST['sl_farmacia'];
            $almacen = $_POST['sl_almacen'];
            $activofijo = $_POST['sl_activofijo'];

            if ($id == -1) {
                $sql = "INSERT INTO far_orden_egreso_tipo(nom_tipo_egreso,es_int_ext,con_pedido,dev_fianza,consumo,farmacia,almacen,activofijo,id_usr_crea) 
                        VALUES('$nom_tipo_egreso',$es_int_ext,$con_pedido,$dev_fianza,$consumo,$farmacia,$almacen,$activofijo,$id_usr_ope)";
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

                $sql = "UPDATE far_orden_egreso_tipo 
                        SET nom_tipo_egreso='$nom_tipo_egreso',es_int_ext=$es_int_ext,con_pedido=$con_pedido,
                            dev_fianza=$dev_fianza,consumo=$consumo,farmacia=$farmacia,almacen=$almacen,activofijo=$activofijo 
                        WHERE id_tipo_egreso>2 AND id_tipo_egreso=" . $id;
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
            $sql = "DELETE FROM far_orden_egreso_tipo WHERE id_tipo_egreso>2 AND id_tipo_egreso=" . $id;
            $rs = $cmd->query($sql);
            if ($rs) {
                include '../../../financiero/reg_logs.php';
                $ruta = '../../../log';
                $consulta = "DELETE FROM far_tipos_orden_egreso WHERE id_subgrupo = $id";
                RegistraLogs($ruta, $consulta);
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
