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
                c_presup.cod_pptal AS cta_presupuesto,
                c_debito.cuenta AS cta_debito,
                c_credito.cuenta AS cta_credito,
                c_copago.cuenta AS cta_copago,
                c_glindeb.cuenta AS cta_glosaini_debito,
                c_glincre.cuenta AS cta_glosaini_credito,
                c_gldef.cuenta AS cta_glosadefinitiva,
                c_devol.cuenta AS cta_devolucion,
                c_caja.cuenta AS cta_caja,                
	            IF(tb_homologacion.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM tb_homologacion
            INNER JOIN tb_regimenes ON (tb_regimenes.id_regimen=tb_homologacion.id_regimen)
            INNER JOIN tb_cobertura ON (tb_cobertura.id_cobertura=tb_homologacion.id_cobertura)
            INNER JOIN tb_modalidad ON (tb_modalidad.id_modalidad=tb_homologacion.id_modalidad)
            LEFT JOIN pto_cargue  AS c_presup ON (c_presup.id_cargue=tb_homologacion.id_cta_presupuesto)
            LEFT JOIN ctb_pgcp AS c_debito ON (c_debito.id_pgcp=tb_homologacion.id_cta_debito)
            LEFT JOIN ctb_pgcp AS c_credito ON (c_credito.id_pgcp=tb_homologacion.id_cta_credito)
            LEFT JOIN ctb_pgcp AS c_copago ON (c_copago.id_pgcp=tb_homologacion.id_cta_copago)
            LEFT JOIN ctb_pgcp AS c_glindeb ON (c_glindeb.id_pgcp=tb_homologacion.id_cta_glosaini_debito)
            LEFT JOIN ctb_pgcp AS c_glincre ON (c_glincre.id_pgcp=tb_homologacion.id_cta_glosaini_credito)
            LEFT JOIN ctb_pgcp AS c_gldef ON (c_gldef.id_pgcp=tb_homologacion.id_cta_glosadefinitiva)
            LEFT JOIN ctb_pgcp AS c_devol ON (c_devol.id_pgcp=tb_homologacion.id_cta_devolucion)
            LEFT JOIN ctb_pgcp AS c_caja ON (c_caja.id_pgcp=tb_homologacion.id_cta_caja)
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
            <th>REPORTE DE CUENTAS DE FACTURACIÓN</th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th rowspan="2">Id</th>
                <th rowspan="2">Régimen</th>
                <th rowspan="2">Cobertura</th>
                <th rowspan="2">Modadlidad</th>
                <th rowspan="2">Fecha Inicio de Vigencia</th>
                <th colspan="9">Cuentas</th>
                <th rowspan="2">Estado</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Presup.</th>
                <th>Debito</th>
                <th>Credito</th>
                <th>Copago</th>
                <th>GI_deb.</th>
                <th>GI_cre.</th>
                <th>G_def.</th>
                <th>Devol.</th>
                <th>Caja</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_homo'] . '</td>
                        <td style="text-align:left">' . $obj['nom_regimen'] . '</td>
                        <td style="text-align:left">' . $obj['nom_cobertura'] . '</td>
                        <td style="text-align:left">' . $obj['nom_modalidad'] . '</td>
                        <td>' . $obj['fecha_vigencia'] . '</td>
                        <td>' . $obj['cta_presupuesto'] . '</td>
                        <td>' . $obj['cta_debito'] . '</td>
                        <td>' . $obj['cta_credito'] . '</td>
                        <td>' . $obj['cta_copago'] . '</td>
                        <td>' . $obj['cta_glosaini_debito'] . '</td>
                        <td>' . $obj['cta_glosaini_credito'] . '</td>
                        <td>' . $obj['cta_glosadefinitiva'] . '</td>
                        <td>' . $obj['cta_devolucion'] . '</td>
                        <td>' . $obj['cta_caja'] . '</td>
                        <td>' . $obj['estado'] . '</td></tr>';              
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="15" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>