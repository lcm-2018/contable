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

$id_reporte = $_POST['id_reporte'];
$titulo = '';
switch($id_reporte){
    case '1':
        $titulo = 'REPORTE DE EXISTENCIAS POR ARTICULO - AGRUPADO POR SUBGRUPO';
        break;
    case '2':
        $titulo =  'REPORTE DE EXISTENCIAS - TOTALIZADO POR SUBGRUPO';
        break;    
}

$where = "WHERE 1";
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_medicamentos.nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
}
if (isset($_POST['id_subgrupo']) && $_POST['id_subgrupo']) {
    $where .= " AND far_medicamentos.id_subgrupo=" . $_POST['id_subgrupo'];
}
if (isset($_POST['tipo_asis']) && strlen($_POST['tipo_asis'])) {
    $where .= " AND far_medicamentos.es_clinico=" . $_POST['tipo_asis'];
}
if (isset($_POST['artactivo']) && $_POST['artactivo']) {
    $where .= " AND far_medicamentos.estado=1";
}
if (isset($_POST['con_existencia']) && $_POST['con_existencia']) {
    if ($_POST['con_existencia'] == 1){
        $where .= " AND far_medicamentos.existencia>=1";
    } else {
        $where .= " AND far_medicamentos.existencia=0";
    }    
}

try {
    $sql = "SELECT far_subgrupos.id_subgrupo,CONCAT_WS(' - ',far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo) AS nom_subgrupo,                
                SUM(far_medicamentos.existencia*far_medicamentos.val_promedio) AS val_totsbg
            FROM far_medicamentos
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo) $where 
            GROUP BY far_subgrupos.id_subgrupo
            ORDER BY far_subgrupos.id_subgrupo ASC";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();

    $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                far_medicamentos.top_min,far_medicamentos.top_max,
                far_medicamentos.existencia,far_medicamentos.val_promedio,
                (far_medicamentos.existencia*far_medicamentos.val_promedio) AS val_total
            FROM far_medicamentos $where AND far_medicamentos.id_subgrupo=:id_subgrupo 
            ORDER BY far_medicamentos.nom_medicamento";
    $rs_d = $cmd->prepare($sql);

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
            <th><?php echo $titulo; ?></th>
        </tr>     
    </table> 

    <table style="width:100% !important">        
        <tbody style="font-size: 60%;">
            <?php
            $tabla='';
            switch($id_reporte){
                case '1':
                    $tabla = '<tr style="background-color:#CED3D3; text-align:center">
                        <th>ID Art.</th><th>Código</th><th>Nombre</th><th>Tope Mínimo</th><th>Tope Máximo</th><th>Existencia</th>
                        <th>Vr. Promedio</th><th>Vr. Parcial</th><th>Vr. Total</th></tr>';
                    break;    
                case '2':
                    $tabla = '<tr style="background-color:#CED3D3; text-align:center">
                        <th colspan="8">Subgrupo</th><th>Vr. Total</th></tr>';
                    break; 
            }
            
            $total = 0;
            $numreg = 0;
            
            foreach ($objs as $obj1) {                

                if ($id_reporte == 1){
                    $id_subgrupo = $obj1['id_subgrupo'];                
                    $tabla .= '<tr><th colspan="8" style="text-align:left">' . strtoupper($obj1['nom_subgrupo']) . '</th><th style="text-align:right">' . formato_valor($obj1['val_totsbg']) . '</th></tr>';

                    $rs_d->bindParam(':id_subgrupo',$id_subgrupo);                        
                    $rs_d->execute();
                    $objd = $rs_d->fetchAll();
                    
                    foreach ($objd as $obj) {                        
                        $tabla .=  '<tr class="resaltar"> 
                                <td>' . str_repeat('&nbsp',10) . $obj['id_med'] . '</td>
                                <td>' . $obj['cod_medicamento'] . '</td>
                                <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                                <td>' . $obj['top_min'] . '</td>   
                                <td>' . $obj['top_max'] . '</td>   
                                <td>' . $obj['existencia'] . '</td>   
                                <td style="text-align:right">' . formato_valor($obj['val_promedio']) . '</td>   
                                <td style="text-align:right">' . formato_valor($obj['val_total']) . '</td></tr>';                        
                        $total += $obj['val_total'];        
                        $numreg += 1;
                    }
                } else {
                    $tabla .= '<tr><td colspan="8" style="text-align:left">' . strtoupper($obj1['nom_subgrupo']) . '</td><td style="text-align:right">' . formato_valor($obj1['val_totsbg']) . '</td></tr>';
                    $total += $obj1['val_totsbg'];
                    $numreg += 1;
                }                     
            }                 
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <th colspan="7" style="text-align:left">
                    No. de Registros: <?php echo $numreg; ?>  
                </th>
                <th style="text-align:left">
                    TOTAL:
                </th>
                <th style="text-align:right">
                    <?php echo formato_valor($total); ?>  
                </th>
            </tr>
        </tfoot>
    </table>
</div>