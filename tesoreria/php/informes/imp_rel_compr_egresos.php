<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include 'funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$fec_ini = isset($_POST['fec_ini']) && strlen($_POST['fec_ini'] > 0) ? "'" . $_POST['fec_ini'] . "'" : '2020-01-01';
$fec_fin = isset($_POST['fec_fin']) && strlen($_POST['fec_fin']) > 0 ? "'" . $_POST['fec_fin'] . "'" : '2050-12-31';

try {
    //----- relacion de comprobantes de egreso generados -----------------------
    $sql = "SELECT
                DATE_FORMAT (ctb_doc.fecha, '%Y-%m-%d') AS fecha
                , ctb_doc.id_manu
                , tb_terceros.nit_tercero
                , tb_terceros.nom_tercero
                , ctb_doc.detalle
                , (SUM(pto_cop_detalle.valor) - SUM(pto_cop_detalle.valor_liberado)) AS causado
                , (SUM(pto_pag_detalle.valor) - SUM(pto_pag_detalle.valor_liberado)) AS pagado
                , SUM(IFNULL(ctb_causa_retencion.valor_retencion,0)) AS valor_retencion
                , ((SUM(pto_pag_detalle.valor) - SUM(pto_pag_detalle.valor_liberado)) - SUM(IFNULL(ctb_causa_retencion.valor_retencion,0))) AS neto
                , tes_cuentas.nombre AS cuenta
                , tes_detalle_pago.valor AS valor_girado
            FROM
                pto_pag_detalle 
                INNER JOIN ctb_doc ON (pto_pag_detalle.id_ctb_doc = ctb_doc.id_ctb_doc)
                INNER JOIN tb_terceros ON (pto_pag_detalle.id_tercero_api = tb_terceros.id_tercero_api)
                INNER JOIN pto_cop_detalle ON (pto_pag_detalle.id_pto_cop_det = pto_cop_detalle.id_pto_cop_det)
                LEFT JOIN tes_detalle_pago ON (tes_detalle_pago.id_ctb_doc = ctb_doc.id_ctb_doc)
                LEFT JOIN tes_cuentas ON (tes_detalle_pago.id_tes_cuenta = tes_cuentas.id_tes_cuenta)
                LEFT JOIN ctb_causa_retencion ON (pto_cop_detalle.id_ctb_doc = ctb_causa_retencion.id_ctb_doc)
                WHERE ctb_doc.fecha BETWEEN $fec_ini AND $fec_fin
                GROUP BY tb_terceros.nit_tercero
                ORDER BY ctb_doc.fecha";

    $rs = $cmd->query($sql);
    $obj_informe = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="text-right py-3">
    <a type="button" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exportar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
    <a type="button" class="btn btn-primary btn-sm" id="btnImprimir">Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="content bg-light" id="areaImprimirrr">
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
            }
        }

        .resaltar:nth-child(even) {
            background-color: #F8F9F9;
        }

        .resaltar:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
</div>
    <?php include('reporte_header.php'); ?>
<div class="content bg-light" id="areaImprimir">

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>RELACIÓN DE COMPROBANTES DE EGRESOS GENERADOS</th>
        </tr>
    </table>

    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">Fecha</th>
                <th style="border:#A9A9A9 1px solid;">Consecutivo</th>
                <th style="border:#A9A9A9 1px solid;">Nit tercero</th>
                <th style="border:#A9A9A9 1px solid;" colspan="2">Tercero</th>
                <th style="border:#A9A9A9 1px solid;" colspan="2">Detalle</th>
                <th style="border:#A9A9A9 1px solid;">Vr. Causado</th>
                <th style="border:#A9A9A9 1px solid;">Vr. Pagado</th>
                <th style="border:#A9A9A9 1px solid;">Vr. Retención</th>
                <th style="border:#A9A9A9 1px solid;">Vr. Neto</th>
                <th style="border:#A9A9A9 1px solid;">Cuenta bancaria</th>
                <th style="border:#A9A9A9 1px solid;">Vr. Girado</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <?php
            $tabla = '';
            foreach ($obj_informe as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['fecha'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['id_manu'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['nit_tercero'] . '</td>
                        <td style="border:#A9A9A9 1px solid; text-align:left;" colspan="2">' . mb_strtoupper($obj['nom_tercero']) . '</td>   
                        <td style="border:#A9A9A9 1px solid; text-align:left;" colspan="2">' . mb_strtoupper($obj['detalle']) . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . ($obj['causado']) . '</td>   
                        <td style="border:#A9A9A9 1px solid;">' . ($obj['pagado']) . '</td>   
                        <td style="border:#A9A9A9 1px solid;">' . ($obj['valor_retencion']) . '</td>   
                        <td style="border:#A9A9A9 1px solid;">' . ($obj['neto']) . '</td>   
                        <td style="border:#A9A9A9 1px solid; text-align:left;">' . mb_strtoupper($obj['cuenta']) . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . ($obj['valor_girado']) . '</td></tr>';
            }
            echo $tabla;
            ?>
        </tbody>
    </table>
</div>