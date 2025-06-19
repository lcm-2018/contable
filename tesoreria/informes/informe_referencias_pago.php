<?php
session_start();
set_time_limit(5600);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}



header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=FORMATO_201101_F07_AGR.xls");
header("Pragma: no-cache");
header("Expires: 0");

include '../../conexion.php';

$id_referencia = $_POST['referencia'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `tb_tipos_documento`.`codigo_ne`
                , `ctb_doc`.`id_manu`
                , `tt`.`valor`
            FROM
                `ctb_doc`
                INNER JOIN `tb_terceros` 
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `tb_tipos_documento` 
                    ON (`tb_terceros`.`tipo_doc` = `tb_tipos_documento`.`id_tipodoc`)
                INNER JOIN 
                (SELECT
                    SUM(`credito`) AS `valor`
                    , `id_ctb_doc`
                FROM
                    `ctb_libaux`
                GROUP BY `id_ctb_doc`) AS `tt`
                    ON (`tt`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (`ctb_doc`.`id_ref` = $id_referencia AND `ctb_doc`.`estado` = 2 AND `ctb_doc`.`id_tipo_doc` = 4)";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];

foreach ($causaciones as $ca) {
    if ($ca['id_tercero'] > 0) {
        $id_t[] = $ca['id_tercero'];
    }
}
$payload = json_encode($id_t);

//API URL
$url = $api . 'terceros/datos/res/datos/cuenta_bancaria';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);
$bancos = json_decode($result, true);
$bancos = $bancos != 0 ? $bancos : [];
echo "\xEF\xBB\xBF";
?>
<table class="table-bordered bg-light" style="width:100% !important;" border=1>
    <tr>
        <td>No. de registro</td>
        <td>Identificación</td>
        <td>Nombre tercero</td>
        <td>Tipo de identificación</td>
        <td>Producto de Destino</td>
        <td>Nombre banco</td>
        <td>Tipo de producto</td>
        <td>Detalle tipo cuenta</td>
        <td>Código del banco</td>
        <td>Valor del traslado</td>
        <td>No. Egreso</td>
    </tr>
    <?php
    $reg = 1;
    foreach ($causaciones as $c) {
        $key = array_search($c['id_tercero'], array_column($bancos, 'id_tercero'));
        $producto = $key !== false ? $bancos[$key]['num_cuenta'] : '';
        $banco = $key !== false ? $bancos[$key]['nom_banco'] : '';
        $tipo_cuenta = $key !== false ? $bancos[$key]['tipo_cuenta'] : '';
        $detalle_cuenta = $tipo_cuenta != '' ? ($tipo_cuenta == 'Ahorros' ? 'CA' : 'CC') : '';
        $cod_banco = $key !== false ? $bancos[$key]['cod_banco'] : '';
        $val = number_format($c['valor'], 2, ',', '');
        echo "<tr>
                <td class='text-left'>{$reg}</td>
                <td class='text-left'>{$c['nit_tercero']}</td>
                <td class='text-left'>{$c['nom_tercero']}</td>
                <td class='text-left'>{$c['codigo_ne']}</td>
                <td class='text-left'>{$producto}</td>
                <td class='text-left'>{$banco}</td>
                <td class='text-left'>{$tipo_cuenta}</td>
                <td class='text-left'>{$detalle_cuenta}</td>
                <td class='text-left'>{$cod_banco}</td>
                <td class='text-right'>{$val}</td>
                <td class='text-left'>{$c['id_manu']}</td>
            </tr>";
        $reg++;
    }
    ?>
</table>