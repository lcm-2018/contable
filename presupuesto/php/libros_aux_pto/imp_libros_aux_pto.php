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

$id_cargue = isset($_POST['id_cargue']) ? $_POST['id_cargue'] : 0;
$doc_fuente = isset($_POST['doc_fuente']) ? $_POST['doc_fuente'] : 0;
$fec_ini = isset($_POST['fec_ini']) && strlen($_POST['fec_ini'] > 0) ? "'" . $_POST['fec_ini'] . "'" : '2020-01-01';
$fec_fin = isset($_POST['fec_fin']) && strlen($_POST['fec_fin']) > 0 ? "'" . $_POST['fec_fin'] . "'" : '2050-12-31';

try {
    //-----libros auxiliares de presupuesto -----------------------
    $sql = "SELECT
                pto_cargue.valor_aprobado,
                DATE_FORMAT(pto_cdp.fecha, '%Y-%m-%d') AS fecha,
                pto_cdp.id_manu,
                pto_cdp.objeto,    
                pto_cdp_detalle.valor_liberado as valor_deb,
                pto_cdp_detalle.valor as valor_cred,
                'CDP' AS tipo_registro,
                pto_cargue.cod_pptal,
                pto_cargue.nom_rubro,
                COUNT(*) OVER() AS filas
            FROM
                pto_cargue
                INNER JOIN pto_cdp_detalle ON (pto_cdp_detalle.id_rubro = pto_cargue.id_cargue)
                INNER JOIN pto_cdp ON (pto_cdp_detalle.id_pto_cdp = pto_cdp.id_pto_cdp)
            WHERE pto_cargue.id_cargue = $id_cargue
            AND pto_cdp.fecha BETWEEN $fec_ini AND $fec_fin
            AND pto_cdp.estado=2

            UNION ALL

            SELECT
                pto_cargue.valor_aprobado,
                IFNULL(DATE_FORMAT(pto_mod.fecha, '%Y-%m-%d'), '0') AS fecha,
                IFNULL(pto_mod.id_manu, 0) AS id_manu,
                IFNULL(pto_mod.objeto, '0') AS objeto,    
                IFNULL(pto_mod_detalle.valor_deb, 0) AS valor_deb,
                IFNULL(pto_mod_detalle.valor_cred, 0) AS valor_cred,
                'MOD' AS tipo_registro,
                pto_cargue.cod_pptal,
                pto_cargue.nom_rubro,
                COUNT(*) OVER() AS filas
            FROM
                pto_cargue
                INNER JOIN pto_mod_detalle ON (pto_mod_detalle.id_cargue = pto_cargue.id_cargue)
                INNER JOIN pto_mod ON (pto_mod_detalle.id_pto_mod = pto_mod.id_pto_mod)
            WHERE pto_cargue.id_cargue = $id_cargue
            AND pto_mod.fecha BETWEEN $fec_ini AND $fec_fin
            AND pto_mod.estado=2

ORDER BY fecha";

    $rs = $cmd->query($sql);
    $obj_informe = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$saldo_inicial = 0;
$total_deb = 0;
$total_cre = 0;

if (!empty($obj_informe) && $obj_informe[0]['filas'] > 0) {
    $saldo_inicial = $obj_informe[0]['valor_aprobado'];
} else {
    $saldo_inicial = 0;
    $total_deb = 0;
    $total_cre = 0;
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
            <label>LIBROS AUXILIARES DE PRESUPUESTO</label>
        </tr>
        <br>
        <tr style="text-align: center;">
            <label>Tipo de documento: <?php
                                        if(!empty($obj_informe)){ 
                                            echo $obj_informe[0]['cod_pptal'] . " - " . $obj_informe[0]['nom_rubro'];
                                        }
                                      ?></label>
        </tr>
    </table>
    

    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">Fecha</th>
                <th style="border:#A9A9A9 1px solid;">Documento</th>
                <th style="border:#A9A9A9 1px solid;">Tipo Reg</th>
                <th style="border:#A9A9A9 1px solid;" colspan="2">Concepto / Detalle</th>
                <th style="border:#A9A9A9 1px solid;">Debito</th>
                <th style="border:#A9A9A9 1px solid;">Credito</th>
                <th style="border:#A9A9A9 1px solid;">Saldo</th>
            </tr>
        </thead>

        <tbody style="font-size: 70%;">
            <tr>
                <td class='text-right' colspan='7'>Saldo inicial: </td>
                <!--<td class='text-right'><?php echo number_format($saldo_inicial, 2, ".", ",") ?> </td>-->
                <td class='text-right'><?php echo $saldo_inicial ?> </td>
            </tr>

            <?php
            foreach ($obj_informe as $obj) {
                $saldo_inicial = $saldo_inicial + $obj['valor_deb'] - $obj['valor_cred'];

            ?>
                <tr class="resaltar">
                    <td style="border:#A9A9A9 1px solid;"><?php echo $obj['fecha'] ?></td>
                    <td style="border:#A9A9A9 1px solid;"><?php echo $obj['id_manu'] ?></td>
                    <td style="border:#A9A9A9 1px solid;"><?php echo $obj['tipo_registro'] ?></td>
                    <td style="border:#A9A9A9 1px solid;" colspan="2"><?php echo $obj['objeto'] ?></td>
                    <td style="border:#A9A9A9 1px solid;" class="text-right"><?php echo $obj['valor_deb'] ?></td>
                    <td style="border:#A9A9A9 1px solid;" class="text-right"><?php echo $obj['valor_cred'] ?></td>
                    <td style="border:#A9A9A9 1px solid;" class="text-right"><?php echo $saldo_inicial ?></td>
                </tr>
                <?php 
                $total_deb += $obj['valor_deb'];
                $total_cre += $obj['valor_cred'];
            }
            ?>
            <tr>
                <td class='text-right' colspan='5'>Total</td>
                <!--<td class='text-right'>Debito: number_format($total_deb, 2, ".", ",") </td>
                <td class='text-right'>Credito: number_format($total_cre, 2, ".", ",") </td>
                <td class='text-right'>Saldo: number_format($saldo_inicial, 2, ".", ",") </td>-->
                <td class='text-right'>Debito: <?php echo $total_deb ?> </td>
                <td class='text-right'>Credito: <?php echo $total_cre ?> </td>
                <td class='text-right'>Saldo: <?php echo $saldo_inicial ?> </td>
            </tr>
        </tbody>
    </table>
</div>