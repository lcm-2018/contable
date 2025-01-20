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

$where = "WHERE tb_homologacion.id_homo<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND (tb_regimenes.descripcion_reg LIKE '" . $_POST['nombre'] . "%' OR 
                    tb_cobertura.nom_cobertura LIKE '" . $_POST['nombre'] . "%' OR 
                    tb_modalidad.nom_modalidad LIKE '" . $_POST['nombre'] . "%')";
}

try {
    $sql = "SELECT tb_homologacion.id_homo,tb_regimenes.descripcion_reg AS nom_regimen,
	            tb_cobertura.nom_cobertura,tb_modalidad.nom_modalidad,tb_homologacion.fecha_vigencia,
                IF(c_presto.cod_pptal IS NULL,'',CONCAT_WS(' - ',c_presto.cod_pptal,c_presto.nom_rubro)) AS cta_presupuesto,
                IF(c_presto_ant.cod_pptal IS NULL,'',CONCAT_WS(' - ',c_presto_ant.cod_pptal,c_presto_ant.nom_rubro)) AS cta_presupuesto_ant,
                IF(c_debito.cuenta IS NULL,'',CONCAT_WS(' - ',c_debito.cuenta,c_debito.nombre)) AS cta_debito,
                IF(c_credito.cuenta IS NULL,'',CONCAT_WS(' - ',c_credito.cuenta,c_credito.nombre)) AS cta_credito,
                IF(c_copago.cuenta IS NULL,'',CONCAT_WS(' - ',c_copago.cuenta,c_copago.nombre)) AS cta_copago,
                IF(c_copago_cap.cuenta IS NULL,'',CONCAT_WS(' - ',c_copago_cap.cuenta,c_copago_cap.nombre)) AS cta_copago_capitado,
                IF(c_gloini_deb.cuenta IS NULL,'',CONCAT_WS(' - ',c_gloini_deb.cuenta,c_gloini_deb.nombre)) AS cta_glosaini_debito,
                IF(c_gloini_cre.cuenta IS NULL,'',CONCAT_WS(' - ',c_gloini_cre.cuenta,c_gloini_cre.nombre)) AS cta_glosaini_credito,
                IF(c_glo_def.cuenta IS NULL,'',CONCAT_WS(' - ',c_glo_def.cuenta,c_glo_def.nombre)) AS cta_glosadefinitiva,
                IF(c_devol.cuenta IS NULL,'',CONCAT_WS(' - ',c_devol.cuenta,c_devol.nombre)) AS cta_devolucion,            
                IF(c_caja.cuenta IS NULL,'',CONCAT_WS(' - ',c_caja.cuenta,c_caja.nombre)) AS cta_caja,
                IF(c_fac_glo.cuenta IS NULL,'',CONCAT_WS(' - ',c_fac_glo.cuenta,c_fac_glo.nombre)) AS cta_fac_global,
                IF(c_x_ide.cuenta IS NULL,'',CONCAT_WS(' - ',c_x_ide.cuenta,c_x_ide.nombre)) AS cta_x_ident,
	            IF(tb_homologacion.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM tb_homologacion
            INNER JOIN tb_regimenes ON (tb_regimenes.id_regimen=tb_homologacion.id_regimen)
            INNER JOIN tb_cobertura ON (tb_cobertura.id_cobertura=tb_homologacion.id_cobertura)
            INNER JOIN tb_modalidad ON (tb_modalidad.id_modalidad=tb_homologacion.id_modalidad)
            LEFT JOIN pto_cargue  AS c_presto ON (c_presto.id_cargue=tb_homologacion.id_cta_presupuesto)
            LEFT JOIN pto_cargue  AS c_presto_ant ON (c_presto_ant.id_cargue=tb_homologacion.id_cta_presupuesto_ant)
            LEFT JOIN ctb_pgcp AS c_debito ON (c_debito.id_pgcp=tb_homologacion.id_cta_debito)
            LEFT JOIN ctb_pgcp AS c_credito ON (c_credito.id_pgcp=tb_homologacion.id_cta_credito)
            LEFT JOIN ctb_pgcp AS c_copago ON (c_copago.id_pgcp=tb_homologacion.id_cta_copago)
            LEFT JOIN ctb_pgcp AS c_copago_cap ON (c_copago_cap.id_pgcp=tb_homologacion.id_cta_copago_capitado)
            LEFT JOIN ctb_pgcp AS c_gloini_deb ON (c_gloini_deb.id_pgcp=tb_homologacion.id_cta_glosaini_debito)
            LEFT JOIN ctb_pgcp AS c_gloini_cre ON (c_gloini_cre.id_pgcp=tb_homologacion.id_cta_glosaini_credito)
            LEFT JOIN ctb_pgcp AS c_glo_def ON (c_glo_def.id_pgcp=tb_homologacion.id_cta_glosadefinitiva)        
            LEFT JOIN ctb_pgcp AS c_devol ON (c_devol.id_pgcp=tb_homologacion.id_cta_devolucion)
            LEFT JOIN ctb_pgcp AS c_caja ON (c_caja.id_pgcp=tb_homologacion.id_cta_caja)
            LEFT JOIN ctb_pgcp AS c_fac_glo ON (c_fac_glo.id_pgcp=tb_homologacion.id_cta_fac_global)
            LEFT JOIN ctb_pgcp AS c_x_ide ON (c_x_ide.id_pgcp=tb_homologacion.id_cta_x_ident)
            $where ORDER BY tb_homologacion.id_homo DESC";
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
            <th>REPORTE DE CUENTAS CONTABLES DE FACTURACIÓN</th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:left">
                <th>Id</th>
                <th>Régimen</th>
                <th>Cobertura</th>
                <th>Modadlidad</th>
                <th>Fecha Inicio de Vigencia</th>                
                <th>Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  
                    '<tr class="resaltar" style="text-align:left"> 
                        <td>' . $obj['id_homo'] . '</td>
                        <td>' . $obj['nom_regimen'] . '</td>
                        <td>' . $obj['nom_cobertura'] . '</td>
                        <td>' . $obj['nom_modalidad'] . '</td>
                        <td>' . $obj['fecha_vigencia'] . '</td>
                        <td>' . $obj['estado'] . '</td></tr>                             
                    <tr class="resaltar" style="text-align:left">
                        <td colspan="6">
                            <table>    
                                <tr>
                                    <td>Cta. Presupuesto:</td>
                                    <td>' . $obj['cta_presupuesto'] . '</td>
                                </tr>
                                    <td>Cta. Presupuesto Anterior:</td>
                                    <td>' . $obj['cta_presupuesto_ant'] . '</td>
                                </tr>
                                    <td>Cta. Débito:</td>                                
                                    <td>' . $obj['cta_debito'] . '</td>
                                </tr>
                                    <td>Cta. Crédito:</td>
                                    <td>' . $obj['cta_credito'] . '</td>
                                </tr>    
                                    <td>Cta. Copago:</td>
                                    <td>' . $obj['cta_copago'] . '</td>
                                </tr>
                                    <td>Cta. Copago Capitado:</td>
                                    <td>' . $obj['cta_copago_capitado'] . '</td>
                                </tr>
                                    <td>Cta. Glosa Inicial Débito:</td>
                                    <td>' . $obj['cta_glosaini_debito'] . '</td>
                                </tr>
                                    <td>Cta. Glosa Inicial Crédito:</td>
                                    <td>' . $obj['cta_glosaini_credito'] . '</td>
                                </tr>
                                    <td>Cta. Glosa Definitiva:</td>
                                    <td>' . $obj['cta_glosadefinitiva'] . '</td>
                                </tr>
                                    <td>Cta. Devolución:</td>
                                    <td>' . $obj['cta_devolucion'] . '</td>
                                </tr>
                                    <td>Cta. Caja:</td>
                                    <td>' . $obj['cta_caja'] . '</td>
                                </tr>
                                    <td>Cta. Factura Global:</td>
                                    <td>' . $obj['cta_fac_global'] . '</td>
                                </tr>
                                    <td>Cta. Por Identificar:</td>
                                    <td>' . $obj['cta_x_ident'] . '</td>
                                </tr>
                            </table>
                        </td>  
                    </tr>';      
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="7" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>