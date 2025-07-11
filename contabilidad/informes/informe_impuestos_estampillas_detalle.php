<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

header("Content-type: application/vnd.ms-excel charset=utf-8");
header("Content-Disposition: attachment; filename=Descuentos_municipio.xls");
header("Pragma: no-cache");
header("Expires: 0");

$vigencia = $_SESSION['vigencia'];
// estraigo las variables que llegan por post en json
$fecha_inicial = $_POST['fecha_inicial'];
$fecha_corte = $_POST['fecha_final'];

function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
include '../../terceros.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto la tabla tb_terceros para obtener el id_tercero_api
try {
    $sql = "SELECT
                ctb_doc.fecha,
                ctb_doc.estado,
                ctb_doc.id_manu,
                tb_terceros.nom_tercero,
                tb_tipos_documento.descripcion,
                tb_terceros.nit_tercero,
                ctb_doc.detalle,
                ctb_causa_retencion.valor_base,
                ctb_causa_retencion.tarifa,
                ctb_causa_retencion.valor_retencion,
                ctb_retenciones.nombre_retencion
            FROM
                ctb_causa_retencion
                INNER JOIN ctb_doc ON (ctb_causa_retencion.id_ctb_doc = ctb_doc.id_ctb_doc)
                INNER JOIN ctb_retencion_rango ON (ctb_causa_retencion.id_rango = ctb_retencion_rango.id_rango)
                INNER JOIN ctb_retenciones ON (ctb_retencion_rango.id_retencion = ctb_retenciones.id_retencion)
                INNER JOIN tb_terceros ON (tb_terceros.id_tercero_api = ctb_doc.id_tercero)
                LEFT JOIN tb_tipos_documento ON (tb_terceros.tipo_doc = tb_tipos_documento.id_tipodoc)
            WHERE 
                ctb_retenciones.id_retencion_tipo = 5
                AND ctb_doc.fecha BETWEEN '$fecha_inicial' AND '$fecha_corte';";
    $res = $cmd->query($sql);
    $descuentos = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT
                `razon_social_ips`, `nit_ips`, `dv`
            FROM
                `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">
        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="7" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="7" style="text-align:center"><?php echo '<h3>' . $empresa['razon_social_ips'] . '</h3>'; ?></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:center"><?php echo $empresa['nit_ips'] . '-' . $empresa['dv']; ?></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:center"><?php echo 'RELACION DE DESCUENTOS Y RETENCIONES DETALLADO'; ?></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:center"></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>
        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td>MUNICIPIO</td>
                <td style='text-align: left;'><?php //echo $tercero; 
                                                ?></td>
            </tr>
            <tr>
                <td>NIT</td>
                <td style='text-align: left;'><?php //echo $ccnit; 
                                                ?></td>
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
                <td>Comprobante</td>
                <td>Fecha</td>
                <td>Nombre</td>
                <td>CC/Nit</td>
                <td>Detalle</td>
                <td>Base</td>
                <td>Valor</td>
            </tr>
            <?php
            $total_ret = 0;
            foreach ($descuentos as $tp) {
                $fecha = date('Y-m-d', strtotime($tp['fecha']));
                echo "<tr>
                    <td class='text-right'>" . $tp['id_manu'] . "</td>
                    <td class='text-right'>" . $fecha . "</td>
                    <td class='text-right'>" .  $tp['nom_tercero']  . "</td>
                    <td class='text'>" .  $tp['nit_tercero'] . "</td>
                    <td class='text-right'>" . $tp['detalle'] . "</td>
                    <td class='text-right'>" . number_format($tp['valor_base'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($tp['valor_retencion'], 2, ".", ",")  . "</td>
                    </tr>";
                $total_ret = $total_ret + $tp['valor_retencion'];
            }
            echo "<tr>
        <td class='text-right' colspan='6'> Total</td>
        <td class='text-right'>" . number_format($total_ret, 2, ".", ",")  . "</td>
        </tr>
        </table>
        </br> &nbsp;
        ";
            ?>
        </table>
    </div>
</div>