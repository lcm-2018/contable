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

$fec_ini = isset($_POST['fec_ini']) && strlen($_POST['fec_ini'] > 0) ? $_POST['fec_ini'] : '2020-01-01';
$fec_fin = isset($_POST['fec_fin']) && strlen($_POST['fec_fin']) > 0 ? $_POST['fec_fin'] : '2050-12-31';

try {
    //----- cuentas por pagar ft004 -----------------------
    $sql = "SELECT id_tercero_api
                    ,nit_tercero
                    ,nom_tercero
                    ,IFNULL(SUM(sumacredito),0) AS sumacredito
                    ,IFNULL(SUM(sumadebito),0) AS sumadebito
                    ,IFNULL(SUM(saldo),0) AS saldo
                    ,IFNULL(SUM(menos30),0) AS menos30
                    ,IFNULL(SUM(de30a60),0) AS de30a60
                    ,IFNULL(SUM(de60a90),0) AS de60a90
                    ,IFNULL(SUM(de90a180),0) AS de90a180
                    ,IFNULL(SUM(de180a360),0) AS de180a360
                    ,IFNULL(SUM(mas360),0) AS mas360
                    FROM (
                        SELECT 
                            c.id_ctb_doc AS documento_credito,
                            d.id_ctb_doc_debito AS documento_debito,
                            c.id_tercero_api,
                            c.nit_tercero,
                            c.nom_tercero,
                            c.fecha AS fecha_credito,
                            d.fecha AS fecha_debito,
                            c.sumacredito,
                            COALESCE(d.sumadebito, 0) AS sumadebito,
                            (c.sumacredito - COALESCE(d.sumadebito, 0)) AS saldo,
                            DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) AS antiguedad,
                            CASE 
                                WHEN DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 30 
                                THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                                END AS menos30,
                            CASE 
                                WHEN (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 30) 
                                AND (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 60) 
                                THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                            END AS de30a60,
                            CASE 
                                WHEN (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 60) 
                                AND (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 90) 
                                THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                            END AS de60a90,
                            CASE 
                                WHEN (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 90) 
                                AND (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 180) 
                                THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                            END AS de90a180,
                            CASE 
                                WHEN (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 180) 
                                AND (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) <= 360)
                                THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                            END AS de180a360,
                            CASE 
                                WHEN (DATEDIFF(DATE_FORMAT('$fec_fin', '%Y-%m-%d'),(DATE_FORMAT(c.fecha, '%Y-%m-%d'))) > 360) 
                                THEN (c.sumacredito - COALESCE(d.sumadebito, 0))
                            END AS mas360
                        FROM 
                            (-- Consulta de Crédito (tipo_doc = 3)
                            SELECT
                                ctb_libaux.id_ctb_doc,
                                tb_terceros.id_tercero_api,
                                tb_terceros.nit_tercero,
                                tb_terceros.nom_tercero,
                                DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha,
                                SUM(ctb_libaux.credito) AS sumacredito
                            FROM
                                ctb_libaux
                                INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                                INNER JOIN tb_terceros ON (ctb_libaux.id_tercero_api = tb_terceros.id_tercero_api)
                            WHERE ctb_doc.id_tipo_doc = 3
                                AND DATE_FORMAT(ctb_libaux.fecha_reg, '%Y-%m-%d') <= '$fec_fin'
                            GROUP BY 
                                ctb_libaux.id_ctb_doc, tb_terceros.id_tercero_api
                            ) c
                        LEFT JOIN 
                            (-- Consulta de Débito (tipo_doc = 4)
                            SELECT 
                                ctb_doc.id_ctb_doc_tipo3 AS id_ctb_doc_credito,
                                ctb_libaux.id_ctb_doc AS id_ctb_doc_debito,
                                tb_terceros.id_tercero_api,
                                DATE_FORMAT(ctb_libaux.fecha_reg, '%Y-%m-%d') AS fecha,
                                SUM(ctb_libaux.debito) AS sumadebito
                            FROM 
                                ctb_libaux
                                LEFT JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
                                INNER JOIN tb_terceros ON (ctb_libaux.id_tercero_api = tb_terceros.id_tercero_api)
                            WHERE ctb_doc.id_tipo_doc = 4
                                AND ctb_doc.id_ctb_doc_tipo3 IS NOT NULL
                                AND DATE_FORMAT(ctb_libaux.fecha_reg, '%Y-%m-%d') <= '$fec_fin'
                            GROUP BY 
                                ctb_doc.id_ctb_doc_tipo3
                            ) d ON c.id_ctb_doc = d.id_ctb_doc_credito AND c.id_tercero_api = d.id_tercero_api
                        WHERE c.sumacredito - COALESCE(d.sumadebito, 0) > 0
                        AND c.id_tercero_api <> 3612 -- la Dian
                        AND c.id_tercero_api <> 3619 -- estampillas
                        ORDER BY 
                        c.nom_tercero ) AS t
                GROUP BY id_tercero_api
                ORDER BY nom_tercero";

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
            <th>CUENTAS POR PAGAR FT004</th>
        </tr>
    </table>

    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;" colspan="3">&nbsp;</th>
                <th style="border:#A9A9A9 1px solid;" colspan="7">Antigüedad (dias)</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">Documento/Nit</th>
                <th style="border:#A9A9A9 1px solid;">Tercero</th>
                <th style="border:#A9A9A9 1px solid;">Credito</th>
                <th style="border:#A9A9A9 1px solid;">< 30</th>
                <th style="border:#A9A9A9 1px solid;">30 a 60</th>
                <th style="border:#A9A9A9 1px solid;">60 a 90</th>
                <th style="border:#A9A9A9 1px solid;">90 a 180</th>
                <th style="border:#A9A9A9 1px solid;">180 a 360</th>
                <th style="border:#A9A9A9 1px solid;">> 360</th>
                <th style="border:#A9A9A9 1px solid;">Saldo</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <?php
            foreach ($obj_informe as $obj) { ?>
                <tr class="resaltar"> 
                    <td style="border:#A9A9A9 1px solid;"> <?php echo $obj['nit_tercero'] ?> </td>
                    <td style="border:#A9A9A9 1px solid; text-align:left;"> <?php echo mb_strtoupper($obj['nom_tercero']) ?> </td>
                    <td style="border:#A9A9A9 1px solid; text-align:right;"> <?php echo $obj['sumacredito'] ?></td>
                    <td style="border:#A9A9A9 1px solid; text-align:right;"> <?php echo $obj['menos30'] ?></td>
                    <td style="border:#A9A9A9 1px solid; text-align:right;"> <?php echo $obj['de30a60'] ?></td>
                    <td style="border:#A9A9A9 1px solid; text-align:right;"> <?php echo $obj['de60a90'] ?></td>
                    <td style="border:#A9A9A9 1px solid; text-align:right;"> <?php echo $obj['de90a180'] ?></td>
                    <td style="border:#A9A9A9 1px solid; text-align:right;"> <?php echo $obj['de180a360'] ?></td>
                    <td style="border:#A9A9A9 1px solid; text-align:right;"> <?php echo $obj['mas360'] ?></td>
                    <td style="border:#A9A9A9 1px solid; text-align:right;"> <?php echo $obj['saldo'] ?></td>
                </tr>
            <?php }
            ?>
        </tbody>
    </table>
</div>