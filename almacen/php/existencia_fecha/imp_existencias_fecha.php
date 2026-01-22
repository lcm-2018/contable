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

$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$fecha = $_POST['fecha'] ? $_POST['fecha'] : date('Y-m-d');
$titulo = "REPORTE DE EXISTENCIAS A: " . $fecha;

$where_kar = " WHERE far_kardex.estado=1";
if($idrol !=1){
    $where_kar .= " AND far_kardex.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where_kar .= " AND far_kardex.id_sede='" . $_POST['id_sede'] . "'";
}
if (isset($_POST['id_bodega']) && $_POST['id_bodega']) {
    $where_kar .= " AND far_kardex.id_bodega='" . $_POST['id_bodega'] . "'";
}
if (isset($_POST['fecha']) && $_POST['fecha']) {
    $where_kar .= " AND far_kardex.fec_movimiento<='" . $_POST['fecha'] . "'";    
}
if (isset($_POST['lotactivo']) && $_POST['lotactivo']) {
    $where_kar .= " AND far_medicamento_lote.estado=1";
}
if (isset($_POST['lote_ven']) && $_POST['lote_ven']) {
    if ($_POST['lote_ven'] == 1){
        $where_kar .= " AND DATEDIFF(far_medicamento_lote.fec_vencimiento,'$fecha')<0";
    } else {
        $where_kar .= " AND DATEDIFF(far_medicamento_lote.fec_vencimiento,'$fecha')>=0";
    }    
} 

$where_art = " WHERE 1";
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where_art .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where_art .= " AND far_medicamentos.nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
}
if (isset($_POST['id_subgrupo']) && $_POST['id_subgrupo']) {
    $where_art .= " AND far_medicamentos.id_subgrupo=" . $_POST['id_subgrupo'];
}
if (isset($_POST['tipo_asis']) && strlen($_POST['tipo_asis'])) {
    $where_art .= " AND far_medicamentos.es_clinico=" . $_POST['tipo_asis'];
}
if (isset($_POST['artactivo']) && $_POST['artactivo']) {
    $where_art .= " AND far_medicamentos.estado=1";
}
if (isset($_POST['con_existencia']) && $_POST['con_existencia']) {
    if ($_POST['con_existencia'] == 1){
        $where_art .= " AND e.existencia_fecha>=1";
    } else {
        $where_art .= " AND e.existencia_fecha=0";
    }    
}

try {
    $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                far_subgrupos.nom_subgrupo,e.existencia_fecha,v.val_promedio_fecha,
                (e.existencia_fecha*v.val_promedio_fecha) AS val_total
            FROM far_medicamentos
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN (SELECT ke.id_med,SUM(ke.existencia_lote) AS existencia_fecha 
                        FROM far_kardex AS ke
                        WHERE ke.id_kardex IN (SELECT MAX(far_kardex.id_kardex) 
                                               FROM far_kardex
                                               INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_kardex.id_lote) 
                                               $where_kar 
                                               GROUP BY far_kardex.id_lote)
                        GROUP BY ke.id_med	
                        ) AS e ON (e.id_med = far_medicamentos.id_med)	
            INNER JOIN (SELECT kv.id_med,kv.val_promedio AS val_promedio_fecha 
                        FROM far_kardex AS kv
                        WHERE kv.id_kardex IN (SELECT MAX(far_kardex.id_kardex) 
                                               FROM far_kardex				
                                               WHERE far_kardex.fec_movimiento<='$fecha' AND far_kardex.estado=1 
                                               GROUP BY far_kardex.id_med)
                        ) AS v ON (v.id_med = far_medicamentos.id_med) 
            $where_art ORDER BY far_medicamentos.nom_medicamento ASC";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();

    $sql = "SELECT SUM(e.existencia_fecha*v.val_promedio_fecha) AS val_total
            FROM far_medicamentos
            INNER JOIN (SELECT ke.id_med,SUM(ke.existencia_lote) AS existencia_fecha 
                        FROM far_kardex AS ke
                        WHERE ke.id_kardex IN (SELECT MAX(far_kardex.id_kardex) 
                                               FROM far_kardex 
                                               INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_kardex.id_lote)
                                               $where_kar 
                                               GROUP BY far_kardex.id_lote)                        
                        GROUP BY ke.id_med	
                        ) AS e ON (e.id_med = far_medicamentos.id_med)	
            INNER JOIN (SELECT kv.id_med,kv.val_promedio AS val_promedio_fecha 
                        FROM far_kardex AS kv
                        WHERE kv.id_kardex IN (SELECT MAX(id_kardex) 
                                               FROM far_kardex				
                                               WHERE fec_movimiento<='$fecha' AND estado=1 
                                               GROUP BY id_med)
                        ) AS v ON (v.id_med = far_medicamentos.id_med) 
            $where_art";
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
            <th><?php echo $titulo; ?></th>
        </tr>     
    </table> 

    <table style="width:100% !important">
        <thead style="font-size:60%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Subgrupo</th>
                <th>Existencia</th>
                <th>Vr. Promedio</th>
                <th>Vr. Total</th>
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
                        <td>' . $obj['existencia_fecha'] . '</td>   
                        <td>' . formato_valor($obj['val_promedio_fecha']) . '</td>   
                        <td>' . formato_valor($obj['val_total']) . '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="5" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>  
                </td>
                <td style="text-align:left">
                    TOTAL:
                </td>
                <td style="text-align:center">
                    <?php echo formato_valor($obj_tot['val_total']); ?>  
                </td>
            </tr>
        </tfoot>
    </table>
</div>