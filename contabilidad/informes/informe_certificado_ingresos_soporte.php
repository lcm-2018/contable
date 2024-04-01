<?php
session_start();
set_time_limit(3600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php';
$id_tercero = $_POST['id_tercero'];
$fecha_ini  = $_POST['fecha_i'];
$fecha_fin  = $_POST['fecha_f'];
$campos = '';

if ($_POST['cert_ret'] > 0) {
    if (strlen($campos) == 0) {
        $campos .= '1';
    } else {
        $campos .= ',1';
    }
}
if ($_POST['cert_ica'] > 0) {
    if (strlen($campos) == 0) {
        $campos .= '3,4';
    } else {
        $campos .= ',3,4';
    }
}
if ($_POST['cert_estap'] > 0) {
    if (strlen($campos) == 0) {
        $campos .= '5';
    } else {
        $campos .= ',5';
    }
}
if ($_POST['cert_otros'] > 0) {
    if (strlen($campos) == 0) {
        $campos .= '6';
    } else {
        $campos .= ',6';
    }
}
if ($_POST['cert_iva'] > 0) {
    if (strlen($campos) == 0) {
        $campos .= '2';
    } else {
        $campos .= ',2';
    }
}



function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
/*
try {
    $sql = "SELECT detalle,fecha,id_manu,id_tercero,fec_reg,tipo_doc FROM ctb_doc WHERE id_ctb_doc =$dto";
    $res = $cmd->query($sql);
    $cdp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
*/
$ccnit = $_POST['id_tercero'];
$prefijo = '';
$url = $api . 'terceros/datos/res/datos/id/' . $ccnit;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
$num_doc = $dat_ter[0]['cc_nit'];
// consulta para motrar cuadro de retenciones
try {
    $sql = "SELECT
                SUM(`seg_ctb_causa_retencion`.`valor_base`) as total_base
                , `seg_ctb_causa_retencion`.`tarifa`
                , SUM(`seg_ctb_causa_retencion`.`valor_retencion`) as total_retencion
                , `seg_ctb_causa_retencion`.`id_terceroapi`
                , `ctb_doc`.`tipo_doc`
                , `seg_ctb_retenciones`.`nombre_retencion`
                , `seg_ctb_retencion_tipo`.`tipo`
                , `seg_ctb_retencion_tipo`.`id_retencion_tipo`
            FROM
                `seg_ctb_causa_retencion`
                INNER JOIN `ctb_doc` 
                    ON (`seg_ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `seg_ctb_retenciones` 
                    ON (`seg_ctb_causa_retencion`.`id_retencion` = `seg_ctb_retenciones`.`id_retencion`)
                INNER JOIN `seg_ctb_retencion_tipo` 
                    ON (`seg_ctb_retenciones`.`id_retencion_tipo` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
            WHERE `ctb_doc`.`id_tercero` =$id_tercero AND  `ctb_doc`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_fin' AND `ctb_doc`.`tipo_doc` ='NCXP'  AND `seg_ctb_retencion_tipo`.`id_retencion_tipo` IN ($campos)
            GROUP BY `seg_ctb_causa_retencion`.`tarifa`, `seg_ctb_causa_retencion`.`id_terceroapi`;";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla tb_datos_ips

try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
    `fin_respon_doc`.`cargo`
    , `fin_respon_doc`.`tipo_control`
    FROM
    `fin_respon_doc`
    INNER JOIN `fin_maestro_doc` 
        ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
    WHERE (`fin_maestro_doc`.`tipo_doc` ='CIR'
    AND `fin_respon_doc`.`estado` =1)
    ORDER BY `fin_respon_doc`.`tipo_control` ASC;";
    $res = $cmd->query($sql);
    $firmas = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>

<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecDoc('areaImprimir',<?php echo 0/*$dto*/; ?>);"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-center' style="width:18%"><label class="small"><img src="../../images/logos/logo.png" width="100"></label></td>
                <td style="text-align:center">
                    <strong><?php echo $empresa['nombre']; ?> </strong>
                    <div>NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></div>
                </td>
            </tr>
        </table>

        </br>


        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col lead "><label><strong><?php echo 'Certificado de ingresos y retenciones'; ?></strong></label></div>'
            </div>

            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Datos generales: </strong></div>
                    </div>
                </div>
            </div>
            <table class="table-bordered bg-light" style="width:100% !important;">
                <tr>
                    <td class='text-left' style="width:18%">FECHA:</td>
                    <td class='text-left'><?php echo 'Desde ' . $_POST['fecha_i'] . ' hasta ' . $_POST['fecha_f']; ?></td>
                </tr>
                <tr>
                    <td class='text-left' style="width:18%">TERCERO:</td>
                    <td class='text-left'><?php echo $tercero; ?></td>
                </tr>
                <tr>
                    <td class='text-left' style="width:18%">CC/NIT:</td>
                    <td class='text-left'><?php echo $num_doc; ?></td>
                </tr>
            </table>
            </br>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Ingresos y retenciones: </strong></div>
                    </div>
                </div>
            </div>


            <table class="table-bordered bg-light" style="width:100% !important;border-collapse: collapse;">
                <tr>
                    <td style="text-align: left;border: 1px solid black">A quien se consigna</td>
                    <td style="border: 1px solid black">Tipo</td>
                    <td style='border: 1px solid black'>Retención</td>
                    <td style='border: 1px solid black'>Valor base</td>
                    <td style='border: 1px solid black'>Valor rete</td>
                </tr>
                <?php
                $total_rete = 0;
                foreach ($retenciones as $re) {
                    // Consulto el valor del tercero de la api
                    // Consulta terceros en la api ********************************************* API
                    $url = $api . 'terceros/datos/res/datos/id/' . $re['id_terceroapi'];
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $res_api = curl_exec($ch);
                    curl_close($ch);
                    $dat_ter = json_decode($res_api, true);
                    $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
                    // fin api terceros **************************
                    if ($re['id_retencion_tipo'] == 6) {
                        $tercero = 'OTRAS RETENCIONES';
                    }
                    echo "<tr>
                <td style='text-align: left;border: 1px solid black'>" . $tercero . "</td>
                <td style='text-align: left;border: 1px solid black'>" . $re['tipo'] . "</td>
                <td style='text-align: left;border: 1px solid black'>" . $re['nombre_retencion'] . "</td>
                <td style='text-align: right;border: 1px solid black'>" . number_format($re['total_base'], 2, ',', '.') . "</td>
                <td style='text-align: right;border: 1px solid black'>" . number_format($re['total_retencion'], 2, ',', '.') . "</td>
                </tr>";
                    $total_rete += $re['total_retencion'];
                }
                ?>
                <tr>
                    <td colspan="4" style="text-align:left;border: 1px solid black ">Total</td>
                    <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_rete, 2, ",", "."); ?></td>
                </tr>

            </table>

            </br>

            </br>
            </br>

            </br>

            <table class="table-bordered bg-light firmas" style="width:100% !important;" rowspan="8">
                <tr>
                    <?php foreach ($firmas as $mv) {
                        echo '
                    <td style="text-align: center;height: 70px;">
                        <div>__________________________</div>
                        <div>' . $mv['cargo'] . '</div>
                    </td>';
                    }
                    ?>
                </tr>
            </table>
            </br> </br> </br></br> </br> </br>
        </div>

    </div>