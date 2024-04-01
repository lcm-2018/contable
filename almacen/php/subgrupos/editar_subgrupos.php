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
$fecha_crea = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5001, 2) && $oper == 'add' && $_POST['id_subgrupo'] == -1) ||
        (PermisosUsuario($permisos, 5001, 3) && $oper == 'add' && $_POST['id_subgrupo'] != -1) ||
        (PermisosUsuario($permisos, 5001, 4) && $oper == 'del') || $id_rol == 1) {

        if ($oper == 'add') {
            $id = $_POST['id_subgrupo'];
            $cod_subgrupo = $_POST['txt_cod_subgrupo'];
            $nom_subgrupo = $_POST['txt_nom_subgrupo'];
            $id_grupo = $_POST['sl_grp_subgrupo'] ? $_POST['sl_grp_subgrupo'] : 0;
            $estado = $_POST['sl_estado'];

            if ($id == -1) {
                $sql = "INSERT INTO far_subgrupos(cod_subgrupo,nom_subgrupo,id_grupo,estado,id_usr_crea,fec_crea) 
                        VALUES($cod_subgrupo,'$nom_subgrupo',$id_grupo,$estado,$id_usr_ope,'$fecha_ope')";
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
                $sql = "UPDATE far_subgrupos 
                        SET cod_subgrupo=$cod_subgrupo,nom_subgrupo='$nom_subgrupo',id_grupo=$id_grupo,estado=$estado 
                        WHERE id_subgrupo=" . $id;
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
            $sql = "DELETE FROM far_subgrupos WHERE id_subgrupo=" . $id;
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
