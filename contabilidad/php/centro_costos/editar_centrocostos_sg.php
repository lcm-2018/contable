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

    if ((PermisosUsuario($permisos, 5508, 2) && $oper == 'add' && $_POST['id_cec_sg'] == -1) ||
        (PermisosUsuario($permisos, 5508, 3) && $oper == 'add' && $_POST['id_cec_sg'] != -1) ||
        (PermisosUsuario($permisos, 5508, 4) && $oper == 'del') || $id_rol == 1
    ) {

        $id_cencos = $_POST['id_cencos'];

        if ($id_cencos > 0) {
            if ($oper == 'add') {
                $id = $_POST['id_cec_sg'];
                $fec_vig = $_POST['txt_fec_vig'] ? "'" . $_POST['txt_fec_vig'] . "'" : 'NULL';
                $estado = $_POST['sl_estado_cta'];

                if ($id == -1) {
                    $sql = "INSERT INTO tb_centrocostos_subgr_cta(id_cencos,fecha_vigencia,estado,id_usr_crea,fec_creacion)  
                            VALUES($id_cencos,$fec_vig,$estado,$id_usr_crea,'$fecha_crea')";
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
                    $sql = "UPDATE tb_centrocostos_subgr_cta 
                            SET fecha_vigencia=$fec_vig,estado=$estado
                            WHERE id_cecsubgrp=" . $id;
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
                $sql = "DELETE FROM tb_centrocostos_subgr_cta WHERE id_cecsubgrp=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    include '../../../financiero/reg_logs.php';
                    $ruta = '../../../log';
                    $consulta = "DELETE FROM tb_centrocostos_subgr_cta WHERE id_cecsubgrp = $id";
                    RegistraLogs($ruta, $consulta);
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar el Centro de Costo';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
