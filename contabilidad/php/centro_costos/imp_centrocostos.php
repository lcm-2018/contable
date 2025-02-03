<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$where = "WHERE tb_centrocostos.id_centro<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND tb_centrocostos.nom_centro LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $sql = "SELECT tb_centrocostos.id_centro,tb_centrocostos.nom_centro,tb_centrocostos.cuenta,
            CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_respon
        FROM tb_centrocostos
        INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=tb_centrocostos.id_responsable) 
        $where ORDER BY tb_centrocostos.id_centro DESC";
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
            <th>REPORTE DE CENTROS DE COSTO</th>
        </tr>     
    </table>  

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Id</th>
                <th>Nombre</th>
                <th>Cuenta Contable Facturación</th>
                <th>Responsable</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php           
            $tabla = '';                                      
            foreach ($objs as $obj) {       
                
                $sql = "SELECT CONCAT_WS(' - ',ctb_pgcp.cuenta,ctb_pgcp.nombre) AS cuenta
                        FROM tb_centrocostos_cta    
                        INNER JOIN ctb_pgcp ON (ctb_pgcp.id_pgcp=tb_centrocostos_cta.id_cuenta)            
                        WHERE tb_centrocostos_cta.estado=1 AND tb_centrocostos_cta.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND tb_centrocostos_cta.id_cencos=" . $obj['id_centro'] . "
                        ORDER BY tb_centrocostos_cta.fecha_vigencia DESC LIMIT 1";
                $rs = $cmd->query($sql);
                $obj_cta = $rs->fetch();
                $cuenta = isset($obj_cta['cuenta']) ? $obj_cta['cuenta'] : '';
                
                $sql = "SELECT id_cecsubgrp AS id FROM tb_centrocostos_subgr_cta
                        WHERE estado=1 AND fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND id_cencos=" . $obj['id_centro'] . " 
                        ORDER BY fecha_vigencia DESC LIMIT 1";
                $rs = $cmd->query($sql);
                $obj_cta = $rs->fetch();
                $id = isset($obj_cta['id']) ? $obj_cta['id'] : -1;

                $sql = "SELECT SG.nom_subgrupo,C.cuenta
                        FROM far_subgrupos AS SG
                        LEFT JOIN (SELECT CSG.*,CONCAT_WS(' - ',CTA.cuenta,CTA.nombre) AS cuenta
                                FROM tb_centrocostos_subgr_cta_detalle AS CSG
                                INNER JOIN ctb_pgcp AS CTA ON (CTA.id_pgcp=CSG.id_cuenta)
                                WHERE CSG.id_cecsubgrp=" . $id .") AS C ON (C.id_subgrupo=SG.id_subgrupo)
                        WHERE SG.id_grupo IN (1,2) ORDER BY SG.id_subgrupo";
                $rs = $cmd->query($sql);
                $objs_ctas = $rs->fetchAll();

                $tabla .=  
                    '<tr class="resaltar" style="text-align:left"> 
                        <td>' . $obj['id_centro'] .'</td>                    
                        <td>' . mb_strtoupper($obj['nom_centro']). '</td>
                        <td>' . $cuenta .'</td>
                        <td>' . $obj['usr_respon'] .'</td>
                     <tr class="resaltar" style="text-align:left"> 
                        <td colspan="4">
                            <table>';
                            foreach ($objs_ctas as $cta){
                                $tabla .=
                                    '<tr> 
                                        <td>' . $cta['nom_subgrupo'] .':</td>                    
                                        <td>' . $cta['cuenta'] .'</td></tr>';
                            } 
                            $tabla .=
                                    '</table></td></tr>';
            }            
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="4" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>