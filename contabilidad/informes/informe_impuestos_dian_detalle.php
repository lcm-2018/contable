<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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
$fecha_inicial = $_POST['fecha_inicial'];
$fecha_corte = $_POST['fecha_final'];
$sede = $_POST['xtercero'];
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
include '../../terceros.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//Consulto descuentos de retefuente
try {
    $sql = "SELECT
    ctb_doc.fecha as fecha,
    ctb_doc.id_manu as documento,
    tb_terceros.nom_tercero as tercero,
    tb_tipos_documento.descripcion as tipo_tercero,
    tb_terceros.nit_tercero as nit,
    ctb_doc.detalle as detalle,
    ctb_causa_retencion.valor_base as base,
    ctb_causa_retencion.tarifa as tarifa,
    ctb_causa_retencion.valor_retencion as valor_retencion,
    ctb_retenciones.nombre_retencion as nombre_retencion
FROM
    ctb_causa_retencion
    INNER JOIN ctb_doc ON (ctb_causa_retencion.id_ctb_doc = ctb_doc.id_ctb_doc)
    INNER JOIN ctb_retencion_rango ON (ctb_causa_retencion.id_rango = ctb_retencion_rango.id_rango)
    INNER JOIN ctb_retenciones ON (ctb_retencion_rango.id_retencion = ctb_retenciones.id_retencion)
    INNER JOIN tb_terceros ON (tb_terceros.id_tercero_api = ctb_doc.id_tercero)
    LEFT JOIN tb_tipos_documento ON (tb_terceros.tipo_doc = tb_tipos_documento.id_tipodoc)
WHERE 
    ctb_retenciones.id_retencion_tipo IN (1,2)
    AND ctb_doc.fecha BETWEEN '$fecha_inicial' AND '$fecha_corte' AND ctb_doc.estado =2";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT razon_social_ips, nit_ips,dv
FROM
    `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// buscar datos del tercero
?>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="5" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="5" style="text-align:center"><?php echo $empresa['razon_social_ips']; ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:center"><?php echo $empresa['nit_ips'] . '-' . $empresa['dv']; ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:center"><?php echo 'RELACION DE DESCUENTOS Y RETENCIONES DETALLE DIAN'; ?></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:center"></td>
            </tr>
            <tr>
                <td colspan="5" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>
        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
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
                <td>Fecha</td>
                <td>Documento</td>
                <td>Tipo tercero</td>
                <td>Tercero</td>
                <td>CC/nit</td>
                <td>Detalle</td>
                <td>Base</td>
                <td>Tarifa</td>
                <td>Valor retencion</td>
                <td>Valor a pagar</td>
                <td>Nombre retencion</td>
            </tr>
            <?php
            $total_base =   0;
            $total_ret = 0;
            $total_pago =  0;
            foreach ($causaciones as $rp) {
                // redodear valor al mil mas cercano
                $pago = round($rp['valor_retencion'], -3);
                echo "<tr>
                    <td class='text-right'>" . $rp['fecha'] . "</td>
                    <td class='text'>" . $rp['documento'] . "</td>
                    <td class='text'>" . $rp['tipo_tercero'] . "</td>
                    <td class='text'>" . $rp['tercero'] . "</td>
                    <td class='text'>" . $rp['nit'] . "</td>
                    <td class='text'>" . $rp['detalle'] . "</td>
                    <td class='text-right'>" . number_format($rp['base'], 2, ".", ",")  . "</td>
                    <td class='text'>" . $rp['tarifa'] . "</td>
                    <td class='text-right'>" . number_format($rp['valor_retencion'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($pago, 2, ".", ",")  . "</td>
                     <td class='text'>" . $rp['nombre_retencion'] . "</td>
                    </tr>";
                $total_base =   $total_base + $rp['base'];
                $total_ret = $total_ret + $rp['valor_retencion'];
                $total_pago =  $total_pago + $pago;
            }
            echo "<tr>
            <td class='text-right' colspan='2'> Total</td>
            <td class='text-right'>" . number_format($total_base, 2, ".", ",")  . "</td>
            <td class='text-right'>" . number_format($total_ret, 2, ".", ",")  . "</td>
            <td class='text-right'>" . number_format($total_pago, 2, ".", ",")  . "</td>
            </tr>";

            ?>
        </table>
        &nbsp;
        &nbsp;
        &nbsp;

        </br>
        </br>
        </br>

    </div>

</div>

</html>