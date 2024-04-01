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
                `ctt_bien_servicio`.`id_b_s`, `tb_tipo_bien_servicio`.`tipo_bn_sv`, `ctt_bien_servicio`.`bien_servicio`
            FROM
                `ctt_bien_servicio`
                INNER JOIN `tb_tipo_bien_servicio` 
                    ON (`ctt_bien_servicio`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
            ORDER BY `tb_tipo_bien_servicio`.`tipo_bn_sv`, `ctt_bien_servicio`.`bien_servicio` ASC";
    $rs = $cmd->query($sql);
    $bien_servicio = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tabla = '';
$tabla = '<table border>
            <thead>
                <tr>
                    <th>id_b_s</th>
                    <th>tipo_bn_sv</th>
                    <th>bien_servicio</th>
                    <th>cod_unspsc</th>
                    <th>cod_cuipo</th>
                    <th>cod_siho</th>
                </tr>
            </thead>
            <tbody>';
foreach ($bien_servicio as $fila) {
    $tabla .= '<tr>
                    <td>' . $fila['id_b_s'] . '</td>
                    <td>' . utf8_decode($fila['tipo_bn_sv']) . '</td>
                    <td>' . utf8_decode($fila['bien_servicio']) . '</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';
}
$tabla .= '</tbody>
        </table>';
header('Content-type:application/xls');
header('Content-Disposition: attachment; filename=homologación.xls');
echo $tabla;
