<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>CONTAFACIL</title>
    <style>
        .text {
            mso-number-format: "\@"
        }
    </style>

    <?php

    header("Content-type: application/vnd.ms-excel charset=utf-8");
    header("Content-Disposition: attachment; filename=Descuentos_municipio.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
// estraigo las variables que llegan por post en json
$fecha_inicial = $_POST['fec_inicial'];
$fecha_corte = $_POST['fec_final'];
$sede = $_POST['mpio'];
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto la tabla seg_terceros para obtener el id_tercero_api
try {
    $sql = "SELECT
                `seg_terceros`.`id_tercero_api`
            FROM
                `seg_terceros`
            WHERE (`seg_terceros`.`id_tercero` = $sede);";
    $res = $cmd->query($sql);
    $tercero = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
//Consulto descuentos de rete ICA
try {
    $sql = "SELECT
    `seg_ctb_retencion_tipo`.`id_retencion_tipo`
    , `seg_ctb_retencion_tipo`.`tipo`
    , `seg_ctb_retenciones`.`nombre_retencion`
    , `seg_ctb_retenciones`.`id_retencion`
    , SUM(`seg_ctb_causa_retencion`.`valor_base`) AS base
    , SUM(`seg_ctb_causa_retencion`.`valor_retencion`) AS retencion
FROM
    `seg_ctb_retenciones`
    INNER JOIN `seg_ctb_retencion_tipo` 
        ON (`seg_ctb_retenciones`.`id_retencion_tipo` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
    INNER JOIN `seg_ctb_causa_retencion` 
        ON (`seg_ctb_retenciones`.`id_retencion` = `seg_ctb_causa_retencion`.`id_retencion`)
    INNER JOIN `ctb_doc` 
        ON (`seg_ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
WHERE (`seg_ctb_retencion_tipo`.`id_retencion_tipo` =3
    AND `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte'
    AND `seg_ctb_causa_retencion`.`id_terceroapi` ={$tercero['id_tercero_api']})
GROUP BY `seg_ctb_retenciones`.`nombre_retencion`;
            ";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
//Consulto descuentos de sobretasa
try {
    $sql = "SELECT
    `seg_ctb_retencion_tipo`.`id_retencion_tipo`
    , `seg_ctb_retencion_tipo`.`tipo`
    , `seg_ctb_retenciones`.`nombre_retencion`
    , `seg_ctb_retenciones`.`id_retencion`
    , SUM(`seg_ctb_causa_retencion`.`valor_base`) AS base
    , SUM(`seg_ctb_causa_retencion`.`valor_retencion`) AS retencion
FROM
    `seg_ctb_retenciones`
    INNER JOIN `seg_ctb_retencion_tipo` 
        ON (`seg_ctb_retenciones`.`id_retencion_tipo` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
    INNER JOIN `seg_ctb_causa_retencion` 
        ON (`seg_ctb_retenciones`.`id_retencion` = `seg_ctb_causa_retencion`.`id_retencion`)
    INNER JOIN `ctb_doc` 
        ON (`seg_ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
WHERE (`seg_ctb_retencion_tipo`.`id_retencion_tipo` =4
    AND `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte'
    AND `seg_ctb_causa_retencion`.`id_terceroapi` ={$tercero['id_tercero_api']})
GROUP BY `seg_ctb_retenciones`.`nombre_retencion`;
            ";
    $res = $cmd->query($sql);
    $sobretasa = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT
    `nombre`
    , `nit`
    , `dig_ver`
FROM
    `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// buscar datos del tercero
// Consulta terceros en la api ********************************************* API
$url = $api . 'terceros/datos/res/datos/id/' . $tercero['id_tercero_api'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
$ccnit = $dat_ter[0]['cc_nit'];
?>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="6" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="6" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center"><?php echo 'RELACION DE DESCUENTOS Y RETENCIONES RESUMEN '; ?></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center"></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>
        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td>MUNICIPIO</td>
                <td style='text-align: left;'><?php echo $tercero; ?></td>
            </tr>
            <tr>
                <td>NIT</td>
                <td style='text-align: left;'><?php echo $ccnit; ?></td>
            </tr>
            <tr>
                <td>FECHA INICIO</td>
                <td style='text-align: left;'><?php echo $fecha_inicial; ?></td>
            </tr>
            <tr>
                <td>FECHA FIN</td>
                <td style='text-align: left;'><?php echo $fecha_corte; ?></td>
            </tr>
        </table>
        </br> &nbsp;
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Tipo de retenci&oacute;n</td>
                <td>Cuenta</td>
                <td>Retenci&oacute;n aplicada</td>
                <td>Base</td>
                <td>Valor retenido</td>
                <td>Valor pago</td>
            </tr>
            <?php
            $total_base =   0;
            $total_ret = 0;
            $total_pago =  0;
            foreach ($causaciones as $rp) {
                // consulta la cuenta contable en ctb_libaux cuando id_rte sea igual a id_retencion
                $sql = "SELECT `ctb_libaux`.`cuenta`  AS cuenta FROM `ctb_libaux` WHERE `ctb_libaux`.`id_rte` = {$rp['id_retencion']} LIMIT 1;";
                $res = $cmd->query($sql);
                $cta = $res->fetch();
                $cuenta = $cta['cuenta'];
                // redodear valor al mil mas cercano
                $pago = round($rp['retencion'], -3);
                echo "<tr>
                    <td class='text-right'>" . $rp['tipo'] . "</td>
                    <td class='text'>" . $cuenta . "</td>
                    <td class='text'>" . $rp['nombre_retencion'] . "</td>
                    <td class='text-right'>" . number_format($rp['base'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($rp['retencion'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($pago, 2, ".", ",")  . "</td>
                    </tr>";
                $total_base =   $total_base + $rp['base'];
                $total_ret = $total_ret + $rp['retencion'];
                $total_pago =  $total_pago + $pago;
            }
            echo "<tr>
            <td class='text-right' colspan='3'> Total</td>
            <td class='text-right'>" . number_format($total_base, 2, ".", ",")  . "</td>
            <td class='text-right'>" . number_format($total_ret, 2, ".", ",")  . "</td>
            <td class='text-right'>" . number_format($total_pago, 2, ".", ",")  . "</td>
            </tr>";

            ?>
        </table>
        &nbsp;
        &nbsp;
        &nbsp;

        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Tipo de retenci&oacute;n</td>
                <td>Cuenta</td>
                <td>Retenci&oacute;n aplicada</td>
                <td>Base</td>
                <td>Valor retenido</td>
                <td>Valor pago</td>
            </tr>
            <?php
            $total_base =   0;
            $total_ret = 0;
            $total_pago =  0;
            foreach ($sobretasa as $rp) {
                // consulta la cuenta contable en ctb_libaux cuando id_rte sea igual a id_retencion
                $sql = "SELECT `ctb_libaux`.`cuenta`  AS cuenta FROM `ctb_libaux` WHERE `ctb_libaux`.`id_rte` = {$rp['id_retencion']} LIMIT 1;";
                $res = $cmd->query($sql);
                $cta = $res->fetch();
                $cuenta = $cta['cuenta'];

                // redodear valor al mil mas cercano
                $pago = round($rp['retencion'], -3);
                echo "<tr>
                <td class='text-right'>" . $rp['tipo'] . "</td>
                <td class='text'>" . $cuenta . "</td>
                <td class='text'>" . $rp['nombre_retencion'] . "</td>
                <td class='text-right'>" . number_format($rp['base'], 2, ".", ",") . "</td>
                <td class='text-right'>" . number_format($rp['retencion'], 2, ".", ",") . "</td>
                <td class='text-right'>" . number_format($pago, 2, ".", ",")  . "</td>
                </tr>";
                $total_base =   $total_base + $rp['base'];
                $total_ret = $total_ret + $rp['retencion'];
                $total_pago =  $total_pago + $pago;
            }
            echo "<tr>
                    <td class='text-right' colspan='3'> Total</td>
                    <td class='text-right'>" . number_format($total_base, 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($total_ret, 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($total_pago, 2, ".", ",")  . "</td>
                    </tr>";
            ?>
        </table>
        </br>
        </br>
        </br>

    </div>

</div>

</html>