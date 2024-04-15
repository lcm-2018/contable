<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}

include '../../../conexion.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$where = "WHERE far_orden_ingreso.id_ingreso<>0";
if (isset($_POST['id_ing']) && $_POST['id_ing']) {
    $where .= " AND far_orden_ingreso.id_ingreso='" . $_POST['id_ing'] . "'";
}
if (isset($_POST['num_ing']) && $_POST['num_ing']) {
    $where .= " AND far_orden_ingreso.num_ingreso='" . $_POST['num_ing'] . "'";
}
if (isset($_POST['num_fac']) && $_POST['num_fac']) {
    $where .= " AND far_orden_ingreso.num_factura LIKE '" . $_POST['num_fac'] . "%'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_orden_ingreso.fec_ingreso BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_tercero']) && $_POST['id_tercero']) {
    $where .= " AND far_orden_ingreso.id_provedor=" . $_POST['id_tercero'] . "";
}
if (isset($_POST['id_tiping']) && $_POST['id_tiping']) {
    $where .= " AND far_orden_ingreso.id_tipo_ingreso=" . $_POST['id_tiping'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_orden_ingreso.estado=" . $_POST['estado'];
}

try {
    $sql = "SELECT far_orden_ingreso.id_ingreso,far_orden_ingreso.num_ingreso,far_orden_ingreso.fec_ingreso,far_orden_ingreso.hor_ingreso,
                far_orden_ingreso.num_factura,far_orden_ingreso.fec_factura,far_orden_ingreso.detalle,tb_terceros.nom_tercero,
                far_orden_ingreso_tipo.nom_tipo_ingreso,far_orden_ingreso.val_total,tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,
                CASE far_orden_ingreso.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado
                FROM far_orden_ingreso
                INNER JOIN far_orden_ingreso_tipo ON (far_orden_ingreso_tipo.id_tipo_ingreso=far_orden_ingreso.id_tipo_ingreso)
                INNER JOIN tb_terceros ON (tb_terceros.id_tercero=far_orden_ingreso.id_provedor)
                INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_orden_ingreso.id_sede)
                INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_orden_ingreso.id_bodega)$where ORDER BY far_orden_ingreso.id_ingreso DESC";
    $res = $cmd->query($sql);
    $objs = $res->fetchAll();
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
    
    <table style="width:100%; font-size:80%">
        <tr style="text-align:center">
            <th>REPORTE DE ORDENES DE INGRESO ENTRE: <?php echo $_POST['fec_ini'].' y '. $_POST['fec_fin'] ?></th>
        </tr>     
    </table>

    <table style="width:100% !important; border-collapse: collapse;">
        <thead style="background-color: white !important;font-size:80%">            
            <tr style="background-color: #CED3D3; text-align:center; border:#A9A9A9 1px solid">
                <th>Id</th>
                <th>No. Ingreso</th>
                <th>Fecha Ingreso</th>
                <th>Hora Ingreso</th>
                <th>No. Factura</th>
                <th>Fecha Factura</th>
                <th>Detalle</th>
                <th>Tercero</th>
                <th>Tipo Ingreso</th>
                <th>Vr. Total</th>
                <th>Sede</th>
                <th>Bodega</th>
                <th>Estado</th>
            </tr>    
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_ingreso'] . '</td>  
                        <td>' . $obj['num_ingreso'] . '</td>
                        <td>' . $obj['fec_ingreso'] . '</td>
                        <td>' . $obj['hor_ingreso'] . '</td>   
                        <td>' . $obj['num_factura'] . '</td>
                        <td>' . $obj['fec_factura'] . '</td> 
                        <td style="text-align:left">' . $obj['detalle']. '</td>   
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_tercero']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_tipo_ingreso']) . '</td>   
                        <td>' . formato_valor($obj['val_total']). '</td>   
                        <td>' . mb_strtoupper($obj['nom_sede']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_bodega']) . '</td>   
                        <td>' . $obj['nom_estado']. '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="background-color:white !important; font-size:60%"> 
            <tr style="background-color:#CED3D3;">
                <td colspan="13" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>&nbsp;&nbsp;-&nbsp;&nbsp;
                    Usuario: <?php echo mb_strtoupper($_SESSION['user']); ?>     
                </td>
            </tr>
        </tfoot>
    </table>
</div>