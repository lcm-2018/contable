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

$id = isset($_POST['id']) ? $_POST['id'] : -1;

try {
    $sql = "SELECT far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,far_medicamentos.top_min,far_medicamentos.top_max,
                IF(far_med_unidad.id_uni=0,far_med_unidad.unidad,CONCAT(far_med_unidad.unidad,' (',far_med_unidad.descripcion,')')) AS unidad_medida,
                far_subgrupos.nom_subgrupo,far_medicamentos.existencia,far_medicamentos.val_promedio,
                IF(far_medicamentos.estado=1,'ACTIVO','INACTIVO') AS estado             
            FROM far_medicamentos 
            LEFT JOIN far_med_unidad ON (far_med_unidad.id_uni=far_medicamentos.id_unidadmedida_2)
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            WHERE id_med=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

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
            <th>REGISTRO DE ARTICULO</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Código</td>
            <td colspan="3">Descripción</td>
            <td>Subgrupo</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['cod_medicamento']; ?></td>
            <td colspan="3"><?php echo $obj_e['nom_medicamento']; ?></td>
            <td><?php echo $obj_e['nom_subgrupo']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Tope Mínimo</td>
            <td>Tope Máximo</td>
            <td>Valor Promedio</td>
            <td>Unidad de Medida</td>
            <td>Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['top_min']; ?></td>
            <td><?php echo $obj_e['top_max']; ?></td>
            <td><?php echo formato_valor($obj_e['val_promedio']); ?></td>
            <td><?php echo $obj_e['unidad_medida']; ?></td>
            <td><?php echo $obj_e['estado']; ?></td>
        </tr>
    </table>    
</div>