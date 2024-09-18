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
$fecha_crea = date('Y-m-d H:i:s');
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5015, 2) && $oper == 'add' && $_POST['id_area'] == -1) ||
        (PermisosUsuario($permisos, 5015, 3) && $oper == 'add' && $_POST['id_area'] != -1) ||
        (PermisosUsuario($permisos, 5015, 4) && $oper == 'del') || $id_rol == 1
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_area'];
            $nom_area = $_POST['txt_nom_area'];
            $id_cencos = $_POST['sl_centrocosto'] ? $_POST['sl_centrocosto'] : 0;
            $id_tipare = $_POST['sl_tipo_area'] ? $_POST['sl_tipo_area'] : 0;
            $id_respon = $_POST['id_txt_responsable'] ? $_POST['id_txt_responsable'] : 0;
            $id_sede = $_POST['sl_sede'] ? $_POST['sl_sede'] : 1;
            $id_bodega = $_POST['sl_bodega'] ? $_POST['sl_bodega'] : 'NULL';

            if ($id == -1) {
                $sql = "INSERT INTO far_centrocosto_area(nom_area,id_centrocosto,id_tipo_area,id_responsable,id_sede,id_bodega,id_usr_crea,fec_crea) 
                        VALUES('$nom_area',$id_cencos,$id_tipare,$id_respon,$id_sede,$id_bodega,$id_usr_crea,'$fecha_crea')";
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
                $sql = "UPDATE far_centrocosto_area 
                        SET nom_area='$nom_area',id_centrocosto=$id_cencos,id_tipo_area=$id_tipare,
                            id_responsable=$id_respon,id_sede=$id_sede,id_bodega=$id_bodega 
                        WHERE id_area=" . $id;
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
            $sql = "DELETE FROM far_centrocosto_area WHERE id_area=" . $id;
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
