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

$where = "WHERE far_subgrupos.id_subgrupo<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_subgrupos.nom_subgrupo LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $sql = "SELECT far_subgrupos.id_subgrupo,far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo,far_grupos.nom_grupo,
                IF(far_subgrupos.estado=1,'ACTIVO','INACTIVO') AS estado
            FROM far_subgrupos
            INNER JOIN far_grupos ON (far_grupos.id_grupo=far_subgrupos.id_grupo) $where ORDER BY far_subgrupos.id_subgrupo DESC";
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

    <table style="width:100% !important; border-collapse: collapse;">
        <thead style="background-color: white !important;font-size:80%">            
            <tr style="background-color: #CED3D3; text-align:center; border:#A9A9A9 1px solid">
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
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_subgrupo'] . '</td>
                        <td>' . $obj['cod_subgrupo'] . '</td>
                        <td>' . $obj['nom_subgrupo'] . '</td>
                        <td>' . $obj['nom_grupo'] . '</td>
                        <td>' . $obj['estado'] . '</td></tr>';
            }
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="background-color:white !important; font-size:60%"> 
            <tr style="background-color:#CED3D3;">
                <td colspan="5" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>&nbsp;&nbsp;-&nbsp;&nbsp;
                    Usuario: <?php echo mb_strtoupper($_SESSION['user']); ?>     
                </td>
            </tr>
        </tfoot>
    </table>
</div>