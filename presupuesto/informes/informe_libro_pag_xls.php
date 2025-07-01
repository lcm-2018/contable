<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$vigencia = $_SESSION['vigencia'];
$fecha_corte = $_POST['fecha_corte'];
$fecha_ini = $_POST['fecha_ini'];

function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../terceros.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT
                ctb_doc.fecha as fecha,
                ctb_doc.id_manu as id_manu,
                tb_terceros.nom_tercero as nom_tercero,
                tb_terceros.nit_tercero as nit_tercero,
                ctb_doc.detalle as detalle,
                pto_cargue.cod_pptal as cod_pptal,
                pto_cargue.nom_rubro as nom_rubro,
                pto_pag_detalle.valor as valor
            FROM
                pto_pag_detalle
                INNER JOIN ctb_doc ON (ctb_doc.id_ctb_doc = pto_pag_detalle.id_ctb_doc)
                INNER JOIN pto_cop_detalle ON (pto_cop_detalle.id_pto_cop_det = pto_pag_detalle.id_pto_cop_det)
                INNER JOIN pto_crp_detalle ON (pto_crp_detalle.id_pto_crp_det = pto_cop_detalle.id_pto_crp_det)
                INNER JOIN pto_cdp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
                INNER JOIN pto_cargue ON pto_cdp_detalle.id_rubro = pto_cargue.id_cargue
                LEFT JOIN tb_terceros ON tb_terceros.id_tercero_api = pto_pag_detalle.id_tercero_api
               
            WHERE ctb_doc.estado = 2 AND ctb_doc.fecha BETWEEN '$fecha_ini' AND '$fecha_corte'";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$nom_informe = "RELACION DE PAGOS";
include_once '../../financiero/encabezado_empresa.php';
?>
<table class="table-hover" style="width:100% !important; border-collapse: collapse;" border="1">
    <thead>
        <tr class="centrar">
            <th>No Egreso</th>
            <th>Fecha</th>
            <th>Tercero</th>
            <th>Cc/Nit</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Nombre rubro</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($causaciones as $rp) {

            $fecha = date('Y-m-d', strtotime($rp['fecha']));
            echo "<tr>
                <td style='text-align:left'>" . $rp['id_manu'] . "</td>
                <td style='text-align:left;white-space: nowrap;'>" .   $fecha   . "</td>
                <td style='text-align:left'>" .  $rp['nom_tercero'] . "</td>
                <td style='text-align:left;white-space: nowrap;'>" . $rp['nit_tercero'] . "</td>
                <td style='text-align:left'>" . $rp['detalle'] . "</td>
                <td style='text-align:left'>" . $rp['cod_pptal'] . "</td>
                <td style='text-align:left'>" .  $rp['nom_rubro'] . "</td>
                <td style='text-align:right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
                </tr>";
        }
        ?>
    </tbody>
</table>