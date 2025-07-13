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
$titulo = '';
 switch($id_reporte){
    case '5':
        $titulo = 'SEMAFORIZACIÓN VENCIMIENTO DE LOTES - AGRUPADO POR SEDE-BODEGA-SUBGRUPO';
        break;
} 

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
if (isset($_POST['tipo_asis']) && strlen($_POST['tipo_asis'])) {
    $where .= " AND far_medicamentos.es_clinico=" . $_POST['tipo_asis'];
}
if (isset($_POST['artactivo']) && $_POST['artactivo']) {
    $where .= " AND far_medicamentos.estado=1";
}
if (isset($_POST['lotactivo']) && $_POST['lotactivo']) {
    $where .= " AND far_medicamento_lote.estado=1";
}
if (isset($_POST['con_existencia']) && $_POST['con_existencia']) {
    if ($_POST['con_existencia'] == 1){
        $where .= " AND far_medicamento_lote.existencia>=1";
    } else {
        $where .= " AND far_medicamento_lote.existencia=0";
    }    
}
if (isset($_POST['lote_ven']) && $_POST['lote_ven']) {
    if ($_POST['lote_ven'] == 1){
        $where .= " AND DATEDIFF(far_medicamento_lote.fec_vencimiento,NOW())<0";
    } else {
        $where .= " AND DATEDIFF(far_medicamento_lote.fec_vencimiento,NOW())>=0";
    }    
} 

try {
    $sql = "SELECT tb_sedes.id_sede,tb_sedes.nom_sede,far_bodegas.id_bodega,far_bodegas.nombre AS nom_bodega,
                SUM(far_medicamento_lote.existencia*far_medicamentos.val_promedio) AS val_total_sb
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_bodegas.id_bodega)
            INNER JOIN tb_sedes ON (tb_sedes.id_sede = tb_sedes_bodega.id_sede)
            $where 
            GROUP BY tb_sedes.id_sede,far_bodegas.id_bodega
            ORDER BY tb_sedes.id_sede,far_bodegas.nombre";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();  
    
    $sql = "SELECT far_subgrupos.id_subgrupo,CONCAT_WS(' - ',far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo) AS nom_subgrupo,
                SUM(far_medicamento_lote.existencia*far_medicamentos.val_promedio) AS val_total_sg
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_bodegas.id_bodega)
            $where AND tb_sedes_bodega.id_sede=:id_sede AND far_medicamento_lote.id_bodega=:id_bodega
            GROUP BY far_subgrupos.id_subgrupo
            ORDER BY far_subgrupos.id_subgrupo";
    $rs_g = $cmd->prepare($sql);
    
    $sql = "SELECT far_medicamento_lote.id_lote,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                far_presentacion_comercial.nom_presentacion,acf_marca.descripcion AS nom_marca,far_medicamentos.vida_util,
                CASE far_medicamentos.tipo_riesgo WHEN 1 THEN 'I BAJO RIESGO'
                    WHEN 2 THEN 'IIa RIESGO MODERADO' WHEN 3 THEN 'IIb RIESGO ALTO'
                    WHEN 4 THEN 'III RIESGO MUY ALTO' END AS riesgo,		
                far_medicamento_lote.lote,far_medicamento_lote.existencia,far_medicamentos.val_promedio,
                (far_medicamento_lote.existencia*far_medicamentos.val_promedio) AS val_total,
                far_medicamento_lote.reg_invima,far_medicamento_lote.fec_vencimiento,DATEDIFF(far_medicamento_lote.fec_vencimiento,NOW()) AS dias
            FROM far_medicamento_lote
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_medicamento_lote.id_presentacion)
            INNER JOIN acf_marca ON (acf_marca.id=far_medicamento_lote.id_marca)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega = far_medicamento_lote.id_bodega)
            INNER JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega = far_bodegas.id_bodega)
            $where AND tb_sedes_bodega.id_sede=:id_sede AND far_medicamento_lote.id_bodega=:id_bodega AND far_medicamentos.id_subgrupo=:id_subgrupo
            ORDER BY far_medicamentos.nom_medicamento,far_medicamento_lote.lote";
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
                case '5':
                    $tabla = '<tr style="background-color:#CED3D3; text-align:center">
                        <th>Código Art.</th><th>Nombre</th><th>Presentacion</th><th>Marca</th>
                        <th>Riesgo</th><th>Vida Últil</th><th>ID Lote</th><th>Lote</th><th>Fecha Vencimiento</th><th>Vence en Días</th>
                        <th>INVIMA</th><th>Existencia</th><th>Vr. Promedio</th><th>Vr. Parcial</th><th>Vr. Total</th></tr>';
                    break;    
            }

            $total = 0;
            $numreg = 0;

            foreach ($objs as $obj1) {
                $id_sede = $obj1['id_sede'];
                $id_bodega = $obj1['id_bodega'];
                
                $tabla .= '<tr><th colspan="14" style="text-align:left">' . strtoupper($obj1['nom_sede'] . ' - ' . $obj1['nom_bodega']) . '</th><th>' . formato_valor($obj1['val_total_sb']) . '</th></tr>';

                $rs_g->bindParam(':id_sede',$id_sede);
                $rs_g->bindParam(':id_bodega',$id_bodega);
                $rs_g->execute();
                $objg = $rs_g->fetchAll();

                foreach ($objg as $obj2) {
                    $id_subgrupo = $obj2['id_subgrupo'];
                    $tabla .= '<tr><th colspan="13" style="text-align:left">' . strtoupper($obj2['nom_subgrupo']) . '</th><th>' . formato_valor($obj2['val_total_sg']) . '</th></tr>';

                    $rs_d->bindParam(':id_sede',$id_sede);
                    $rs_d->bindParam(':id_bodega',$id_bodega);
                    $rs_d->bindParam(':id_subgrupo',$id_subgrupo);
                    $rs_d->execute();
                    $objd = $rs_d->fetchAll();
                    $na = '';
                    foreach ($objd as $obj) { 
                        $sw = $na != $obj['nom_medicamento'] ? 1 : 0;
                        $na = $obj['nom_medicamento'];
                        $color = "bg-secondary";
                        if ($obj['dias'] > 365){
                            $color = "bg-success";
                        } else if ($obj['dias'] > 182){
                            $color = "bg-warning";
                        } else if ($obj['dias'] > 0){
                            $color = "bg-danger";
                        }          
                        $tabla .=  '<tr class="resaltar">
                            <td>' . $obj['cod_medicamento'] . '</td>
                            <td style="text-align:left">' . ($sw == 1 ? mb_strtoupper($obj['nom_medicamento']) : '-') . '</td>
                            <td style="text-align:left">' . ($sw == 1 ? mb_strtoupper($obj['nom_presentacion']) : '-') . '</td>
                            <td style="text-align:left">' . ($sw == 1 ? mb_strtoupper($obj['nom_marca']) : '-') . '</td>
                            <td style="text-align:left">' . ($sw == 1 ? mb_strtoupper($obj['riesgo']) : '-') . '</td>
                            <td>' . ($sw == 1 ? $obj['vida_util'] : '-') . '</td>
                            <td>' . $obj['id_lote'] . '</td>    
                            <td>' . $obj['lote'] . '</td>   
                            <td>' . $obj['fec_vencimiento'] . '</td>    
                            <td class="' . $color .'">' . $obj['dias'] . '</td>    
                            <td>' . $obj['reg_invima'] . '</td>   
                            <td>' . $obj['existencia'] . '</td>   
                            <td>' . formato_valor($obj['val_promedio']) . '</td>   
                            <td>' . formato_valor($obj['val_total']) . '</td></tr>';                      
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
                <th colspan="13" style="text-align:left">
                    No. de Registros: <?php echo $numreg; ?>  
                </th>
                <th style="text-align:left">
                    TOTAL:
                </th>
                <th colspan="1" style="text-align:center">
                    <?php echo formato_valor($total); ?>  
                </th>
            </tr>
        </tfoot>
    </table>
</div>