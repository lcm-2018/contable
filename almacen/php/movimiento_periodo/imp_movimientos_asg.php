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
$id_reporte = $_POST['id_reporte'];

if($_POST['fecini'] && $_POST['fecfin']){
    $fecini = $_POST['fecini'];
    $fecfin = $_POST['fecfin'];
} else {
    $fecini = date('Y-m-d');
    $fecfin = date('Y-m-d');
}    

$titulo = '';
switch($id_reporte){
    case '1':
        $titulo = "REPORTE DE MOVIMIENTOS ENTRE " . $fecini . ' Y ' . $fecfin . " - AGRUPADO POR SUBGRUPO";
        break;
    case '2':
        $titulo = "REPORTE DE MOVIMIENTOS ENTRE " . $fecini . ' Y ' . $fecfin . " - TOTALIZADO POR SUBGRUPO";
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

$where_mov = $where_kar . ' AND (id_ingreso IS NOT NULL OR id_egreso IS NOT NULL)';

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
        $where_art .= " AND ef.existencia_fin>=1";
    } else {
        $where_art .= " AND ef.existencia_fin=0";
    }    
}

try {
    $sql = "SELECT far_subgrupos.id_subgrupo,CONCAT_WS(' - ',far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo) AS nom_subgrupo,                  
                SUM(ef.existencia_fin*vf.val_promedio_fin) AS valores_fin_sg,
                SUM(ei.existencia_ini*vi.val_promedio_ini) AS valores_ini_sg,
                SUM(es.valores_ent) AS valores_ent_sg,                
                SUM(es.valores_sal) AS valores_sal_sg
            FROM far_medicamentos
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN (SELECT id_med,SUM(existencia_lote) AS existencia_fin FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex $where_kar AND fec_movimiento<='$fecfin' GROUP BY id_lote)                        
                        GROUP BY id_med	
                        ) AS ef ON (ef.id_med = far_medicamentos.id_med)	
            INNER JOIN (SELECT id_med,val_promedio AS val_promedio_fin FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex				
                                            WHERE fec_movimiento<='$fecfin' AND estado=1 
                                            GROUP BY id_med)
                        ) AS vf ON (vf.id_med = far_medicamentos.id_med) 
            LEFT JOIN (SELECT id_med,SUM(existencia_lote) AS existencia_ini FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex $where_kar AND fec_movimiento<'$fecini' GROUP BY id_lote)                        
                        GROUP BY id_med	
                        ) AS ei ON (ei.id_med = far_medicamentos.id_med)	
            LEFT JOIN (SELECT id_med,val_promedio AS val_promedio_ini FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex				
                                            WHERE fec_movimiento<'$fecini' AND estado=1 
                                            GROUP BY id_med)
                        ) AS vi ON (vi.id_med = far_medicamentos.id_med) 
            LEFT JOIN (SELECT id_med, 
                        SUM(can_ingreso) AS cantidad_ent,SUM(can_ingreso*val_ingreso) AS valores_ent, 
                        SUM(can_egreso) AS cantidad_sal,SUM(can_egreso*val_promedio) AS valores_sal 
                        FROM far_kardex $where_mov AND fec_movimiento BETWEEN '$fecini' AND '$fecfin' AND estado=1 
                        GROUP BY id_med
                        ) AS es ON (es.id_med = far_medicamentos.id_med) 
            $where_art 
            GROUP BY far_subgrupos.id_subgrupo
            ORDER BY far_subgrupos.id_subgrupo ASC";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();

    $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                IFNULL(ef.existencia_fin,0) as existencia_fin,
                (ef.existencia_fin*vf.val_promedio_fin) AS valores_fin,
                IFNULL(ei.existencia_ini,0) as existencia_ini,
                (ei.existencia_ini*vi.val_promedio_ini) AS valores_ini,
                IFNULL(es.cantidad_ent,0) as cantidad_ent,
                es.valores_ent,
                IFNULL(es.cantidad_sal,0) as cantidad_sal,
                es.valores_sal
            FROM far_medicamentos
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN (SELECT id_med,SUM(existencia_lote) AS existencia_fin FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex $where_kar AND fec_movimiento<='$fecfin' GROUP BY id_lote)                        
                        GROUP BY id_med	
                        ) AS ef ON (ef.id_med = far_medicamentos.id_med)	
            INNER JOIN (SELECT id_med,val_promedio AS val_promedio_fin FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex				
                                            WHERE fec_movimiento<='$fecfin' AND estado=1 
                                            GROUP BY id_med)
                        ) AS vf ON (vf.id_med = far_medicamentos.id_med) 
            LEFT JOIN (SELECT id_med,SUM(existencia_lote) AS existencia_ini FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex $where_kar AND fec_movimiento<'$fecini' GROUP BY id_lote)                        
                        GROUP BY id_med	
                        ) AS ei ON (ei.id_med = far_medicamentos.id_med)	
            LEFT JOIN (SELECT id_med,val_promedio AS val_promedio_ini FROM far_kardex
                        WHERE id_kardex IN (SELECT MAX(id_kardex) FROM far_kardex				
                                            WHERE fec_movimiento<'$fecini' AND estado=1 
                                            GROUP BY id_med)
                        ) AS vi ON (vi.id_med = far_medicamentos.id_med) 
            LEFT JOIN (SELECT id_med, 
                        SUM(can_ingreso) AS cantidad_ent,SUM(can_ingreso*val_ingreso) AS valores_ent, 
                        SUM(can_egreso) AS cantidad_sal,SUM(can_egreso*val_promedio) AS valores_sal 
                        FROM far_kardex $where_mov AND fec_movimiento BETWEEN '$fecini' AND '$fecfin' AND estado=1 
                        GROUP BY id_med
                        ) AS es ON (es.id_med = far_medicamentos.id_med) 
            $where_art AND far_medicamentos.id_subgrupo=:id_subgrupo 
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
                case '1':
                   $tabla = '<tr style="background-color:#CED3D3; color:#000000; text-align:center">
                                <th rowspan="2">Id</th><th rowspan="2">Código</th>
                                <th rowspan="2">Nombre</th><th colspan="2">Saldo Inicial</th>
                                <th colspan="2">Entradas</th><th colspan="2">Salidas</th>
                                <th colspan="2">Saldo_Final</th>
                            </tr>
                            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                                <th>Existencia</th><th>Valores</th>
                                <th>Cantidad</th><th>Valores</th>
                                <th>Cantidad</th><th>Valores</th>
                                <th>Existencia</th>
                            <th>Valores</th>';
                    break;    
                case '2':
                    $tabla = '<tr style="background-color:#CED3D3; text-align:center">
                              <th colspan="3">Subgrupo</th><th colspan="2">Saldo Inicial</th>
                              <th colspan="2">Entradas</th><th colspan="2">Salidas</th><th colspan="2">Saldo Final</th></tr>';
                    break; 
            }
            
            $total_ini = 0;
            $total_ent = 0;
            $total_sal = 0;
            $total_fin = 0;
            $numreg = 0;

            foreach ($objs as $obj1) {
                if ($id_reporte == 1){    
                    $id_subgrupo = $obj1['id_subgrupo'];                
                    $tabla .= '<tr><th colspan="3" style="text-align:left">' . strtoupper($obj1['nom_subgrupo']) . '</th>
                                   <th colspan="2" style="text-align:right">' . formato_valor($obj1['valores_ini_sg']) . '</th>
                                   <th colspan="2" style="text-align:right">' . formato_valor($obj1['valores_ent_sg']) . '</th>
                                   <th colspan="2" style="text-align:right">' . formato_valor($obj1['valores_sal_sg']) . '</th>
                                   <th colspan="2" style="text-align:right">' . formato_valor($obj1['valores_fin_sg']) . '</th></tr>';

                    $rs_d->bindParam(':id_subgrupo',$id_subgrupo);                        
                    $rs_d->execute();
                    $objd = $rs_d->fetchAll();
                    
                    foreach ($objd as $obj) {
                        $tabla .=  '<tr class="resaltar"> 
                            <td>' . str_repeat('&nbsp',20) . $obj['id_med'] . '</td>
                            <td>' . $obj['cod_medicamento'] . '</td>
                            <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                            <td>' . $obj['existencia_ini'] . '</td>   
                            <td style="text-align:right">' . formato_valor($obj['valores_ini']) . '</td>   
                            <td>' . $obj['cantidad_ent'] . '</td>   
                            <td style="text-align:right">' . formato_valor($obj['valores_ent']) . '</td>   
                            <td>' . $obj['cantidad_sal'] . '</td>   
                            <td style="text-align:right">' . formato_valor($obj['valores_sal']) . '</td>   
                            <td>' . $obj['existencia_fin'] . '</td>   
                            <td style="text-align:right">' . formato_valor($obj['valores_fin']) . '</td></tr>';
                        $total_ini += $obj['valores_ini'];
                        $total_ent += $obj['valores_ent'];
                        $total_sal += $obj['valores_sal'];
                        $total_fin += $obj['valores_fin'];
                        $numreg += 1;    
                    }
                } else {
                    $tabla .= '<tr><td colspan="3" style="text-align:left">' . strtoupper($obj1['nom_subgrupo']) . '</td>
                                   <td colspan="2" style="text-align:right">' . formato_valor($obj1['valores_ini_sg']) . '</td>
                                   <td colspan="2" style="text-align:right">' . formato_valor($obj1['valores_ent_sg']) . '</td>
                                   <td colspan="2" style="text-align:right">' . formato_valor($obj1['valores_sal_sg']) . '</td>
                                   <td colspan="2" style="text-align:right">' . formato_valor($obj1['valores_fin_sg']) . '</td></tr>';
                    $total_ini += $obj1['valores_ini_sg'];
                    $total_ent += $obj1['valores_ent_sg'];
                    $total_sal += $obj1['valores_sal_sg'];
                    $total_fin += $obj1['valores_fin_sg'];
                    $numreg += 1;
                } 
            }                  
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <th colspan="3" style="text-align:left">
                    No. de Registros: <?php echo $numreg; ?>  
                </th>
                <th style="text-align:left">
                    TOTAL:
                </th>
                <th style="text-align:right">
                    <?php echo formato_valor($total_ini); ?>  
                </th>
                <th colspan="2" style="text-align:right">
                    <?php echo formato_valor($total_ent); ?>  
                </th>
                <th colspan="2" style="text-align:right">
                    <?php echo formato_valor($total_sal); ?>  
                </th>
                <th colspan="2" style="text-align:right">
                    <?php echo formato_valor($total_fin); ?>  
                </th>
            </tr>
        </tfoot>
    </table>
</div>