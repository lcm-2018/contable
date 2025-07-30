<?php

if (isset($_POST['tipo'])) {
    session_start();

    include '../../../conexion.php';
    include '../common/funciones_generales.php';
    include '../common/funciones_kardex.php';
    include '../../../permisos.php';
    //Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir

    try {
        
        if (PermisosUsuario($permisos, 5009, 2) || PermisosUsuario($permisos, 5009, 3) || $id_rol == 1) {

            $idlot = isset($_POST['art']) ? implode(",",$_POST['art']) : '';

            if ($idlot != ''){            
                $tipo = $_POST['tipo'];
                $iding = $_POST['id_ing'];
                $idegr = $_POST['id_egr'];
                $idtra = $_POST['id_tra'];
                $iddev = 0;
                $fecini = $_POST['fec_ini'];

                set_time_limit(0);
                $res = array();

                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

                $cmd->beginTransaction();

                recalcular_kardex($cmd, $idlot, $tipo, $iding, $idegr, $idtra, $iddev, $fecini);

                /*Cuenta cuantos errores ocurrieron al ejecutar el script*/
                $errores = error_get_last();
                if (!$errores) {
                    $cmd->commit();
                    $res['mensaje'] = 'ok';
                    $accion = 'Actualizar';
                    $opcion = 'Recalcular Kardex';
                    $detalle = 'Recalcular Kardex en la fecha : ' . date('Y-m-d H:i:s');
                    bitacora($accion, $opcion, $detalle, $_SESSION['id_user'], $_SESSION['user']);
                } else {
                    $res['mensaje'] = 'Error de Ejecución de Proceso';
                    $cmd->rollBack();
                }
            } else {
                $res['mensaje'] = 'Debe seleccionar un registro para reclacular kardex';
            }
        } else {
            $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
        }   

        $cmd = null;
    } catch (PDOException $e) {
        $res['mensaje'] = $e->getCode();
    }
    echo json_encode($res);
}