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
if (isset($_POST['id_mantenimiento']) && $_POST['id_mantenimiento']) {
    $where .= " AND M.id_mantenimiento='" . $_POST['id_mantenimiento'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND M.fec_mantenimiento BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_tercero']) && $_POST['id_tercero']) {
    $where .= " AND M.id_tercero=" . $_POST['id_tercero'] . "";
}
if (isset($_POST['id_tipo_mant']) && $_POST['id_tipo_mant']) {
    $where .= " AND M.tipo_mantenimiento=" . $_POST['id_tipo_mant'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND M.estado=" . $_POST['estado'];
}

try {
    $sql = "SELECT M.id_mantenimiento,M.fec_mantenimiento,M.hor_mantenimiento,
                CASE M.tipo_mantenimiento WHEN 1 THEN 'PREVENTIVO' WHEN 2 THEN 'CORRECTIVO INTERNO' WHEN 3 THEN 'CORRECTIVO EXTERNO' END AS tipo_mantenimiento, 
                M.observaciones,
                CONCAT_WS(' ',U.apellido1,U.apellido2,U.nombre1,U.nombre2) AS nom_responsable,
                T.nom_tercero,M.fec_ini_mantenimiento,M.fec_fin_mantenimiento,M.estado,
                CASE M.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'APROBADO' WHEN 3 THEN 'EN EJECUCION' WHEN 4 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado
            FROM acf_mantenimiento M
            INNER JOIN tb_terceros T ON (T.id_tercero = M.id_tercero)
            INNER JOIN seg_usuarios_sistema U ON (U.id_usuario = M.id_responsable)
            $where ORDER BY M.id_mantenimiento DESC";
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
            <th>REPORTE DE ORDENES DE MANTENIMIENTO DE ACTIVOS FIJOS ENTRE: <?php echo $_POST['fec_ini'].' y '. $_POST['fec_fin'] ?></th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th rowspan="2">Id</th>
                <th rowspan="2">Fecha</th>
                <th rowspan="2">Hora</th>
                <th rowspan="2">Tipo</th>
                <th rowspan="2">Observaciones</th>
                <th colspan="2">Responsable</th>
                <th colspan="2">Periodo Mantenimiento</th>
                <th rowspan="2">Estado</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Funcionario</th>
                <th>Tercero</th>
                <th>Fecha Inicial</th>
                <th>Fecha Final</th> 
            </tr> 
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_mantenimiento'] . '</td>  
                        <td>' . $obj['fec_mantenimiento'] . '</td>
                        <td>' . $obj['hor_mantenimiento'] . '</td>   
                        <td>' . $obj['tipo_mantenimiento'] . '</td>   
                        <td style="text-align:left">' . $obj['observaciones']. '</td>   
                        <td>' . $obj['nom_responsable'] . '</td>   
                        <td>' . $obj['nom_tercero'] . '</td>   
                        <td>' . $obj['fec_ini_mantenimiento'] . '</td>   
                        <td>' . $obj['fec_ini_mantenimiento'] . '</td>   
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