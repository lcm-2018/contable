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

$where = " WHERE 1";
if (isset($_POST['codigo']) && $_POST['codigo']) {
    $where .= " AND far_medicamentos.cod_medicamento LIKE '" . $_POST['codigo'] . "%'";
}
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_medicamentos.nom_medicamento LIKE '" . $_POST['nombre'] . "%'";
}
if (isset($_POST['subgrupo']) && $_POST['subgrupo']) {
    $where .= " AND far_medicamentos.id_subgrupo=" . $_POST['subgrupo'];
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_medicamentos.estado=" . $_POST['estado'];
}

try {
    $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                    far_subgrupos.nom_subgrupo,far_medicamentos.top_min,far_medicamentos.top_max,
                    far_medicamentos.existencia,far_medicamentos.val_promedio,
                    IF(far_medicamentos.es_clinico=1,'SI','NO') AS es_clinico,
                    IF(far_medicamentos.estado=1,'ACTIVO','INACTIVO') AS estado
                FROM far_medicamentos
                INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo) $where ORDER BY far_medicamentos.id_med DESC ";
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
                <td colspan="10">
                    <table style="width:100% !important;">
                        <tr>
                            <td rowspan="5" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                            <td colspan="10" style="text-align:center">
                                <header><b><?php echo $empresa['nombre']; ?> </b></header>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" style="text-align:center">
                                NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" style="text-align:right">
                                Fec. Imp.: <?php echo date('Y/m/d'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" style="text-align:center">
                                <b>ARTICULOS KARDEX</b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background-color: #CED3D3; text-align:center">
            <th>Id</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Subgrupo</th>
            <th>Tope Mínimo</th>
            <th>Tope Máximo</th>
            <th>Existencia</th>
            <th>Vr. Promedio</th>
            <th>Es Clínico</th>
            <th>Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($objs as $obj) {
            $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                        <td>' . $obj['id_med'] . '</td>
                        <td>' . $obj['cod_medicamento'] . '</td>
                        <td>' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                        <td>' . mb_strtoupper($obj['nom_subgrupo']) . '</td>   
                        <td>' . $obj['top_min'] . '</td>   
                        <td>' . $obj['top_max'] . '</td>   
                        <td>' . $obj['existencia'] . '</td>   
                        <td>' . formato_valor($obj['val_promedio']) . '</td>   
                        <td>' . $obj['es_clinico'] . '</td>   
                        <td>' . $obj['estado'] . '</td>                                                            
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