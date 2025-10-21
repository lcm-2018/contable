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

$where = "WHERE id_tipo_egreso<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND nom_tipo_egreso LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $sql = "SELECT id_tipo_egreso,nom_tipo_egreso,
                IF(es_int_ext=1,'Interno','Externo') AS es_int_ext,
                IF(con_pedido=1,'SI','') AS con_pedido,
                IF(dev_fianza=1,'SI','') AS dev_fianza,
                IF(consumo=1,'SI','') AS consumo,
                IF(farmacia=1,'SI','') AS farmacia,
                IF(almacen=1,'SI','') AS almacen,
                IF(activofijo=1,'SI','') AS activofijo
            FROM far_orden_egreso_tipo
            $where ORDER BY id_tipo_egreso DESC";
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
            <th>REPORTE DE TIPOS DE ORDEN DE EGRESO</th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Id</th>
                <th>Nombre</th>                                        
                <th>Cuenta Contable Vigenete</th>                                        
                <th>Int/Ext</th>
                <th>Con Pedido</th>
                <th>Dev. Fianza</th>
                <th>De Consumo</th>
                <th>Farmacia</th>
                <th>Almacén</th>
                <th>Activo Fijo</th>                
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {

                $sql = "SELECT ctb_pgcp.cuenta
                        FROM far_orden_egreso_tipo_cta AS TOEGR
                        INNER JOIN ctb_pgcp ON (ctb_pgcp.id_pgcp=TOEGR.id_cuenta)            
                        WHERE TOEGR.estado=1 AND TOEGR.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d') AND TOEGR.id_tipo_egreso=" . $obj['id_tipo_egreso'] . "
                        ORDER BY TOEGR.fecha_vigencia DESC LIMIT 1";
                $rs = $cmd->query($sql);
                $obj_cta = $rs->fetch();
                $cuenta_c = isset($obj_cta['cuenta']) ? $obj_cta['cuenta'] : '';
        
                $tabla .=  
                    '<tr class="resaltar" style="text-align:left"> 
                        <td>' . $obj['id_tipo_egreso'] . '</td>
                        <td>' . $obj['nom_tipo_egreso'] . '</td>
                        <td>' . $cuenta_c . '</td>
                        <td>' . $obj['es_int_ext'] . '</td>
                        <td>' . $obj['con_pedido'] . '</td>
                        <td>' . $obj['dev_fianza'] . '</td>
                        <td>' . $obj['consumo'] . '</td>
                        <td>' . $obj['farmacia'] . '</td>
                        <td>' . $obj['almacen'] . '</td>
                        <td>' . $obj['activofijo'] . '</td></tr>';
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