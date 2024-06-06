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
$id_ser = isset($_POST['id_ser']) ? $_POST['id_ser'] : exit('Accion no permitida');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_entra_detalle_activos_fijos`.`id_acfijo`
                , `seg_entra_detalle_activos_fijos`.`id_prod`
                , `ctt_bien_servicio`.`bien_servicio`
                , `seg_entra_detalle_activos_fijos`.`mantenimiento`
                , `seg_entra_detalle_activos_fijos`.`depreciable`
                , `seg_entra_detalle_activos_fijos`.`marca`
                , `seg_entra_detalle_activos_fijos`.`modelo`
                , `seg_entra_detalle_activos_fijos`.`val_unit`
                , `seg_entra_detalle_activos_fijos`.`cantidad`
                , `seg_entra_detalle_activos_fijos`.`descripcion`
                , `seg_entra_detalle_activos_fijos`.`id_tipo_activo`
                , `seg_tipo_activo`.`descripcion` AS `tipo_activo`
                , `seg_num_serial`.`id_serial`
                , `seg_num_serial`.`placa`
                , `seg_num_serial`.`num_serial`
                , `seg_num_serial`.`tipo_entra`
                , `seg_num_serial`.`id_ser_componente`
            FROM
                `seg_num_serial` 
                INNER JOIN `seg_entra_detalle_activos_fijos`
                    ON (`seg_num_serial`.`id_activo_fijo` = `seg_entra_detalle_activos_fijos`.`id_acfijo`)
                INNER JOIN `ctt_bien_servicio` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_prod` = `ctt_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_tipo_activo` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_tipo_activo` = `seg_tipo_activo`.`id_tipo_act`)
            WHERE `seg_num_serial`.`id_ser_componente` = '$id_ser'";
    $rs = $cmd->query($sql);
    $lcomponentes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($lcomponentes)) {
    foreach ($lcomponentes as $lcp) {
        $id_acf = $lcp['id_acfijo'];
        $editar = $borrar = null;
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_acf . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar1 = '<a value="' . $id_acf . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar activo fijo"><span class="fas fa-trash-alt fa-lg"></span></a>';
            $borrar2 = '<a value="' . $lcp['id_serial'] . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb eliminar" title="Eliminar componente"><span class="fas fa-eraser fa-lg" ></span></a>';
        }
        $data[] = [
            "id_acfijo" => $id_acf,
            "bien_servicio" => $lcp['bien_servicio'],
            "mantenimiento" => $lcp['mantenimiento'] == '1' ? 'SI' : 'NO',
            "depreciable" => $lcp['depreciable'] == '1' ? 'SI' : 'NO',
            "marca" => $lcp['marca'],
            "modelo" => $lcp['modelo'],
            "val_unit" => '<div class="text-right">' . pesos($lcp['val_unit']) . '</div>',
            "descripcion" => $lcp['descripcion'],
            "cantidad" => $lcp['cantidad'],
            "serial" => $lcp['num_serial'],
            "tipo_activo" => $lcp['tipo_activo'],
            "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar1 . $borrar2 . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
