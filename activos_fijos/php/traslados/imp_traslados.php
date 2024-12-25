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
if (isset($_POST['id_areori']) && $_POST['id_areori']) {
    $where .= " AND acf_traslado.id_area_origen='" . $_POST['id_areori'] . "'";
}
if (isset($_POST['id_resori']) && $_POST['id_resori']) {
    $where .= " AND acf_traslado.id_usr_origen='" . $_POST['id_resori'] . "'";
}
if (isset($_POST['id_traslado']) && $_POST['id_traslado']) {
    $where .= " AND acf_traslado.id_traslado='" . $_POST['id_traslado'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND acf_traslado.fec_traslado BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_aredes']) && $_POST['id_aredes']) {
    $where .= " AND acf_traslado.id_area_destino='" . $_POST['id_aredes'] . "'";
}
if (isset($_POST['id_resdes']) && $_POST['id_resdes']) {
    $where .= " AND acf_traslado.id_usr_destino='" . $_POST['id_resdes'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND acf_traslado.estado=" . $_POST['estado'];
}
try {
    $sql = "SELECT acf_traslado.id_traslado,
                acf_traslado.fec_traslado,acf_traslado.hor_traslado,acf_traslado.observaciones,                    
                ao.nom_area AS nom_area_origen,
                CONCAT_WS(' ',uo.apellido1,uo.apellido2,uo.nombre1,uo.nombre2)  AS nom_usuario_origen,                    
                ad.nom_area AS nom_area_destino,
                CONCAT_WS(' ',ud.apellido1,ud.apellido2,ud.nombre1,ud.nombre2)  AS nom_usuario_destino,                
                acf_traslado.estado,
                CASE acf_traslado.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS nom_estado 
            FROM acf_traslado             
            INNER JOIN far_centrocosto_area AS ao ON (ao.id_area = acf_traslado.id_area_origen)
            LEFT JOIN seg_usuarios_sistema AS uo ON (uo.id_usuario = acf_traslado.id_usr_origen)           
            INNER JOIN far_centrocosto_area AS ad ON (ad.id_area = acf_traslado.id_area_destino)
            LEFT JOIN seg_usuarios_sistema AS ud ON (ud.id_usuario = acf_traslado.id_usr_destino) $where 
            ORDER BY acf_traslado.id_traslado DESC";
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
            <th>REPORTE DE TRASLADOS DE ACTIVOS FIJOS ENTRE: <?php echo $_POST['fec_ini'].' y '. $_POST['fec_fin'] ?></th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th rowspan="2">Id</th>
                <th rowspan="2">Fecha traslado</th>
                <th rowspan="2">Hora traslado</th>
                <th rowspan="2">Observaciones</th>
                <th colspan="2">Unidad Origen</th>
                <th colspan="2">Unidad Destino</th>
                <th rowspan="2">Estado</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Area</th>
                <th>Responsable</th>
                <th>Area</th>
                <th>Responsable</th>
            </tr> 
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_traslado'] . '</td>  
                        <td>' . $obj['fec_traslado'] . '</td>
                        <td>' . $obj['hor_traslado'] . '</td>   
                        <td style="text-align:left">' . $obj['observaciones']. '</td>   
                        <td>' . mb_strtoupper($obj['nom_area_origen']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_usuario_origen']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_area_destino']). '</td>   
                        <td>' . mb_strtoupper($obj['nom_usuario_destino']) . '</td>   
                        <td>' . $obj['nom_estado']. '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="9" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>