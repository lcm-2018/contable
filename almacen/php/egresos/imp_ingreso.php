<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;

try {
    $sql = "SELECT far_orden_ingreso.id_ingreso,far_orden_ingreso.num_ingreso,far_orden_ingreso.fec_ingreso,
            far_orden_ingreso.hor_ingreso,far_orden_ingreso.num_factura,far_orden_ingreso.fec_factura,
            far_orden_ingreso.detalle,far_orden_ingreso.val_total,
            tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,
            tb_terceros.nom_tercero,far_orden_ingreso_tipo.nom_tipo_ingreso,
            CASE far_orden_ingreso.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS estado,
            CASE far_orden_ingreso.estado WHEN 0 THEN far_orden_ingreso.fec_anulacion WHEN 1 THEN far_orden_ingreso.fec_creacion WHEN 2 THEN far_orden_ingreso.fec_cierre END AS fec_estado
        FROM far_orden_ingreso 
        INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_orden_ingreso.id_sede)
        INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_orden_ingreso.id_bodega)
        INNER JOIN tb_terceros ON (tb_terceros.id_tercero=far_orden_ingreso.id_provedor)
        INNER JOIN far_orden_ingreso_tipo ON (far_orden_ingreso_tipo.id_tipo_ingreso=far_orden_ingreso.id_tipo_ingreso)
        WHERE id_ingreso=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT INGRESO.cod_medicamento,INGRESO.nom_medicamento,INGRESO.cantidad,
                INGRESO.val_total,
                IFNULL(EGRESO.cantidad,0) AS cantidad_eg,
                EGRESO.val_total AS val_total_eg
            FROM 	
                (SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                        SUM(far_orden_ingreso_detalle.cantidad) AS cantidad,
                        SUM(far_orden_ingreso_detalle.cantidad*far_orden_ingreso_detalle.valor) AS val_total
                FROM far_orden_ingreso_detalle
                INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote = far_orden_ingreso_detalle.id_lote)
                INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_medicamento_lote.id_med)
                WHERE far_orden_ingreso_detalle.id_ingreso=$id
                GROUP BY far_medicamentos.id_med) AS INGRESO
            LEFT JOIN (SELECT far_medicamento_lote.id_med,
                    SUM(far_orden_egreso_detalle.cantidad) AS cantidad,
                    SUM(far_orden_egreso_detalle.cantidad*far_orden_egreso_detalle.valor) AS val_total 
                FROM far_orden_egreso_detalle
                INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote = far_orden_egreso_detalle.id_lote)
                INNER JOIN far_orden_egreso ON (far_orden_egreso.id_egreso=far_orden_egreso_detalle.id_egreso)
                WHERE far_orden_egreso.estado<>0 AND far_orden_egreso.id_ingreso_fz=$id
                GROUP BY far_medicamento_lote.id_med) AS EGRESO
            ON (INGRESO.id_med=EGRESO.id_med)";
        $rs = $cmd->query($sql);
    $obj_ds = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="text-right py-3">
    <a type="button" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
    <a type="button" class="btn btn-primary btn-sm" id="btnImprimir">Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="content bg-light" id="areaImprimir">
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

    <?php include('../common/reporte_header.php'); ?>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>ORDEN DE INGRESO</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Id. Ingreso</td>
            <td>No. Ingreso</td>
            <td>Fecha Ingreso</td>
            <td>Hora Ingreso</td>
            <td>Estado</td>
            <td>Fecha Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['id_ingreso']; ?></td>
            <td><?php echo $obj_e['num_ingreso']; ?></td>
            <td><?php echo $obj_e['fec_ingreso']; ?></td>
            <td><?php echo $obj_e['hor_ingreso']; ?></td>
            <td><?php echo $obj_e['estado']; ?></td>
            <td><?php echo $obj_e['fec_estado']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Sede</td>
            <td>Bodega</td>
            <td>Tipo de Ingreso</td>
            <td>No. Acta y/o Remisión</td>
            <td>Fecha Acta y/o Remisión</td>
            <td>Proveedor</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['nom_sede']; ?></td>
            <td><?php echo $obj_e['nom_bodega']; ?></td>
            <td><?php echo $obj_e['nom_tipo_ingreso']; ?></td>
            <td><?php echo $obj_e['num_factura']; ?></td>
            <td><?php echo $obj_e['fec_factura']; ?></td>
            <td><?php echo $obj_e['nom_tercero']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td colspan="6">Detalle</td>
        </tr>
        <tr>
            <td colspan="6"><?php echo $obj_e['detalle']; ?></td>
        </tr>
    </table>

    <table style="width:100% !important">
        <thead style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Código</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Valor Total</th>
                <th>Cantidad Devuelta</th>
                <th>Valor Total</th>
                <th>Cantidad Pendiente</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $val_total_eg = 0;
            $tabla = '';
            foreach ($obj_ds as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['cod_medicamento'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                        <td>' . $obj['cantidad'] . '</td>
                        <td>' . formato_valor($obj['val_total']) . '</td> 
                        <td>' . $obj['cantidad_eg'] . '</td>
                        <td>' . formato_valor($obj['val_total_eg']) . '</td>
                        <td>' . ($obj['cantidad'] - $obj['cantidad_eg']) . '</td></tr>';
                $val_total_eg +=$obj['val_total_eg'];
            }
            echo $tabla;
            ?>
        </tbody>
        <tfoot style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="2"></td>
                <td>TOTAL:</td>
                <td><?php echo formato_valor($obj_e['val_total']); ?> </td>
                <td></td>
                <td><?php echo formato_valor($val_total_eg); ?> </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</div>