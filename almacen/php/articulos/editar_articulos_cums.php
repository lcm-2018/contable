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

    if ((PermisosUsuario($permisos, 5002, 2) && $oper == 'add' && $_POST['id_cum'] == -1) ||
        (PermisosUsuario($permisos, 5002, 3) && $oper == 'add' && $_POST['id_cum'] != -1) ||
        (PermisosUsuario($permisos, 5002, 4) && $oper == 'del') || $id_rol == 1) {

        $id_articulo = $_POST['id_articulo'];

        if ($id_articulo > 0) {
            if ($oper == 'add') {
                $id = $_POST['id_cum'];
                $cod_cum = $_POST['txt_cod_cum'];
                $cod_ium = $_POST['txt_cod_ium'];
                $id_lab = $_POST['id_txt_lab_cum'] ? $_POST['id_txt_lab_cum'] : 0;
                $id_precom = $_POST['id_txt_precom_cum'] ? $_POST['id_txt_precom_cum'] : 0;
                $estado = $_POST['sl_estado_cum'];

                if ($id == -1) {
                    $sql = "INSERT INTO far_medicamento_cum(cum,ium,id_lab,id_prescom,estado,id_usr_crea,id_med,con_sismed,uni_fac_sismed)  
                        VALUES('$cod_cum','$cod_ium',$id_lab,$id_precom,$estado,$id_usr_crea,$id_articulo,1,'C')";
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
                    $sql = "UPDATE far_medicamento_cum SET cum='$cod_cum',ium='$cod_ium',id_lab=$id_lab,id_prescom=$id_precom,estado=$estado
                        WHERE id_cum=" . $id;
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
                $sql = "DELETE FROM far_medicamento_cum WHERE id_cum=" . $id;
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
