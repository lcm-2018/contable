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

$where = " WHERE 1";
if($idrol !=1){
    $sql = "SELECT count(*) AS count FROM seg_bodegas_usuario WHERE id_usuario=$idusr";
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetch();

    if ($bodegas['count'] > 0){
        $where .= " AND far_cec_pedido.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
    }else{
        $where .= " AND far_cec_pedido.id_cencosto IN (SELECT id_centrocosto FROM seg_usuarios_sistema WHERE id_usuario=$idusr)";
    }
}

if (isset($_POST['id_cencos']) && $_POST['id_cencos']) {
    $where .= " AND far_cec_pedido.id_cencosto='" . $_POST['id_cencos'] . "'";
}
if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where .= " AND far_cec_pedido.id_sede='" . $_POST['id_sede'] . "'";
}
if (isset($_POST['id_bodega']) && $_POST['id_bodega']) {
    $where .= " AND far_cec_pedido.id_bodega='" . $_POST['id_bodega'] . "'";
}
if (isset($_POST['id_pedido']) && $_POST['id_pedido']) {
    $where .= " AND far_cec_pedido.id_pedido='" . $_POST['id_pedido'] . "'";
}
if (isset($_POST['num_pedido']) && $_POST['num_pedido']) {
    $where .= " AND far_cec_pedido.num_pedido='" . $_POST['num_pedido'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_cec_pedido.fec_pedido BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_cec_pedido.estado=" . $_POST['estado'];
}

try {
    $sql = "SELECT far_cec_pedido.id_pedido,far_cec_pedido.num_pedido,
                far_cec_pedido.fec_pedido,far_cec_pedido.hor_pedido,
                tb_centrocostos.nom_centro, 
                tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,                    
                far_cec_pedido.val_total,far_cec_pedido.detalle,  
                far_cec_pedido.estado,
                CASE far_cec_pedido.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS nom_estado 
            FROM far_cec_pedido       
            INNER JOIN tb_centrocostos ON (tb_centrocostos.id_centro = far_cec_pedido.id_cencosto)      
            INNER JOIN tb_sedes ON (tb_sedes.id_sede = far_cec_pedido.id_sede)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_cec_pedido.id_bodega) $where ORDER BY far_cec_pedido.id_pedido DESC";
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
                <th>Id</th>
                <th>No. Pedido</th>
                <th>Fecha Pedido</th>
                <th>Hora Pedido</th>
                <th>Detalle</th>
                <th>Dependencia</th>
                <th>Sede Proveedora</th>
                <th>Bodega Proveedora</th>
                <th>Valor Total</th>
                <th>Estado</th>
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
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_centro']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_sede']). '</td>   
                        <td>' . mb_strtoupper($obj['nom_bodega']) . '</td>   
                        <td>' . formato_valor($obj['val_total']) . '</td>   
                        <td>' . $obj['nom_estado']. '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="10" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>