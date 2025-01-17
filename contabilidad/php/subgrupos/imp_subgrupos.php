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

$where = "WHERE far_subgrupos.id_subgrupo<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_subgrupos.nom_subgrupo LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $sql = "SELECT far_subgrupos.id_subgrupo,far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo,far_grupos.id_grupo,far_grupos.nom_grupo,
                IF(far_subgrupos.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM far_subgrupos
            INNER JOIN far_grupos ON (far_grupos.id_grupo=far_subgrupos.id_grupo)
            $where ORDER BY far_subgrupos.id_subgrupo DESC";
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
            <th>REPORTE DE SUBGRUPOS</th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Id</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Grupo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {

                $sql = "SELECT CONCAT_WS(' - ',CACT.cuenta,CACT.nombre) AS cuenta
                        FROM far_subgrupos_cta AS SBG
                        INNER JOIN ctb_pgcp AS CACT ON (CACT.id_pgcp=SBG.id_cuenta)            
                        WHERE SBG.estado=1 AND SBG.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND SBG.id_subgrupo=" . $obj['id_subgrupo'] . "
                        ORDER BY SBG.fecha_vigencia DESC LIMIT 1";
                $rs = $cmd->query($sql);
                $objs_cta = $rs->fetch();
                $cuenta_cs = isset($objs_cta['cuenta']) ? $objs_cta['cuenta'] : '';

                $sql = "SELECT CONCAT_WS(' - ',CACT.cuenta,CACT.nombre) AS cuenta_af,
                            CONCAT_WS(' - ',CDEP.cuenta,CDEP.nombre) AS cuenta_dep,
                            CONCAT_WS(' - ',CGAS.cuenta,CGAS.nombre) AS cuenta_gas
                        FROM far_subgrupos_cta_af AS SBG
                        INNER JOIN ctb_pgcp AS CACT ON (CACT.id_pgcp=SBG.id_cuenta)
                        INNER JOIN ctb_pgcp AS CDEP ON (CDEP.id_pgcp=SBG.id_cuenta_dep)
                        INNER JOIN ctb_pgcp AS CGAS ON (CGAS.id_pgcp=SBG.id_cuenta_gas)
                        WHERE SBG.estado=1 AND SBG.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND SBG.id_subgrupo=" . $obj['id_subgrupo'] . "
                        ORDER BY SBG.fecha_vigencia DESC LIMIT 1";
                $rs = $cmd->query($sql);
                $objs_cta = $rs->fetch();
                $cuenta_af = isset($objs_cta['cuenta_af']) ? $objs_cta['cuenta_af'] : '';
                $cuenta_dep = isset($objs_cta['cuenta_dep']) ? $objs_cta['cuenta_dep'] : '';
                $cuenta_gas = isset($objs_cta['cuenta_gas']) ? $objs_cta['cuenta_gas'] : '';
                        
                $tabla .=  
                    '<tr class="resaltar" style="text-align:left"> 
                        <td>' . $obj['id_subgrupo'] . '</td>
                        <td>' . $obj['cod_subgrupo'] . '</td>
                        <td>' . $obj['nom_subgrupo'] . '</td>
                        <td>' . $obj['nom_grupo'] . '</td>
                        <td>' . $obj['estado'] . '</td></tr>
                    <tr class="resaltar" style="text-align:left"> 
                        <td colspan="5">
                            <table>';
                            if($obj['id_grupo'] == 1 || $obj['id_grupo'] == 2){  
                                $tabla .= 
                                    '<tr>    
                                       <td>Cta. Subgrupo de Consumo:</td><td>' . $cuenta_cs .'</td>
                                    </tr></table></td></tr>';
                            }else{        
                                $tabla .= 
                                    '<tr>    
                                        <td>Cta. Subgrupo Activo Fijo:</td><td> ' . $cuenta_af .'</td>
                                    </tr>
                                    <tr>        
                                        <td>Cta. Depreciación Activo Fijo:</td><td> ' . $cuenta_dep .'</td>
                                    </tr>
                                    <tr>        
                                        <td>Cta. Gasto Depreciación Activo Fijo:</td><td> ' . $cuenta_gas .'</td>
                                    </tr></table></td></tr>';
                            }        
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="5" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>