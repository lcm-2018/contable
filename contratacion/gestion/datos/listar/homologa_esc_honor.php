<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_tipo_bien_servicio`.`id_tipo_b_s`, `tb_tipo_compra`.`tipo_compra`, `tb_tipo_contratacion`.`tipo_contrato`, `tb_tipo_bien_servicio`.`tipo_bn_sv`
                
            FROM
                `tb_tipo_bien_servicio`
                INNER JOIN `tb_tipo_contratacion` 
                    ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
                INNER JOIN `tb_tipo_compra` 
                    ON (`tb_tipo_contratacion`.`id_tipo_compra` = `tb_tipo_compra`.`id_tipo`)
            ORDER BY `tb_tipo_compra`.`tipo_compra`,`tb_tipo_contratacion`.`tipo_contrato`, `tb_tipo_bien_servicio`.`tipo_bn_sv` ASC";
    $rs = $cmd->query($sql);
    $tipo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tabla = '';
$tabla = '<table border>
            <thead>
                <tr>
                    <th style="background-color:#7DCEA0">id_tipo_bn_ss</th>
                    <th style="background-color:#7DCEA0">tipo_compra</th>
                    <th style="background-color:#7DCEA0">tipo_contrato</th>
                    <th style="background-color:#7DCEA0">tipo_bn_sv</th>
                    <th style="background-color:#7DCEA0">codigo<br>presupuestal</th>
                    <th style="background-color:#7DCEA0">valor<br>Mensual</th>
                    <th style="background-color:#7DCEA0">valor<br>Horas</th>
                    <th style="background-color:#7DCEA0">Vigencia</th>
                </tr>
            </thead>
            <tbody>';
foreach ($tipo as $key => $value) {
    $tabla .= '<tr>
                    <td>' . $value['id_tipo_b_s'] . '</td>
                    <td>' . utf8_decode($value['tipo_compra']) . '</td>
                    <td>' . utf8_decode($value['tipo_contrato']) . '</td>
                    <td>' . utf8_decode($value['tipo_bn_sv']) . '</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>' . $_SESSION['vigencia'] . '</td>
                </tr>';
}
$tabla .= '</tbody>
        </table>';
header('Content-type:application/xls');
header('Content-Disposition: attachment; filename=homologación_honorarios_' . $_SESSION['vigencia'] . '.xls');
echo $tabla;
