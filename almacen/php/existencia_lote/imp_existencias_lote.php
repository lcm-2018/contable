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

$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$where = " WHERE 1";
if($idrol !=1){
    $where .= " AND far_medicamento_lote.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where .= " AND far_medicamento_lote.id_bodega IN (SELECT id_bodega FROM tb_sedes_bodega WHERE id_sede=" . $_POST['id_sede'] . ")";
}
if (isset($_POST['id_bodega']) && $_POST['id_bodega']) {
    $where .= " AND far_medicamento_lote.id_bodega='" . $_POST['id_bodega'] . "'";
}
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_medicamentos.nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
}
if (isset($_POST['id_subgrupo']) && $_POST['id_subgrupo']) {
    $where .= " AND far_medicamentos.id_subgrupo=" . $_POST['id_subgrupo'];
}
if (isset($_POST['artactivo']) && $_POST['artactivo']) {
    $where .= " AND far_medicamentos.estado=1";
}
if (isset($_POST['lotactivo']) && $_POST['lotactivo']) {
    $where .= " AND far_medicamento_lote.estado=1";
}
if (isset($_POST['conexistencia']) && $_POST['conexistencia']) {
    $where .= " AND far_medicamentos.existencia>=1";
}

try {
    $sql = "SELECT far_medicamento_lote.id_lote,tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,
                far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,far_subgrupos.nom_subgrupo,
                far_medicamento_lote.lote,far_medicamento_lote.existencia,far_medicamentos.val_promedio,
                (far_medicamento_lote.existencia*far_medicamentos.val_promedio) AS val_total,
                far_medicamento_lote.fec_vencimiento,
	            IF(far_medicamentos.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_bodegas.id_bodega)
            INNER JOIN tb_sedes ON (tb_sedes.id_sede = tb_sedes_bodega.id_sede)
            $where ORDER BY far_medicamento_lote.id_lote DESC";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();

    $sql = "SELECT SUM(far_medicamento_lote.existencia*far_medicamentos.val_promedio) AS val_total
            FROM far_medicamento_lote 
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            $where";
    $rs = $cmd->query($sql);
    $obj_tot = $rs->fetch();

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
            <th>REPORTE DE EXISTENCIAS</th>
        </tr>     
    </table> 

    <table style="width:100% !important">
        <thead style="font-size:60%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>ID</th>
                <th>Sede</th>
                <th>Bodega</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Subgrupo</th>
                <th>Lote</th>
                <th>Existencia</th>
                <th>Vr. Promedio</th>
                <th>Vr. Total</th>
                <th>Fecha Vencimiento</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['id_lote'] . '</td>
                        <td>' . mb_strtoupper($obj['nom_sede']) . '</td>
                        <td>' . mb_strtoupper($obj['nom_bodega']) . '</td>
                        <td>' . $obj['cod_medicamento'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_subgrupo']) . '</td>   
                        <td>' . $obj['lote'] . '</td>   
                        <td>' . $obj['existencia'] . '</td>   
                        <td>' . formato_valor($obj['val_promedio']) . '</td>   
                        <td>' . formato_valor($obj['val_total']) . '</td>  
                        <td>' . $obj['fec_vencimiento'] . '</td>    
                        <td>' . $obj['estado'] . '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="3" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>  
                </td>
                <td colspan="5"></td>
                <td style="text-align:left">
                    TOTAL:
                </td>
                <td colspan="1" style="text-align:center">
                    <?php echo formato_valor($obj_tot['val_total']); ?>  
                </td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>