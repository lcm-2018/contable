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

$id_cuenta_ini = isset($_POST['id_cuenta_ini']) ? $_POST['id_cuenta_ini'] : 0;
$id_cuenta_fin = isset($_POST['id_cuenta_fin']) ? $_POST['id_cuenta_fin'] : 0;
$fec_ini = isset($_POST['fec_ini']) && strlen($_POST['fec_ini'] > 0) ? "'" . $_POST['fec_ini'] . "'" : '2020-01-01';
$fec_fin = isset($_POST['fec_fin']) && strlen($_POST['fec_fin']) > 0 ? "'" . $_POST['fec_fin'] . "'" : '2050-12-31';
$id_tipo_doc = isset($_POST['id_tipo_doc']) ? $_POST['id_tipo_doc'] : 0;
$id_tercero = isset($_POST['id_tercero']) ? $_POST['id_tercero'] : 0;


try {
    $sql = "SELECT `id_pgcp`, `cuenta`, `nombre` FROM `ctb_pgcp` WHERE `id_pgcp` IN ('$cta_inicial', '$cta_final') ORDER BY `cuenta` ASC";
    $res = $cmd->query($sql);
    $cta = $res->fetchAll();
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}
$cta_inicial = $cta[0]['cuenta'];
$cta_final = $cta[0]['cuenta'];
$where = '';
if (isset($_POST['id_tercero']) && $_POST['id_tercero'] > 0) {
    $id_tercero = $_POST['id_tercero'];
    $where .= " AND `ctb_libaux`.`id_tercero_api` = $id_tercero";
}
if (isset($_POST['tp_doc']) && $_POST['tp_doc'] > 0) {
    $id_documento = $_POST['tp_doc'];
    $where .= " AND `ctb_doc`.`id_tipo_doc` = $id_documento";
}

try {
    //----- relacion obligaciones por pagar - causacion -----------------------
    $sql = "SELECT
                `ctb_doc`.`fecha`,
                `ctb_pgcp`.`cuenta`,
                `ctb_libaux`.`id_tercero_api`,
                `ctb_libaux`.`debito`,
                `ctb_libaux`.`credito`,
                `ctb_doc`.`id_tipo_doc`,
                `ctb_fuente`.`cod` AS `cod_tipo_doc`,
                `ctb_fuente`.`nombre` AS `nom_tipo_doc`,
                `ctb_doc`.`id_manu`,
                `ctb_doc`.`detalle`,
                `tes_forma_pago`.`forma_pago`,
                `tb_terceros`.`nom_tercero`,
                `tb_terceros`.`nit_tercero`
            FROM 
                `ctb_libaux`
            INNER JOIN `ctb_doc` 
                ON `ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
            INNER JOIN `ctb_pgcp` 
                ON `ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`
            INNER JOIN `ctb_fuente` 
                ON `ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`
            LEFT JOIN `tes_detalle_pago` 
                ON `tes_detalle_pago`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`
            LEFT JOIN `tes_forma_pago` 
                ON `tes_detalle_pago`.`id_forma_pago` = `tes_forma_pago`.`id_forma_pago`
            LEFT JOIN `tb_terceros` 
                ON `tb_terceros`.`id_tercero_api` = `ctb_libaux`.`id_tercero_api`
            WHERE `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte' AND `ctb_doc`.`estado` = 2 
                AND (`ctb_pgcp`.`cuenta` LIKE '$cta_inicial%' OR `ctb_pgcp`.`cuenta` LIKE '$cta_final%')
                $where
            ORDER BY `ctb_pgcp`.`fecha`, `ctb_pgcp`.`cuenta` ASC";

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
            <th>RELACIÓN DE OBLIGACIONES POR PAGAR (CAUSACIÓN)</th>
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
                <th style="border:#A9A9A9 1px solid;">Vr. Retención</th>
                <th style="border:#A9A9A9 1px solid;">Vr. Neto</th>
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
                        <td style="border:#A9A9A9 1px solid;">' . ($obj['causacion']) . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . ($obj['retencion']) . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . ($obj['neto']) . '</td></tr>';
            }
            echo $tabla;
            ?>
        </tbody>
    </table>
</div>