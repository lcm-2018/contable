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

$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$where = "WHERE 1";
if($idrol !=1){
    $where .= " AND far_traslado.id_bodega_origen IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

if (isset($_POST['id_sedori']) && $_POST['id_sedori']) {
    $where .= " AND far_traslado.id_sede_origen='" . $_POST['id_sedori'] . "'";
}
if (isset($_POST['id_bodori']) && $_POST['id_bodori']) {
    $where .= " AND far_traslado.id_bodega_origen='" . $_POST['id_bodori'] . "'";
}
if (isset($_POST['id_tra']) && $_POST['id_tra']) {
    $where .= " AND far_traslado.id_traslado='" . $_POST['id_tra'] . "'";
}
if (isset($_POST['num_tra']) && $_POST['num_tra']) {
    $where .= " AND far_traslado.num_traslado='" . $_POST['num_tra'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_traslado.fec_traslado BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_seddes']) && $_POST['id_seddes']) {
    $where .= " AND far_traslado.id_sede_destino='" . $_POST['id_seddes'] . "'";
}
if (isset($_POST['id_boddes']) && $_POST['id_boddes']) {
    $where .= " AND far_traslado.id_bodega_destino='" . $_POST['id_boddes'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_traslado.estado=" . $_POST['estado'];
}
if (isset($_POST['modulo']) && strlen($_POST['modulo'])) {
    $where .= " AND far_traslado.creado_far=" . $_POST['modulo'];
}

try {
    $sql = "SELECT far_traslado.id_traslado,far_traslado.num_traslado,far_traslado.fec_traslado,far_traslado.hor_traslado,
                    far_traslado.detalle,far_traslado.val_total,
                    tb_so.nom_sede AS nom_sede_origen,tb_bo.nombre AS nom_bodega_origen,
                    tb_sd.nom_sede AS nom_sede_destino,tb_bd.nombre AS nom_bodega_destino,
                    CASE far_traslado.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado
                FROM far_traslado
                INNER JOIN tb_sedes AS tb_so ON (tb_so.id_sede=far_traslado.id_sede_origen)
                INNER JOIN far_bodegas AS tb_bo ON (tb_bo.id_bodega=far_traslado.id_bodega_origen)
                INNER JOIN tb_sedes AS tb_sd ON (tb_sd.id_sede=far_traslado.id_sede_destino)
                INNER JOIN far_bodegas AS tb_bd ON (tb_bd.id_bodega=far_traslado.id_bodega_destino) $where ORDER BY far_traslado.id_traslado DESC";
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
            <th>REPORTE DE TRASLADOS ENTRE: <?php echo $_POST['fec_ini'].' y '. $_POST['fec_fin'] ?></th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th rowspan="2">Id</th>
                <th rowspan="2">No. Traslado</th>
                <th rowspan="2">Fecha Traslado</th>
                <th rowspan="2">Hora Traslado</th>
                <th rowspan="2">Detalle</th>
                <th colspan="2">Unidad Origen</th>
                <th colspan="2">Unidad Destino</th>
                <th rowspan="2">Vr. Total</th>
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
                        <td>' . $obj['id_traslado'] . '</td>  
                        <td>' . $obj['num_traslado'] . '</td>
                        <td>' . $obj['fec_traslado'] . '</td>
                        <td>' . $obj['hor_traslado'] . '</td>                  
                        <td style="text-align:left">' . $obj['detalle'] . '</td>                      
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_sede_origen']) . '</td>   
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_bodega_origen']) . '</td> 
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_sede_destino']) . '</td>   
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_bodega_destino']) . '</td>   
                        <td style="text-align:right">' . formato_valor($obj['val_total']). '</td> 
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