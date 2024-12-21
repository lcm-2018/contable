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

    if ((PermisosUsuario($permisos, 5701, 2) && $oper == 'add' && $_POST['id_articulo'] == -1) ||
        (PermisosUsuario($permisos, 5701, 3) && $oper == 'add' && $_POST['id_articulo'] != -1) ||
        (PermisosUsuario($permisos, 5701, 4) && $oper == 'del') || $id_rol == 1) {

        if ($oper == 'add') {
            $id = $_POST['id_articulo'];
            $cod_art = $_POST['txt_cod_art'];
            $nom_art = $_POST['txt_nom_art'];
            $id_subgrp = $_POST['sl_subgrp_art'] ? $_POST['sl_subgrp_art'] : 0;
            $top_min = $_POST['txt_topmin_art'];
            $top_max = $_POST['txt_topmax_art'];
            $id_unimed = $_POST['id_txt_unimed_art'] ? $_POST['id_txt_unimed_art'] : 0;
            $estado = $_POST['sl_estado'];

            if ($id == -1) {
                $sql = "INSERT INTO far_medicamentos(cod_medicamento,nom_medicamento,id_subgrupo,top_min,top_max,
                            id_unidadmedida_2,id_unidadmedida,id_formafarmaceutica,id_atc,es_clinico,id_tip_medicamento,estado,id_usr_crea) 
                        VALUES($cod_art,'$nom_art',$id_subgrp,$top_min,$top_max,$id_unimed,0,0,0,0,NULL,$estado,$id_usr_crea)";
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
                $sql = "UPDATE far_medicamentos SET cod_medicamento=$cod_art,nom_medicamento='$nom_art',
                            id_subgrupo=$id_subgrp,top_min=$top_min,top_max=$top_max,id_unidadmedida_2=$id_unimed,estado=$estado
                        WHERE id_med=" . $id;
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
            $sql = "DELETE FROM far_medicamentos WHERE id_med=" . $id;
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
