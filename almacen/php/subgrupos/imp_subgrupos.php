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
    <table style="width:100% !important; border-collapse: collapse;">
        <thead style="background-color: white !important;font-size:80%">
            <tr style="padding: bottom 3px; color:black">
                <td colspan="5">
                    <table style="width:100% !important;">
                        <tr>
                            <td rowspan="5" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                            <td colspan="5" style="text-align:center">
                                <header><b><?php echo $empresa['nombre']; ?> </b></header>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align:center">
                                NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align:right">
                                Fec. Imp.: <?php echo date('Y/m/d'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align:center">
                                <b>SUBGRUPOS ARTICULOS</b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background-color: #CED3D3; text-align:center">
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
                        <td>' . $obj['estado'] . '</td>                                                         
                    </tr>';
            }
            echo $tabla;
            ?>
            <tr>
                <td colspan="5" style="height: 30px;"></td>
            </tr>
            <tr>
                <td colspan="5">
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="5" style="text-align:left">
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