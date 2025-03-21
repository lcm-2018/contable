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
    $sql = "SELECT far_alm_pedido.id_pedido,far_alm_pedido.num_pedido,far_alm_pedido.fec_pedido,far_alm_pedido.hor_pedido,far_alm_pedido.detalle,far_alm_pedido.val_total,
                tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,                    
                CASE far_alm_pedido.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CONFIRMADO' 
                    WHEN 3 THEN 'ACEPTADO' WHEN 4 THEN 'FINALIZADO' WHEN 0 THEN 'ANULADO' END AS estado,
                CASE far_alm_pedido.estado WHEN 1 THEN far_alm_pedido.fec_creacion WHEN 2 THEN far_alm_pedido.fec_confirma 
                    WHEN 3 THEN far_alm_pedido.fec_acepta WHEN 4 THEN far_alm_pedido.fec_cierre WHEN 0 THEN far_alm_pedido.fec_anulacion END AS fec_estado
            FROM far_alm_pedido             
            INNER JOIN tb_sedes ON (tb_sedes.id_sede = far_alm_pedido.id_sede)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_alm_pedido.id_bodega)           
            WHERE id_pedido=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
            far_alm_pedido_detalle.cantidad,far_alm_pedido_detalle.valor,
            (far_alm_pedido_detalle.cantidad*far_alm_pedido_detalle.valor) AS val_total,
            IFNULL(INGRESO.cantidad,0) AS cantidad_in,
            IFNULL(INGRESO.valor,0) AS val_total_in
        FROM far_alm_pedido_detalle
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_alm_pedido_detalle.id_medicamento)
        LEFT JOIN (SELECT far_medicamento_lote.id_med,
                        SUM(far_orden_ingreso_detalle.cantidad*far_presentacion_comercial.cantidad) AS cantidad,
                        SUM(far_orden_ingreso_detalle.cantidad*far_orden_ingreso_detalle.valor) AS valor
                    FROM far_orden_ingreso_detalle
                    INNER JOIN far_orden_ingreso ON (far_orden_ingreso.id_ingreso=far_orden_ingreso_detalle.id_ingreso)
                    INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_orden_ingreso_detalle.id_lote)
                    INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_orden_ingreso_detalle.id_presentacion)
                    WHERE far_orden_ingreso.id_pedido=$id AND far_orden_ingreso.estado<>0
                    GROUP BY far_medicamento_lote.id_med
                ) AS INGRESO ON (INGRESO.id_med=far_alm_pedido_detalle.id_medicamento)
        WHERE far_alm_pedido_detalle.id_pedido=" . $id . " ORDER BY far_alm_pedido_detalle.id_ped_detalle";
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
            <th>ORDEN DE PEDIDO DE ALMACEN - INGRESADO</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Id. Pedido</td>
            <td>No. Pedido</td>
            <td>Fecha Pedido</td>
            <td>Hora Pedido</td>
            <td>Estado</td>
            <td>Fecha Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['id_pedido']; ?></td>
            <td><?php echo $obj_e['num_pedido']; ?></td>
            <td><?php echo $obj_e['fec_pedido']; ?></td>
            <td><?php echo $obj_e['hor_pedido']; ?></td>
            <td><?php echo $obj_e['estado']; ?></td>
            <td><?php echo $obj_e['fec_estado']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Sede</td>
            <td>Bodega</td>
            <td colspan="4">Detalle</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['nom_sede']; ?></td>
            <td><?php echo $obj_e['nom_bodega']; ?></td>
            <td colspan="4"><?php echo $obj_e['detalle']; ?></td>        
        </tr>        
    </table>

    <table style="width:100% !important">
        <thead style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Código</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Valor Promedio</th>
                <th>Valor Total</th>
                <th>Cantidad Ingresada</th>
                <th>Valor Total</th>
                <th>Cantidad Pendiente</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $val_total_in = 0;
            $tabla = '';
            foreach ($obj_ds as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['cod_medicamento'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                        <td>' . $obj['cantidad'] . '</td>
                        <td>' . formato_valor($obj['valor']) . '</td>   
                        <td>' . formato_valor($obj['val_total']) . '</td>
                        <td>' . $obj['cantidad_in'] . '</td>
                        <td>' . formato_valor($obj['val_total_in']) . '</td>                        
                        <td>' . ($obj['cantidad'] - $obj['cantidad_in']) . '</td></tr>';
                $val_total_in +=$obj['val_total_in'];
            }
            echo $tabla;
            ?>
        </tbody>
        <tfoot style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="3"></td>
                <td>TOTAL:</td>
                <td><?php echo formato_valor($obj_e['val_total']); ?> </td>
                <td></td>
                <td><?php echo formato_valor($val_total_in); ?> </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>