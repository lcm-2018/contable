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

$where = " WHERE 1";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND FM.nom_medicamento LIKE '%" . $_POST['nombre'] . "%'";
}
if (isset($_POST['placa']) && $_POST['placa']) {
    $where .= " AND HV.placa LIKE '" . $_POST['placa'] . "%'";
}
if (isset($_POST['num_serial']) && $_POST['num_serial']) {
    $where .= " AND HV.num_serial LIKE '" . $_POST['num_serial'] . "%'";
}
if (isset($_POST['marca']) && $_POST['marca']) {
    $where .= " AND HV.id_marca=" . $_POST['marca'];
}
if (isset($_POST['estado_gen']) && $_POST['estado_gen']) {
    $where .= " AND HV.estado_general=" . $_POST['estado_gen'];
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND HV.estado=" . $_POST['estado'];
}

try {
    $sql = "SELECT HV.id_activo_fijo,HV.placa,
                FM.cod_medicamento cod_articulo,FM.nom_medicamento nom_articulo,
                HV.des_activo,HV.num_serial,MA.descripcion marca,HV.valor,
                SE.nom_sede,AR.nom_area,
                CONCAT_WS(' ',US.apellido1,US.apellido2,US.nombre1,US.nombre2) AS nom_responsable,
                CASE HV.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' 
                                        WHEN 4 THEN 'SIN SERVICIO' END AS estado_general,
                CASE HV.estado WHEN 1 THEN 'ACTIVO' WHEN 2 THEN 'PARA MANTENIMIENTO' WHEN 3 THEN 'EN MANTENIMIENTO'
                                    WHEN 4 THEN 'INACTIVO' WHEN 5 THEN 'DADO DE BAJA' END AS estado
            FROM acf_hojavida HV
            INNER JOIN far_medicamentos FM On (FM.id_med = HV.id_articulo)
            INNER JOIN acf_marca MA ON (MA.id = HV.id_marca)
            LEFT JOIN tb_sedes SE ON (SE.id_sede=HV.id_sede)
            LEFT JOIN far_centrocosto_area AR ON (AR.id_area=HV.id_area)
            LEFT JOIN seg_usuarios_sistema AS US ON (US.id_usuario=HV.id_responsable) 
            $where ORDER BY HV.id_activo_fijo DESC ";
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
            <th>REPORTE DE ACTIVOS FIJOS</th>
        </tr>     
    </table> 

    <table style="width:100% !important">
        <thead style="font-size:60%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Id</th>
                <th>Placa</th>                                        
                <th>Cod. Articulo</th>
                <th>Articulo</th>
                <th>Nombre Activo Fijo</th>
                <th>No. Serial</th>
                <th>Marca</th>
                <th>Valor</th>                                        
                <th>Sede</th>
                <th>Area</th>
                <th>Responsable</th>
                <th>Estado Funcionam.</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['id_activo_fijo'] . '</td>
                        <td>' . $obj['placa'] . '</td>
                        <td>' . $obj['cod_articulo'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_articulo']) . '</td> 
                        <td style="text-align:left">' . mb_strtoupper($obj['des_activo']) . '</td> 
                        <td>' . $obj['num_serial'] . '</td>
                        <td>' . $obj['marca'] . '</td>
                        <td>' . formato_valor($obj['valor']) . '</td>   
                        <td>' . $obj['nom_sede'] . '</td>   
                        <td>' . $obj['nom_area'] . '</td>   
                        <td>' . $obj['nom_responsable'] . '</td>   
                        <td>' . $obj['estado_general'] . '</td>   
                        <td>' . $obj['estado'] . '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="13" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>  
                </td>
            </tr>
        </tfoot>
    </table>
</div>