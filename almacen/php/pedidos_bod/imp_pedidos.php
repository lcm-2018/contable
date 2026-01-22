<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include '../common/funciones_generales.php';

$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$where = " WHERE far_pedido.es_pedido_spsr=0";
if($idrol !=1){
    $where .= " AND far_pedido.id_bodega_destino IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

if (isset($_POST['id_sedsol']) && $_POST['id_sedsol']) {
    $where .= " AND far_pedido.id_sede_destino='" . $_POST['id_sedsol'] . "'";
}
if (isset($_POST['id_bodsol']) && $_POST['id_bodsol']) {
    $where .= " AND far_pedido.id_bodega_destino='" . $_POST['id_bodsol'] . "'";
}
if (isset($_POST['id_pedido']) && $_POST['id_pedido']) {
    $where .= " AND far_pedido.id_pedido='" . $_POST['id_pedido'] . "'";
}
if (isset($_POST['num_pedido']) && $_POST['num_pedido']) {
    $where .= " AND far_pedido.num_pedido='" . $_POST['num_pedido'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_pedido.fec_pedido BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_sedpro']) && $_POST['id_sedpro']) {
    $where .= " AND far_pedido.id_sede_origen='" . $_POST['id_sedpro'] . "'";
}
if (isset($_POST['id_bodpro']) && $_POST['id_bodpro']) {
    $where .= " AND far_pedido.id_bodega_origen='" . $_POST['id_bodpro'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_pedido.estado=" . $_POST['estado'];
}
if (isset($_POST['modulo']) && strlen($_POST['modulo'])) {
    $where .= " AND far_pedido.creado_far=" . $_POST['modulo'];
}

try {
    $sql = "SELECT far_pedido.id_pedido,far_pedido.num_pedido,
                    far_pedido.fec_pedido,far_pedido.hor_pedido,far_pedido.detalle,                    
                    ss.nom_sede AS nom_sede_solicita,bs.nombre AS nom_bodega_solicita,                    
                    sp.nom_sede AS nom_sede_provee,bp.nombre AS nom_bodega_provee,                    
                    far_pedido.val_total,
                    CASE far_pedido.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CONFIRMADO' WHEN 3 THEN 'FINALIZADO' END AS nom_estado 
                FROM far_pedido             
                INNER JOIN tb_sedes AS ss ON (ss.id_sede = far_pedido.id_sede_destino)
                INNER JOIN far_bodegas AS bs ON (bs.id_bodega = far_pedido.id_bodega_destino)           
                INNER JOIN tb_sedes AS sp ON (sp.id_sede = far_pedido.id_sede_origen)
                INNER JOIN far_bodegas AS bp ON (bp.id_bodega = far_pedido.id_bodega_origen) $where ORDER BY far_pedido.id_pedido DESC";
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
            <th>REPORTE DE PEDIDOS DE BODEGAS ENTRE: <?php echo $_POST['fec_ini'].' y '. $_POST['fec_fin'] ?></th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th rowspan="2">Id</th>
                <th rowspan="2">No. Pedido</th>
                <th rowspan="2">Fecha Pedido</th>
                <th rowspan="2">Hora Pedido</th>
                <th rowspan="2">Detalle</th>
                <th colspan="2">Unidad DE donde se solicita</th>
                <th colspan="2">Unidad Proveedor</th>
                <th rowspan="2">Valor Total</th>
                <th rowspan="2">Estado</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Sede</th>
                <th>Bodega</th>
                <th>Sede</th>
                <th>Bodega</th>
            </tr> 
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_pedido'] . '</td>  
                        <td>' . $obj['num_pedido'] . '</td>
                        <td>' . $obj['fec_pedido'] . '</td>
                        <td>' . $obj['hor_pedido'] . '</td>   
                        <td style="text-align:left">' . $obj['detalle']. '</td>   
                        <td>' . mb_strtoupper($obj['nom_sede_solicita']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_bodega_solicita']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_sede_provee']). '</td>   
                        <td>' . mb_strtoupper($obj['nom_bodega_provee']) . '</td>   
                        <td>' . formato_valor($obj['val_total']) . '</td>   
                        <td>' . $obj['nom_estado']. '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="11" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>