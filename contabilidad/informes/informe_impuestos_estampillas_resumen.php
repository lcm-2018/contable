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
$id_des = $_POST['xtercero'];
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
    tb_tipos_documento.id_tipodoc,
    tb_tipos_documento.descripcion,
    ctb_retenciones.nombre_retencion as nombre,
    SUM(ctb_causa_retencion.valor_base) AS total_valor_base,
    SUM(ctb_causa_retencion.valor_retencion) AS total_valor_retencion
FROM
    ctb_causa_retencion
    INNER JOIN ctb_doc ON (ctb_causa_retencion.id_ctb_doc = ctb_doc.id_ctb_doc)
    INNER JOIN ctb_retencion_rango ON (ctb_causa_retencion.id_rango = ctb_retencion_rango.id_rango)
    INNER JOIN ctb_retenciones ON (ctb_retencion_rango.id_retencion = ctb_retenciones.id_retencion)
    INNER JOIN tb_terceros ON (tb_terceros.id_tercero_api = ctb_doc.id_tercero)
    INNER JOIN tb_tipos_documento ON (tb_terceros.tipo_doc = tb_tipos_documento.id_tipodoc)
WHERE 
    ctb_retenciones.id_retencion_tipo =5
    AND ctb_doc.fecha BETWEEN '$fecha_inicial' AND '$fecha_corte'
GROUP BY 
        ctb_retenciones.id_retencion
ORDER BY 
        ctb_retenciones.nombre_retencion";
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
                <td colspan="4" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="4" style="text-align:center"><?php echo $empresa['razon_social_ips']; ?></td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:center"><?php echo $empresa['nit_ips'] . '-' . $empresa['dv']; ?></td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:center"><?php echo 'RELACION DE DESCUENTOS Y RETENCIONES ESTAMPILLAS'; ?></td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:center"></td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:center"><?php echo ''; ?></td>
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
                <td>Tipo de retenci&oacute;n</td>
                <td>Retenci&oacute;n aplicada</td>
                <td>Valor retenido</td>
            </tr>
            <?php
            $total_base =   0;
            $total_ret = 0;
            $total_pago =  0;
            foreach ($causaciones as $rp) {
                echo "<tr>
                    <td class='text-left'>" . $rp['nombre'] . "</td>
                    <td class='text'>" . number_format($rp['total_valor_base'], 2, ".", ",") . "</td>
                    <td class='text-right'>" . number_format($rp['total_valor_retencion'], 2, ".", ",")  . "</td>
                    </tr>";
                $total_ret = $total_ret + $rp['total_valor_retencion'];
            }
            echo "<tr>
            <td class='text-right' colspan='2'> Total</td>
            <td class='text-right'>" . number_format($total_ret, 2, ".", ",")  . "</td>
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