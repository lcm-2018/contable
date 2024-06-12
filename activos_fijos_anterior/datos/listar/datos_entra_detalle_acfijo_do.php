<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$tipo = isset($_POST['tip_eaf_det']) ? $_POST['tip_eaf_det'] : exit('Accion no permitida');
$id_acfi = isset($_POST['id_acfi_det']) ? $_POST['id_acfi_det'] : exit('Accion no permitida');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_entra_detalle_activos_fijos`.`id_acfijo`
                , `seg_entra_detalle_activos_fijos`.`id_prod`
                , `ctt_bien_servicio`.`bien_servicio`
                , `seg_entra_detalle_activos_fijos`.`id_entra_acfijo_do`
                , `seg_entra_detalle_activos_fijos`.`mantenimiento`
                , `seg_entra_detalle_activos_fijos`.`depreciable`
                , `seg_entra_detalle_activos_fijos`.`marca`
                , `seg_entra_detalle_activos_fijos`.`modelo`
                , `seg_entra_detalle_activos_fijos`.`val_unit`
                , `seg_entra_detalle_activos_fijos`.`descripcion`
                , `seg_entra_detalle_activos_fijos`.`cantidad`
                , `seg_entra_detalle_activos_fijos`.`id_tipo_activo`
                , `seg_tipo_activo`.`descripcion` AS `tipo_activo`
                , `acf_entrada`.`estado`
            FROM
                `seg_entra_detalle_activos_fijos`
                INNER JOIN `ctt_bien_servicio` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_prod` = `ctt_bien_servicio`.`id_b_s`)
                INNER JOIN `acf_entrada` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_entra_acfijo_do` = `acf_entrada`.`id_entra_af`)
                INNER JOIN `seg_tipo_activo` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_tipo_activo` = `seg_tipo_activo`.`id_tipo_act`)
            WHERE `seg_entra_detalle_activos_fijos`.`id_entra_acfijo_do` = '$id_acfi'";
    $rs = $cmd->query($sql);
    $lEntAF = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($lEntAF)) {
    foreach ($lEntAF as $laf) {
        $id_eaf = $laf['id_acfijo'];
        $editar = $borrar = null;
        if ($laf['estado'] < '3') {
            if ((intval($permisos['editar'])) == 1) {
                $editar = '<a value="' . $id_eaf . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
            if ((intval($permisos['borrar'])) == 1) {
                $borrar = '<a value="' . $id_eaf . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        }
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT `id_activo_fijo`, `num_serial` FROM `seg_num_serial` WHERE `id_activo_fijo` = '$id_eaf'";
            $rs = $cmd->query($sql);
            $series = $rs->fetchAll();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $seriales = '';
        $seriales = '<ul class="m-0">';
        if (!empty($series)) {
            foreach ($series as $serie) {
                $seriales .= '<li>' . $serie['num_serial'] . '</li>';
            }
        }
        $seriales .= '</ul>';
        $data[] = [
            "id_acfijo" => $id_eaf,
            "bien_servicio" => $laf['bien_servicio'],
            "mantenimiento" => $laf['mantenimiento'] == '1' ? 'SI' : 'NO',
            "depreciable" => $laf['depreciable'] == '1' ? 'SI' : 'NO',
            "marca" => $laf['marca'],
            "modelo" => $laf['modelo'],
            "val_unit" => '<div class="text-right">' . pesos($laf['val_unit']) . '</div>',
            "descripcion" => $laf['descripcion'],
            "cantidad" => $laf['cantidad'],
            "serial" => $seriales,
            "tipo_activo" => $laf['tipo_activo'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
