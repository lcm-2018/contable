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
$user = $_SESSION['user'];

// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT razon_social_ips as nombre ,nit_ips as nit,dv as dig_ver FROM tb_datos_ips";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$where = "WHERE far_orden_egreso.id_egreso<>0";

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
if (isset($_POST['id_tercero']) && $_POST['id_tercero']) {
    $where .= " AND far_orden_egreso.id_cliente=" . $_POST['id_tercero'] . "";
}
if (isset($_POST['id_depende']) && $_POST['id_depende']) {
    $where .= " AND far_orden_egreso.id_dependencia=" . $_POST['id_depende'] . "";
}
if (isset($_POST['id_tipegr']) && $_POST['id_tipegr']) {
    $where .= " AND far_orden_egreso.id_tipo_egreso=" . $_POST['id_tipegr'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_orden_egreso.estado=" . $_POST['estado'];
}

try {
    $sql = "SELECT far_orden_egreso.id_egreso,far_orden_egreso.num_egreso,far_orden_egreso.fec_egreso,far_orden_egreso.hor_egreso,
                    far_orden_egreso.detalle,tb_terceros.nom_tercero,tb_dependencias.nom_dependencia,
                    far_orden_egreso_tipo.nom_tipo_egreso,far_orden_egreso.val_total,tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,
                    CASE far_orden_egreso.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado
                    FROM far_orden_egreso
                    INNER JOIN far_orden_egreso_tipo ON (far_orden_egreso_tipo.id_tipo_egreso=far_orden_egreso.id_tipo_egreso)
                    INNER JOIN tb_terceros ON (tb_terceros.id_tercero=far_orden_egreso.id_cliente)
                    INNER JOIN tb_dependencias ON (tb_dependencias.id_dependencia=far_orden_egreso.id_dependencia)
                    INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_orden_egreso.id_sede)
                    INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_orden_egreso.id_bodega)$where ORDER BY far_orden_egreso.id_egreso DESC";
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
    <table style="width:100% !important; border-collapse: collapse;">
        <thead style="background-color: white !important;font-size:80%">
            <tr style="padding: bottom 3px; color:black">
                <td colspan="12">
                    <table style="width:100% !important;">
                        <tr>
                            <td rowspan="5" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                            <td colspan="12" style="text-align:center">
                                <header><b><?php echo $empresa['nombre']; ?> </b></header>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="12" style="text-align:center">
                                NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="12" style="text-align:right">
                                Fec. Imp.: <?php echo date('Y/m/d'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="12" style="text-align:center">
                                <b>EGRESOS</b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background-color: #CED3D3; text-align:center">
            <th>Id</th>
            <th>No. Egreso</th>
            <th>Fecha Egreso</th>
            <th>Hora Egreso</th>
            <th>Detalle</th>
            <th>Tercero</th>
            <th>Dependencia</th>
            <th>Tipo Egreso</th>
            <th>Vr. Total</th>
            <th>Sede</th>
            <th>Bodega</th>
            <th>Estado</th>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
            $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_egreso'] . '</td>  
                        <td>' . $obj['num_egreso'] . '</td>
                        <td>' . $obj['fec_egreso'] . '</td>
                        <td>' . $obj['hor_egreso'] . '</td>                  
                        <td>' . $obj['detalle'] . '</td>                      
                        <td>' . mb_strtoupper($obj['nom_tercero']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_dependencia']) . '</td> 
                        <td>' . mb_strtoupper($obj['nom_tipo_egreso']) . '</td>   
                        <td>' . formato_valor($obj['val_total']). '</td>                             
                        <td>' . mb_strtoupper($obj['nom_sede']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_bodega']) . '</td>   
                        <td>' . $obj['nom_estado']. '</td>                                                                                     
                    </tr>';
            }
            echo $tabla;
            ?>
            <tr>
                <td colspan="2" style="height: 30px;"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="2" style="text-align:left">
                                Usuario: <?php echo mb_strtoupper($user); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="n">
                    <div class="footer">
                        <div class="page-number"></div>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>