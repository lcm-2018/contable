<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_num_serial`.`id_serial`
                , `seg_num_serial`.`placa`
                , `seg_num_serial`.`num_serial`
                , `ctt_bien_servicio`.`bien_servicio`
            FROM
                `seg_num_serial` 
                INNER JOIN `seg_entra_detalle_activos_fijos`
                    ON(`seg_entra_detalle_activos_fijos`.`id_acfijo` = `seg_num_serial`.`id_activo_fijo`)
                INNER JOIN `ctt_bien_servicio` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_prod` = `ctt_bien_servicio`.`id_b_s`)";
    $rs = $cmd->query($sql);
    $acfijos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($acfijos)) {
    foreach ($acfijos as $af) {
        $id_serie = $af['id_serial'];
        $btnqr =  '<a value="' . $id_serie . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb codeqr" title="Generar Código QR"><span class="fas fa-qrcode fa-lg"></span></a>';
        $data[] = [
            "id_serie" => $id_serie,
            "nombre" => $af['bien_servicio'],
            "serial" => $af['num_serial'],
            "placa" => $af['placa'],
            "botones" => '<div class="text-center">' . $btnqr . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
