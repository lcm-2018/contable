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

$where = "WHERE id_tipo_ingreso<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND nom_tipo_ingreso LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $sql = "SELECT id_tipo_ingreso,nom_tipo_ingreso,
                IF(es_int_ext=1,'Interno','Externo') AS es_int_ext,
                IF(orden_compra=1,'SI','') AS orden_compra,
                IF(fianza=1,'SI','') AS fianza,
                IF(farmacia=1,'SI','') AS farmacia,
                IF(almacen=1,'SI','') AS almacen,
                IF(activofijo=1,'SI','') AS activofijo
            FROM far_orden_ingreso_tipo
            $where ORDER BY id_tipo_ingreso DESC";
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
            <th>REPORTE DE TIPOS DE ORDEN DE INGRESO</th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th rowspan="2">Id</th>
                <th rowspan="2">Nombre</th>
                <th rowspan="2">Int/Ext</th>
                <th rowspan="2">Con Orden Compra</th>
                <th rowspan="2">Es Fianza</th>
                <th colspan="3">Modulos</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Almacén</th>
                <th>Farmacia</th>                
                <th>Activos Fijos</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {        
                $tabla .=  
                    '<tr class="resaltar" style="text-align:left"> 
                        <td>' . $obj['id_tipo_ingreso'] . '</td>
                        <td>' . $obj['nom_tipo_ingreso'] . '</td>
                        <td>' . $obj['es_int_ext'] . '</td>
                        <td>' . $obj['orden_compra'] . '</td>
                        <td>' . $obj['fianza'] . '</td>
                        <td>' . $obj['almacen'] . '</td>
                        <td>' . $obj['farmacia'] . '</td>                        
                        <td>' . $obj['activofijo'] . '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="8" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>