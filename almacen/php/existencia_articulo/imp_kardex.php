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

$titulo = "TARJETA KARDEX A: " . date('Y-m-d');
$where = " WHERE far_kardex.id_med=" . $_POST['id_articulo'] . " AND (far_kardex.can_ingreso>0 OR far_kardex.can_egreso>0) AND far_kardex.estado=1";
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_kardex.fec_movimiento BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
    $titulo = "TARJETA KARDEX ENTRE: " . $_POST['fec_ini'] . " y " . $_POST['fec_ini'];
}

try {
    $sql = "SELECT cod_medicamento,nom_medicamento FROM far_medicamentos WHERE id_med=" . $_POST['id_articulo'] . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT id_kardex,fec_movimiento,comprobante,nom_sede,nom_bodega,lote,detalle,val_ingreso,val_promedio,can_ingreso,can_egreso,existencia
            FROM (
                SELECT far_kardex.id_kardex,far_kardex.fec_movimiento,CONCAT_WS('-','I',far_orden_ingreso.num_ingreso) AS comprobante,
                    tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_medicamento_lote.lote,far_kardex.detalle,
                    far_kardex.val_ingreso,far_kardex.val_promedio,far_kardex.can_ingreso,far_kardex.can_egreso,far_kardex.existencia
                FROM far_kardex
                INNER JOIN far_orden_ingreso ON (far_kardex.id_ingreso = far_orden_ingreso.id_ingreso)
                INNER JOIN tb_sedes ON (far_kardex.id_sede = tb_sedes.id_sede)
                INNER JOIN far_bodegas ON (far_kardex.id_bodega = far_bodegas.id_bodega)
                INNER JOIN far_medicamento_lote ON (far_kardex.id_lote= far_medicamento_lote.id_lote) $where                
                UNION ALL
                SELECT far_kardex.id_kardex,far_kardex.fec_movimiento,CONCAT_WS('-','E',far_orden_egreso.num_egreso) AS comprobante,
			        tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_medicamento_lote.lote,far_kardex.detalle,
			        far_kardex.val_ingreso,far_kardex.val_promedio,far_kardex.can_ingreso,far_kardex.can_egreso,far_kardex.existencia
                FROM far_kardex
                INNER JOIN far_orden_egreso ON (far_kardex.id_egreso = far_orden_egreso.id_egreso)
                INNER JOIN tb_sedes ON (far_kardex.id_sede = tb_sedes.id_sede)
                INNER JOIN far_bodegas ON (far_kardex.id_bodega = far_bodegas.id_bodega)
                INNER JOIN far_medicamento_lote ON (far_kardex.id_lote= far_medicamento_lote.id_lote) $where 
                UNION ALL
                SELECT far_kardex.id_kardex,far_kardex.fec_movimiento,CONCAT_WS('-','TE',far_traslado.num_traslado) AS comprobante,
			        tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_medicamento_lote.lote,far_kardex.detalle,
			        far_kardex.val_ingreso,far_kardex.val_promedio,far_kardex.can_ingreso,far_kardex.can_egreso,far_kardex.existencia
                FROM far_kardex
                INNER JOIN far_traslado ON (far_kardex.id_egreso_tra = far_traslado.id_traslado)
                INNER JOIN tb_sedes ON (far_kardex.id_sede = tb_sedes.id_sede)
                INNER JOIN far_bodegas ON (far_kardex.id_bodega = far_bodegas.id_bodega)
                INNER JOIN far_medicamento_lote ON (far_kardex.id_lote= far_medicamento_lote.id_lote) $where 
                UNION ALL
                SELECT far_kardex.id_kardex,far_kardex.fec_movimiento,CONCAT_WS('-','TI',far_traslado.num_traslado) AS comprobante,
			        tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,far_medicamento_lote.lote,far_kardex.detalle,
			        far_kardex.val_ingreso,far_kardex.val_promedio,far_kardex.can_ingreso,far_kardex.can_egreso,far_kardex.existencia
                FROM far_kardex
                INNER JOIN far_traslado ON (far_kardex.id_ingreso_tra = far_traslado.id_traslado)
                INNER JOIN tb_sedes ON (far_kardex.id_sede = tb_sedes.id_sede)
                INNER JOIN far_bodegas ON (far_kardex.id_bodega = far_bodegas.id_bodega)
                INNER JOIN far_medicamento_lote ON (far_kardex.id_lote= far_medicamento_lote.id_lote) $where
            ) AS t ORDER BY id_kardex ASC";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();

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
            <th><?php echo $titulo; ?></th>
        </tr>     
    </table> 

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="border:#A9A9A9 1px solid">
            <td>Articulo: <?php echo $obj_e['nom_medicamento'];?> - Código: <?php echo $obj_e['cod_medicamento']; ?></td>
        </tr>        
    </table>

    <table style="width:100% !important">
        <thead style="font-size:60%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Id</th>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Sede</th>
                <th>Bodega</th>
                <th>Lote</th>
                <th>Detalle</th>
                <th>Vr. Unitario</th>
                <th>Vr. Promedio</th>
                <th>Can. Ingreso</th>
                <th>Can. Egreso</th>
                <th>Existencia</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['id_kardex'] . '</td>
                        <td>' . $obj['fec_movimiento'] . '</td>
                        <td>' . $obj['comprobante'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_sede']) . '</td>   
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_bodega']) . '</td>   
                        <td>' . $obj['lote'] . '</td>   
                        <td style="text-align:left">' . $obj['detalle'] . '</td>                           
                        <td>' . formato_valor($obj['val_ingreso']) . '</td>   
                        <td>' . formato_valor($obj['val_promedio']) . '</td>   
                        <td>' . $obj['can_ingreso'] . '</td>   
                        <td>' . $obj['can_egreso'] . '</td>   
                        <td>' . $obj['existencia'] . '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="12" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>  
                </td>                
            </tr>
        </tfoot>
    </table>
</div>