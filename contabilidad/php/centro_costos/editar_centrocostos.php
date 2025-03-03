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

    if ((PermisosUsuario($permisos, 5508, 2) && $oper == 'add' && $_POST['id_centrocosto'] == -1) ||
        (PermisosUsuario($permisos, 5508, 3) && $oper == 'add' && $_POST['id_centrocosto'] != -1) ||
        (PermisosUsuario($permisos, 5508, 4) && $oper == 'del') || $id_rol == 1
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_centrocosto'];
            $nom_centro = $_POST['txt_nom_centrocosto'];
            $es_clinico = $_POST['rdo_escli_cec'];
            $id_respon = $_POST['id_txt_responsable'] ? $_POST['id_txt_responsable'] : 0;

            if ($id == -1) {
                $sql = "INSERT INTO tb_centrocostos(nom_centro,es_clinico,id_responsable,id_usr_crea) 
                        VALUES('$nom_centro',$es_clinico,$id_respon,$id_usr_crea)";
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
                $sql = "UPDATE tb_centrocostos 
                        SET nom_centro='$nom_centro',es_clinico=$es_clinico,id_responsable=$id_respon 
                        WHERE id_centro=" . $id;
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
            $sql = "DELETE FROM tb_centrocostos WHERE id_centro=" . $id;
            $rs = $cmd->query($sql);
            if ($rs) {
                include '../../../financiero/reg_logs.php';
                $ruta = '../../../log';
                $consulta = "DELETE FROM tb_centrocostos WHERE id_centro = $id";
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
