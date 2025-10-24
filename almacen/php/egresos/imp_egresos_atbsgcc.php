<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include '../common/funciones_generales.php';

$idusr = $_SESSION['id_user'];
$idrol = $_SESSION['rol'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_reporte = $_POST['id_reporte'];
$titulo = '';
 switch($id_reporte){
    case '5':
        $titulo = 'REPORTE DE EGRESOS ENTRE:' . $_POST['fec_ini'] . ' y ' .  $_POST['fec_fin'] . ', TOTALIZADOS POR TIPO DE EGRESO-SEDE-BODEGA-CENTRO DE COSTO-SUBGRUPO';
        break;
} 

//$where = "WHERE far_orden_egreso.id_tipo_egreso NOT IN (1,2) AND far_orden_egreso.id_ingreso IS NULL";
$where = "WHERE far_orden_egreso.estado=2";
if($idrol !=1){
    $where .= " AND far_orden_egreso.id_bodega IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
}

if (isset($_POST['id_sede']) && $_POST['id_sede']) {
    $where .= " AND far_orden_egreso.id_sede='" . $_POST['id_sede'] . "'";
}
if (isset($_POST['id_bodega']) && $_POST['id_bodega']) {
    $where .= " AND far_orden_egreso.id_bodega='" . $_POST['id_bodega'] . "'";
}
if (isset($_POST['id_egr']) && $_POST['id_egr']) {
    $where .= " AND far_orden_egreso.id_egreso='" . $_POST['id_egr'] . "'";
}
if (isset($_POST['num_egr']) && $_POST['num_egr']) {
    $where .= " AND far_orden_egreso.num_egreso='" . $_POST['num_egr'] . "'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_orden_egreso.fec_egreso BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}

$id_tipegr = isset($_POST['id_tipegr']) ? implode(",", array_filter($_POST['id_tipegr'])) : '';
if ($id_tipegr) {
    $where .= " AND far_orden_egreso.id_tipo_egreso IN (" . $id_tipegr . ")";    
}  

if (isset($_POST['id_cencost']) && $_POST['id_cencost']) {
    $where .= " AND far_orden_egreso.id_centrocosto=" . $_POST['id_cencost'] . "";
}
if (isset($_POST['id_sede_des']) && $_POST['id_sede_des']) {
    $where .= " AND far_orden_egreso.id_area IN (SELECT id_area FROM far_centrocosto_area WHERE id_sede=" . $_POST['id_sede_des'] . ")";
}
if (isset($_POST['id_area']) && $_POST['id_area']) {
    $where .= " AND far_orden_egreso.id_area=" . $_POST['id_area'] . "";
}
if (isset($_POST['id_tercero']) && $_POST['id_tercero']) {
    $where .= " AND far_orden_egreso.id_cliente=" . $_POST['id_tercero'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_orden_egreso.estado=" . $_POST['estado'];
}
if (isset($_POST['modulo']) && strlen($_POST['modulo'])) {
    $where .= " AND far_orden_egreso.creado_far=" . $_POST['modulo'];
}

try {
    $sql = "SELECT id_centro,nom_centro FROM tb_centrocostos WHERE es_farmacia=1 LIMIT 1";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $id_farmacia = isset($obj['id_centro']) ? $obj['id_centro'] : 0;
    $nom_farmacia = isset($obj['id_centro']) ? $obj['nom_centro'] : '';

    $sql = "SELECT far_orden_egreso_tipo.id_tipo_egreso,far_orden_egreso_tipo.nom_tipo_egreso,far_orden_egreso_tipo.consumo,
                SUM(far_orden_egreso.val_total) AS val_total_te
            FROM far_orden_egreso
            INNER JOIN far_orden_egreso_tipo ON (far_orden_egreso_tipo.id_tipo_egreso=far_orden_egreso.id_tipo_egreso)
            $where 
            GROUP BY far_orden_egreso_tipo.id_tipo_egreso
            ORDER BY far_orden_egreso_tipo.id_tipo_egreso";
    $res = $cmd->query($sql);
    $objs = $res->fetchAll();

    $sql = "SELECT tb_sedes.id_sede,tb_sedes.nom_sede,far_bodegas.id_bodega,far_bodegas.nombre AS nom_bodega,
                SUM(far_orden_egreso.val_total) AS val_total_sb
            FROM far_orden_egreso
            INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_orden_egreso.id_sede)
            INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_orden_egreso.id_bodega)
            $where AND far_orden_egreso.id_tipo_egreso=:id_tipo_egreso
            GROUP BY tb_sedes.id_sede,far_bodegas.id_bodega
            ORDER BY tb_sedes.id_sede,far_bodegas.nombre";
    $rs_b = $cmd->prepare($sql);

    $sql = "SELECT IF(far_orden_egreso.id_centrocosto=0,$id_farmacia,tb_centrocostos.id_centro) AS id_centro,
                IF(far_orden_egreso.id_centrocosto=0,'$nom_farmacia',tb_centrocostos.nom_centro) AS nom_centro, 
                far_subgrupos.id_subgrupo,CONCAT_WS(' - ',far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo) AS nom_subgrupo,                               
                SUM(far_orden_egreso_detalle.cantidad*far_orden_egreso_detalle.valor) AS val_total_sg
            FROM far_orden_egreso_detalle
            INNER JOIN far_orden_egreso ON (far_orden_egreso.id_egreso=far_orden_egreso_detalle.id_egreso)
            INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_orden_egreso_detalle.id_lote)
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            INNER JOIN tb_centrocostos ON (tb_centrocostos.id_centro=far_orden_egreso.id_centrocosto)
            $where AND far_orden_egreso.id_tipo_egreso=:id_tipo_egreso 
                   AND far_orden_egreso.id_sede=:id_sede AND far_orden_egreso.id_bodega=:id_bodega
            GROUP BY IF(far_orden_egreso.id_centrocosto=0,$id_farmacia,tb_centrocostos.id_centro),far_subgrupos.id_subgrupo
            ORDER BY IF(far_orden_egreso.id_centrocosto=0,$id_farmacia,tb_centrocostos.id_centro),far_subgrupos.id_subgrupo";
    $rs_d = $cmd->prepare($sql);

    // Gasto - Cuenta del Subgrupo por Centro de Costo - Egresos de Consumo
    $sql = "SELECT CTA.cuenta
            FROM tb_centrocostos_subgr_cta_detalle AS CSG
            INNER JOIN ctb_pgcp AS CTA ON (CTA.id_pgcp=CSG.id_cuenta)
            WHERE CSG.id_cecsubgrp=(SELECT id_cecsubgrp AS id FROM tb_centrocostos_subgr_cta
                                    WHERE estado=1 AND fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND id_cencos=:id_cencos
                                    ORDER BY fecha_vigencia DESC LIMIT 1)
                AND CSG.id_subgrupo=:id_subgrupo";        
    $rs_ccos = $cmd->prepare($sql);

    // Gasto - Cuenta del Tipo de Egreso - Otro tipo de Egresos
    $sql = "SELECT CACT.cuenta
            FROM far_orden_egreso_tipo_cta AS TEGR
            INNER JOIN ctb_pgcp AS CACT ON (CACT.id_pgcp=TEGR.id_cuenta)            
            WHERE TEGR.estado=1 AND TEGR.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND TEGR.id_tipo_egreso=:id_tipo_egreso
            ORDER BY TEGR.fecha_vigencia DESC LIMIT 1";
    $rs_tegr = $cmd->prepare($sql);

    //Cuenta contable de Subgrupo
    $sql = "SELECT CACT.cuenta
            FROM far_subgrupos_cta AS SBG
            INNER JOIN ctb_pgcp AS CACT ON (CACT.id_pgcp=SBG.id_cuenta)            
            WHERE SBG.estado=1 AND SBG.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND SBG.id_subgrupo=:id_subgrupo
            ORDER BY SBG.fecha_vigencia DESC LIMIT 1";
    $rs_subg = $cmd->prepare($sql);

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
            <th><?php echo $titulo; ?></th>
        </tr>     
    </table>

    <table style="width:100% !important">        
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            switch($id_reporte){
                case '5':
                    $tabla = '<tr style="background-color:#CED3D3">
                        <th>Cuenta</th><th>Centro de Costo</th><th>Cuenta</th><th>Subgrupo</th><th>Vr. Parcial</th><th>Vr. Total</th></tr>';
                    break;  
            }

            $total = 0;
            $numreg = 0;

            foreach ($objs as $obj1) {
                $id_tipo_egreso = $obj1['id_tipo_egreso'];
                $consumo = $obj1['consumo'];
                
                $tabla .= '<tr><th colspan="5" style="text-align:left">TIPO DE EGRESO : ' . mb_strtoupper($obj1['nom_tipo_egreso']) . '</th>
                            <th style="text-align:right">' . formato_valor($obj1['val_total_te']) . '</th></tr>';

                $rs_b->bindParam(':id_tipo_egreso',$id_tipo_egreso);
                $rs_b->execute();
                $objb = $rs_b->fetchAll();

                foreach ($objb as $obj2) {
                    $id_sede = $obj2['id_sede'];
                    $id_bodega = $obj2['id_bodega'];
                
                    $tabla .= '<tr><th style="text-align:left">' . str_repeat('&nbsp',10) . mb_strtoupper($obj2['nom_sede'] . ' - ' . $obj2['nom_bodega']) . '</th>
                                <th colspan="4" style="text-align:right">' . formato_valor($obj2['val_total_sb']) . '</th></tr>';

                    $rs_d->bindParam(':id_tipo_egreso',$id_tipo_egreso);
                    $rs_d->bindParam(':id_sede',$id_sede);
                    $rs_d->bindParam(':id_bodega',$id_bodega);
                    $rs_d->execute();
                    $objd = $rs_d->fetchAll();

                    foreach ($objd as $obj) {
                        $id_cencos = $obj['id_centro'];
                        $id_subgrupo = $obj['id_subgrupo'];
                        $cuenta_con = "";

                        if ($consumo == 1){
                            $rs_ccos->bindParam(':id_cencos',$id_cencos);
                            $rs_ccos->bindParam(':id_subgrupo',$id_subgrupo);
                            $rs_ccos->execute();
                            $obj_ccos = $rs_ccos->fetch();
                            $cuenta_con = isset($obj_ccos['cuenta']) ? $obj_ccos['cuenta'] : '';
                        } else {
                            $rs_tegr->bindParam(':id_tipo_egreso',$id_tipo_egreso);
                            $rs_tegr->execute();
                            $obj_tegr = $rs_tegr->fetch();
                            $cuenta_con = isset($obj_tegr['cuenta']) ? $obj_tegr['cuenta'] : '';    
                        }    
                        
                        $rs_subg->bindParam(':id_subgrupo',$id_subgrupo);
                        $rs_subg->execute();
                        $obj_subg = $rs_subg->fetch();
                        $cuenta_subg = isset($obj_subg['cuenta']) ? $obj_subg['cuenta'] : '';

                        $tabla .=  '<tr class="resaltar">
                            <td style="text-align:left">' . str_repeat('&nbsp',20) . $cuenta_con . '</td>
                            <td style="text-align:left">' . mb_strtoupper($obj['nom_centro']) . '</td>
                            <td style="text-align:left">' . $cuenta_subg . '</td>
                            <td style="text-align:left">' . mb_strtoupper($obj['nom_subgrupo']) . '</td>
                            <td style="text-align:right">' . formato_valor($obj['val_total_sg']) . '</td></tr>'; 

                        $total += $obj['val_total_sg'];
                        $numreg += 1;
                    }   
                }      
            }    
            echo $tabla;                   
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <th colspan="4" style="text-align:left">
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