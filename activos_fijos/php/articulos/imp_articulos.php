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

$where = "WHERE far_subgrupos.id_grupo IN (3,4,5)";
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_medicamentos.nom_medicamento LIKE '%" . $_POST['nombre'] . "%'";
}
if (isset($_POST['subgrupo']) && $_POST['subgrupo']) {
    $where .= " AND far_medicamentos.id_subgrupo=" . $_POST['subgrupo'];
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_medicamentos.estado=" . $_POST['estado'];
}

try {
    $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
            far_subgrupos.nom_subgrupo,far_medicamentos.top_min,far_medicamentos.top_max,
            e.existencia,acf_orden_ingreso_detalle.valor,
            IF(far_medicamentos.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM far_medicamentos
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo) 
            LEFT JOIN (SELECT acf_orden_ingreso_detalle.id_articulo,MAX(acf_orden_ingreso_detalle.id_ing_detalle) AS id 
                        FROM acf_orden_ingreso_detalle 
                        INNER JOIN acf_orden_ingreso ON (acf_orden_ingreso.id_ingreso=acf_orden_ingreso_detalle.id_ingreso)
                        WHERE acf_orden_ingreso.estado=2
                        GROUP BY acf_orden_ingreso_detalle.id_articulo) AS v ON (v.id_articulo=far_medicamentos.id_med)
            LEFT JOIN acf_orden_ingreso_detalle ON (acf_orden_ingreso_detalle.id_ing_detalle=v.id)
            LEFT JOIN (SELECT id_articulo, COUNT(*) AS existencia FROM acf_hojavida
                       WHERE estado IN (1,2,3)
                       GROUP BY id_articulo) AS e ON (e.id_articulo=far_medicamentos.id_med)
            $where ORDER BY far_medicamentos.id_med DESC ";
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
            <th>REPORTE DE ARTICULOS</th>
        </tr>     
    </table> 

    <table style="width:100% !important">
        <thead style="font-size:60%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Subgrupo</th>
                <th>Tope Mínimo</th>
                <th>Tope Máximo</th>
                <th>Existencia</th>
                <th>Vr. Última Compra</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['id_med'] . '</td>
                        <td>' . $obj['cod_medicamento'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_subgrupo']) . '</td>   
                        <td>' . $obj['top_min'] . '</td>   
                        <td>' . $obj['top_max'] . '</td>   
                        <td>' . $obj['existencia'] . '</td>   
                        <td>' . formato_valor($obj['valor']) . '</td>   
                        <td>' . $obj['estado'] . '</td></tr>';
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