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
$id_reporte = $_POST['id_reporte'];
$titulo = '';
switch($id_reporte){
    case '2':
        $titulo = "REPORTE DE EXISTENCIAS POR ARTICULO A:$fecha, AGRUPADO POR SEDE-BODEGA-GRUPO";
        break;      
} 

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
if (isset($_POST['lotactivo']) && $_POST['lotactivo']) {
    $where_art .= " AND far_medicamento_lote.estado=1";
}
if (isset($_POST['lote_ven']) && $_POST['lote_ven']) {
    if ($_POST['lote_ven'] == 1){
        $where_art .= " AND DATEDIFF(far_medicamento_lote.fec_vencimiento,'$fecha')<0";
    } else {
        $where_art .= " AND DATEDIFF(far_medicamento_lote.fec_vencimiento,'$fecha')>=0";
    }    
} 
if (isset($_POST['con_existencia']) && $_POST['con_existencia']) {
    if ($_POST['con_existencia'] == 1){
        $where_art .= " AND e.existencia_lote>=1";
    } else {
        $where_art .= " AND e.existencia_lote=0";
    }    
}

try {
    $sql = "SELECT tb_sedes.id_sede,tb_sedes.nom_sede,far_bodegas.id_bodega,far_bodegas.nombre AS nom_bodega,
                SUM(e.existencia_lote*v.val_promedio) AS val_total_sb
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_bodegas.id_bodega)
            INNER JOIN tb_sedes ON (tb_sedes.id_sede = tb_sedes_bodega.id_sede)
            INNER JOIN (SELECT id_lote,MAX(id_kardex) AS id FROM far_kardex $where_kar GROUP BY id_lote) AS kei ON (kei.id_lote=far_medicamento_lote.id_lote)
            INNER JOIN far_kardex AS e ON (e.id_kardex=kei.id)
            INNER JOIN (SELECT id_med,MAX(id_kardex) AS id FROM far_kardex WHERE fec_movimiento<='$fecha' AND estado=1 GROUP BY id_med) AS kvi ON (kvi.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_kardex AS v ON (v.id_kardex=kvi.id)
            $where_art 
            GROUP BY tb_sedes.id_sede,far_bodegas.id_bodega
            ORDER BY tb_sedes.id_sede,far_bodegas.nombre";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();

    $sql = "SELECT far_subgrupos.id_subgrupo,CONCAT_WS(' - ',far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo) AS nom_subgrupo,
                SUM(e.existencia_lote*v.val_promedio) AS val_total_sg
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN (SELECT id_lote,MAX(id_kardex) AS id FROM far_kardex $where_kar GROUP BY id_lote) AS kei ON (kei.id_lote=far_medicamento_lote.id_lote)
            INNER JOIN far_kardex AS e ON (e.id_kardex=kei.id)
            INNER JOIN (SELECT id_med,MAX(id_kardex) AS id FROM far_kardex WHERE fec_movimiento<='$fecha' AND estado=1 GROUP BY id_med) AS kvi ON (kvi.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_kardex AS v ON (v.id_kardex=kvi.id)
            $where_art AND tb_sedes_bodega.id_sede=:id_sede AND far_medicamento_lote.id_bodega=:id_bodega
            GROUP BY far_subgrupos.id_subgrupo
            ORDER BY far_subgrupos.id_subgrupo";
    $rs_g = $cmd->prepare($sql);

    $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                SUM(e.existencia_lote) AS existencia,v.val_promedio,
                SUM(e.existencia_lote*v.val_promedio) AS val_total
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN (SELECT id_lote,MAX(id_kardex) AS id FROM far_kardex $where_kar GROUP BY id_lote) AS kei ON (kei.id_lote=far_medicamento_lote.id_lote)
            INNER JOIN far_kardex AS e ON (e.id_kardex=kei.id)
            INNER JOIN (SELECT id_med,MAX(id_kardex) AS id FROM far_kardex WHERE fec_movimiento<='$fecha' AND estado=1 GROUP BY id_med) AS kvi ON (kvi.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_kardex AS v ON (v.id_kardex=kvi.id)
            $where_art AND tb_sedes_bodega.id_sede=:id_sede AND far_medicamento_lote.id_bodega=:id_bodega AND far_medicamentos.id_subgrupo=:id_subgrupo
            GROUP BY far_medicamentos.id_med
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
            $tabla = '';
            switch($id_reporte){
                case '2':
                    $tabla = '<tr style="background-color:#CED3D3; text-align:center">
                        <th>ID Art.</th><th>Código Art.</th><th>Nombre</th><th>Existencia</th>
                        <th>Vr. Promedio</th><th>Vr. Parcial</th><th>Vr. Total</th></tr>';
                    break; 
            }

            $total = 0;
            $numreg = 0;

            foreach ($objs as $obj1) {
                $id_sede = $obj1['id_sede'];
                $id_bodega = $obj1['id_bodega'];
                
                $tabla .= '<tr><th colspan="6" style="text-align:left">' . strtoupper($obj1['nom_sede'] . ' - ' . $obj1['nom_bodega']) . '</th><th style="text-align:right">' . formato_valor($obj1['val_total_sb']) . '</th></tr>';

                $rs_g->bindParam(':id_sede',$id_sede);
                $rs_g->bindParam(':id_bodega',$id_bodega);
                $rs_g->execute();
                $objg = $rs_g->fetchAll();

                foreach ($objg as $obj2) {                  
                    $id_subgrupo = $obj2['id_subgrupo'];
                    $tabla .= '<tr><th colspan="5" style="text-align:left">' . str_repeat('&nbsp',10) . strtoupper($obj2['nom_subgrupo']) . '</th><th style="text-align:right">' . formato_valor($obj2['val_total_sg']) . '</th></tr>';

                    $rs_d->bindParam(':id_sede',$id_sede);
                    $rs_d->bindParam(':id_bodega',$id_bodega);
                    $rs_d->bindParam(':id_subgrupo',$id_subgrupo);
                    $rs_d->execute();
                    $objd = $rs_d->fetchAll();
            
                    foreach ($objd as $obj) {                        
                        $tabla .=  '<tr class="resaltar"> 
                            <td>' . str_repeat('&nbsp',20) . $obj['id_med'] . '</td>                                                
                            <td>' . $obj['cod_medicamento'] . '</td>
                            <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>
                            <td>' . $obj['existencia'] . '</td>   
                            <td style="text-align:right">' . formato_valor($obj['val_promedio']) . '</td>   
                            <td style="text-align:right">' . formato_valor($obj['val_total']) . '</td></tr>';   
                    }

                    $total += $obj2['val_total_sg'];
                    $numreg += count($objd);
                } 
            }    
            echo $tabla;                   
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <th colspan="5" style="text-align:left">
                    No. de Registros: <?php echo $numreg; ?>  
                </th>
                <th style="text-align:left">
                    TOTAL:
                </th>
                <th style="text-align:center">
                    <?php echo formato_valor($total); ?>  
                </th>
            </tr>
        </tfoot>
    </table>
</div>