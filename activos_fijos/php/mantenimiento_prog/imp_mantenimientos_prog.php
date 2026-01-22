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

$where = " WHERE MM.estado IN (3,4)"; //Con estado: 3-En ejecución, 4-Cerrado
if (isset($_POST['id_mantenimiento']) && $_POST['id_mantenimiento']) {
    $where .= " AND MM.id_mantenimiento='" . $_POST['id_mantenimiento'] . "'";
}
if (isset($_POST['placa']) && $_POST['placa']) {
    $where .= " AND HV.placa LIKE '" . $_POST['placa'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND FM.nom_medicamento LIKE '%" . $_POST['nombre'] . "%'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND MM.fec_mantenimiento BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_tip_man']) && $_POST['id_tip_man']) {
    $where .= " AND MM.tipo_mantenimiento=" . $_POST['id_tip_man'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND MD.estado=" . $_POST['estado'];
}

try {
    $sql = "SELECT MD.id_mant_detalle,MM.id_mantenimiento,MM.fec_mantenimiento,	
                CASE MM.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'APROBADO' WHEN 3 THEN 'EN EJECUCION' WHEN 4 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado_man,		
                HV.placa,FM.nom_medicamento AS nom_articulo,HV.des_activo,
                CASE MD.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' WHEN 4 THEN 'SIN SERVICIO' END AS estado_general,
                CASE MM.tipo_mantenimiento WHEN 1 THEN 'PREVENTIVO' WHEN 2 THEN 'CORRECTIVO INTERNO' WHEN 3 THEN 'CORRECTIVO EXTERNO' END AS tipo_mantenimiento, 
                MM.fec_ini_mantenimiento,MM.fec_fin_mantenimiento,
                CASE MD.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'EN MANTENIMIENTO' WHEN 3 THEN 'FINALIZADO' END AS estado
            FROM acf_mantenimiento_detalle AS MD
            INNER JOIN acf_mantenimiento AS MM ON (MM.id_mantenimiento=MD.id_mantenimiento)
            INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo=MD.id_activo_fijo)
            INNER JOIN far_medicamentos FM ON (FM.id_med=HV.id_articulo)
            $where ORDER BY MD.id_mant_detalle DESC";
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
            <th>REPORTE DE PROGRESO DE MANTENIMIENTO DE ACTIVOS FIJOS ENTRE: <?php echo $_POST['fec_ini'].' y '. $_POST['fec_fin'] ?></th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th rowspan="2">Id</th>
                <th colspan="3">Orden Mantenimiento</th>
                <th colspan="4">Activo Fijo</th>
                <th colspan="4">Mantenimiento</th>                                                                                
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Id</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Placa</th>
                <th>Articulo</th>
                <th>Nombre</th>
                <th>Estado General</th>
                <th>Tipo</th>
                <th>Fec. Ini.</th>
                <th>Fec. Fin</th>                                        
                <th>Estado</th>
            </tr> 
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_mant_detalle'] . '</td>  
                        <td>' . $obj['id_mantenimiento'] . '</td>
                        <td>' . $obj['fec_mantenimiento'] . '</td>   
                        <td>' . $obj['nom_estado_man'] . '</td>   
                        <td>' . $obj['placa'] . '</td>   
                        <td style="text-align:left">' . $obj['nom_articulo']. '</td>   
                        <td style="text-align:left">' . $obj['des_activo']. '</td>   
                        <td>' . $obj['estado_general'] . '</td>   
                        <td>' . $obj['tipo_mantenimiento'] . '</td>   
                        <td>' . $obj['fec_ini_mantenimiento'] . '</td>   
                        <td>' . $obj['fec_ini_mantenimiento'] . '</td>   
                        <td>' . $obj['estado']. '</td></tr>';
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