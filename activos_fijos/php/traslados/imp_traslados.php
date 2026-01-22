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
if (isset($_POST['id_sedori']) && $_POST['id_sedori']) {
    $where .= " AND AO.id_sede='" . $_POST['id_sedori'] . "'";
}
if (isset($_POST['id_areori']) && $_POST['id_areori']) {
    $where .= " AND AT.id_area_origen='" . $_POST['id_areori'] . "'";
}
if (isset($_POST['id_resori']) && $_POST['id_resori']) {
    $where .= " AND AT.id_usr_origen='" . $_POST['id_resori'] . "'";
}
if (isset($_POST['id_traslado']) && $_POST['id_traslado']) {
    $where .= " AND AT.id_traslado='" . $_POST['id_traslado'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND AT.fec_traslado BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_seddes']) && $_POST['id_seddes']) {
    $where .= " AND AD.id_sede='" . $_POST['id_seddes'] . "'";
}
if (isset($_POST['id_aredes']) && $_POST['id_aredes']) {
    $where .= " AND AT.id_area_destino='" . $_POST['id_aredes'] . "'";
}
if (isset($_POST['id_resdes']) && $_POST['id_resdes']) {
    $where .= " AND AT.id_usr_destino='" . $_POST['id_resdes'] . "'";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND AT.estado=" . $_POST['estado'];
}
try {
    $sql = "SELECT AT.id_traslado,
                AT.fec_traslado,AT.hor_traslado,AT.observaciones,                    
                AO.nom_area AS nom_area_origen,SO.nom_sede AS nom_sede_origen,
                CONCAT_WS(' ',UO.apellido1,UO.apellido2,UO.nombre1,UO.nombre2)  AS nom_usuario_origen,                    
                AD.nom_area AS nom_area_destino,SD.nom_sede AS nom_sede_destino,
                CONCAT_WS(' ',UD.apellido1,UD.apellido2,UD.nombre1,UD.nombre2)  AS nom_usuario_destino,                
                AT.estado,
                CASE AT.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS nom_estado 
            FROM acf_traslado AS AT             
            INNER JOIN far_centrocosto_area AS AO ON (AO.id_area = AT.id_area_origen)
            INNER JOIN tb_sedes AS SO ON (SO.id_sede = AO.id_sede)
            LEFT JOIN seg_usuarios_sistema AS UO ON (UO.id_usuario = AT.id_usr_origen)           
            INNER JOIN far_centrocosto_area AS AD ON (AD.id_area = AT.id_area_destino)
            INNER JOIN tb_sedes AS SD ON (SD.id_sede = AD.id_sede)
            LEFT JOIN seg_usuarios_sistema AS UD ON (UD.id_usuario = AT.id_usr_destino) $where 
            ORDER BY AT.id_traslado DESC";
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
                <th colspan="3">Unidad Origen</th>
                <th colspan="3">Unidad Destino</th>
                <th rowspan="2">Estado</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Sede</th>
                <th>Area</th>
                <th>Responsable</th>
                <th>Sede</th>
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
                        <td>' . mb_strtoupper($obj['nom_sede_origen']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_area_origen']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_usuario_origen']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_sede_destino']). '</td>   
                        <td>' . mb_strtoupper($obj['nom_area_destino']). '</td>   
                        <td>' . mb_strtoupper($obj['nom_usuario_destino']) . '</td>   
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