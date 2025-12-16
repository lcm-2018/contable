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
    $sql = "SELECT far_pedido.id_pedido,far_pedido.num_pedido,far_pedido.fec_pedido,far_pedido.hor_pedido,far_pedido.detalle,far_pedido.val_total,
            ss.nom_sede AS nom_sede_solicita,bs.nombre AS nom_bodega_solicita,                    
            CASE far_pedido.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CONFIRMADO' WHEN 3 THEN 'FINALIZADO' END AS estado,
            CASE far_pedido.estado WHEN 0 THEN far_pedido.fec_anulacion WHEN 1 THEN far_pedido.fec_creacion ELSE far_pedido.fec_cierre END AS fec_estado
        FROM far_pedido             
        INNER JOIN tb_sedes AS ss ON (ss.id_sede = far_pedido.id_sede_destino)
        INNER JOIN far_bodegas AS bs ON (bs.id_bodega = far_pedido.id_bodega_destino)           
        WHERE id_pedido=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
            far_pedido_detalle.cantidad,far_pedido_detalle.valor,
            (far_pedido_detalle.cantidad*far_pedido_detalle.valor) AS val_total,
            IFNULL(TRASLADO.cantidad,0) AS cantidad_tr,
            IFNULL(TRASLADO.valor,0) AS val_total_tr
        FROM far_pedido_detalle
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_pedido_detalle.id_medicamento)
        LEFT JOIN (SELECT TRD.id_ped_detalle,SUM(TRD.cantidad) AS cantidad,
                        SUM(TRD.cantidad*TRD.valor) AS valor     
                    FROM far_traslado_r_detalle AS TRD
                    INNER JOIN far_traslado_r AS TR ON (TR.id_traslado=TRD.id_traslado)
                    WHERE TR.estado<>0 AND TRD.id_ped_detalle IS NOT NULL
                    GROUP BY TRD.id_ped_detalle
                   ) AS TRASLADO ON (TRASLADO.id_ped_detalle=far_pedido_detalle.id_ped_detalle) 
        WHERE far_pedido_detalle.id_pedido=" . $id . " ORDER BY far_pedido_detalle.id_ped_detalle";
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
            <th>ORDEN DE PEDIDO DE SEDE-BODEGA PARA TRASLADADO</th>
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
            <td colspan="6">Sede y Bodega DE donde se solicita (Destinatario del Traslado)</td>
        </tr>
        <tr>
            <td colspan="2"><?php echo $obj_e['nom_sede_solicita']; ?></td>
            <td><?php echo $obj_e['nom_bodega_solicita']; ?></td>
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
                <th>Cantidad Solicitada</th>                
                <th>Valor Promedio</th>
                <th>Valor Total</th>
                <th>Cantidad Entregada</th>
                <th>Valor Total</th>
                <th>Cantidad Pendiente</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $val_total_tr = 0;
            $tabla = '';
            foreach ($obj_ds as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['cod_medicamento'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                        <td>' . $obj['cantidad'] . '</td>
                        <td>' . formato_valor($obj['valor']) . '</td>   
                        <td>' . formato_valor($obj['val_total']) . '</td>
                        <td>' . $obj['cantidad_tr'] . '</td>
                        <td>' . formato_valor($obj['val_total_tr']) . '</td>
                        <td>' . ($obj['cantidad'] - $obj['cantidad_tr']) . '</td></tr>';
                $val_total_tr +=$obj['val_total_tr'];
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
                <td><?php echo formato_valor($val_total_tr); ?> </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    
</div>