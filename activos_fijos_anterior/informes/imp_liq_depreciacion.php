<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
function calcularDV($nit)
{
    if (!is_numeric($nit)) {
        return false;
    }

    $arr = array(
        1 => 3, 4 => 17, 7 => 29, 10 => 43, 13 => 59, 2 => 7, 5 => 19,
        8 => 37, 11 => 47, 14 => 67, 3 => 13, 6 => 23, 9 => 41, 12 => 53, 15 => 71
    );
    $x = 0;
    $y = 0;
    $z = strlen($nit);
    $dv = '';

    for ($i = 0; $i < $z; $i++) {
        $y = substr($nit, $i, 1);
        $x += ($y * $arr[$z - $i]);
    }

    $y = $x % 11;

    if ($y > 1) {
        $dv = 11 - $y;
        return $dv;
    } else {
        $dv = $y;
        return $dv;
    }
}
include '../../conexion.php';
$mes = $_POST['mes'];
$vigencia = $_SESSION['vigencia'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT `codigo`,`nom_mes`,`fin_mes` FROM `nom_meses` WHERE `codigo` = $mes";
    $res = $cmd->query($sql);
    $dataMes = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios_sistema`
            WHERE (`id_usuario` = $_SESSION[id_user])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_usuarios_sistema`.`documento`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`
                , `seg_usuarios_sistema`.`apellido2`) AS `responsable`
                , `seg_bodega_almacen`.`nombre`
                , `seg_responsable_bodega`.`id_bodega`
            FROM
                `seg_responsable_bodega`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_responsable_bodega`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_usuarios_sistema` 
                    ON (`seg_responsable_bodega`.`id_usuario` = `seg_usuarios_sistema`.`id_usuario`)
            WHERE `seg_responsable_bodega`.`id_bodega` = 1";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `ctt_bien_servicio`.`id_tipo_bn_sv`
                , `tb_tipo_bien_servicio`.`tipo_bn_sv`
                , `seg_entra_detalle_activos_fijos`.`id_prod`
                , `ctt_bien_servicio`.`bien_servicio`
                , `seg_num_serial`.`num_serial`
                , `seg_num_serial`.`placa`
                , `nom_liq_depreciacion`.`dias`
                , `nom_liq_depreciacion`.`val_depreciado`
                , `nom_liq_depreciacion`.`fecha`
                , `nom_liq_depreciacion`.`mes`
                , `nom_liq_depreciacion`.`anio`
            FROM
                `nom_liq_depreciacion`
                INNER JOIN `seg_num_serial` 
                    ON (`nom_liq_depreciacion`.`id_serial` = `seg_num_serial`.`id_serial`)
                INNER JOIN `seg_entra_detalle_activos_fijos` 
                    ON (`seg_num_serial`.`id_activo_fijo` = `seg_entra_detalle_activos_fijos`.`id_acfijo`)
                INNER JOIN `ctt_bien_servicio` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_prod` = `ctt_bien_servicio`.`id_b_s`)
                INNER JOIN `tb_tipo_bien_servicio` 
                    ON (`ctt_bien_servicio`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE (`nom_liq_depreciacion`.`mes` = '$mes' AND `nom_liq_depreciacion`.`anio` = '$vigencia')
            ORDER BY `tb_tipo_bien_servicio`.`tipo_bn_sv` ASC, `ctt_bien_servicio`.`bien_servicio` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$data = [];
$total = 0;
foreach ($datos as $dt) {
    $tipo = $dt['tipo_bn_sv'];
    $bien = $dt['bien_servicio'];
    $data[$tipo][$bien][] = [
        'id_prod' => $dt['id_prod'],
        'num_serial' => $dt['num_serial'],
        'placa' => $dt['placa'],
        'dias' => $dt['dias'],
        'val_depreciado' => $dt['val_depreciado'],
        'fecha' => $dt['fecha'],
        'id_tipo' => $dt['id_tipo_bn_sv']
    ];
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
                            <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                            <td colspan="9" style="text-align:center">
                                <header><strong><?php echo $empresa['nombre']; ?> </strong></header>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="9" style="text-align:center">
                                NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align:left">
                                PERIODO DEPRECIADO: <?php echo $vigencia . '-' . $mes . '-01 A ' . $vigencia . '-' . $mes . '-' . $dataMes['fin_mes']; ?>
                            </td>
                            <td colspan="2" style="text-align:left">
                                MES: <?php echo $dataMes['nom_mes']; ?>
                            </td>
                            <td colspan="2" style="text-align:right">
                                Fec. Imp.: <?php echo date('Y/m/d'); ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" style="text-align:center">
                                DEPRECIACIÓN DE ACTIVOS FIJOS
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background-color: #CED3D3; text-align:center">
                <th>ID</th>
                <th>Producto</th>
                <th>Serial</th>
                <th>Placa</th>
                <th>Dias</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $totalaf = 0;
            $row_tipo = '';
            foreach ($data as $tipo => $bien) {
                $totalTipo = 0;
                $row_bien = '';
                foreach ($bien as $key => $placa) {
                    $status = false;
                    $row_placa = '';
                    $total_bien = 0;
                    if (count($placa) > 1) {
                        foreach ($placa as $pl) {
                            $row_placa .= '<tr class="resaltar" style="text-align:left">
                                <td></td>
                                <td></td>
                                <td>' . $pl['num_serial'] . '</td>
                                <td>' . $pl['placa'] . '</td>
                                <td>' . $pl['dias'] . '</td>
                                <td style="text-align:right">' . pesos($pl['val_depreciado']) . '</td>
                            </tr>';
                            $total_bien += $pl['val_depreciado'];
                            $totalTipo += $pl['val_depreciado'];
                            $status = true;
                            $id_tipo = $pl['id_tipo'];
                        }
                    } else {
                        $row_placa .= '<tr class="resaltar" style="text-align:left">
                            <td>' . $placa[0]['id_prod'] . '</td>
                            <td>' . $key . '</td>
                            <td>' . $placa[0]['num_serial'] . '</td>
                            <td>' . $placa[0]['placa'] . '</td>
                            <td>' . $placa[0]['dias'] . '</td>
                            <td style="text-align:right">' . pesos($placa[0]['val_depreciado']) . '</td>
                        </tr>';
                        $totalTipo += $placa[0]['val_depreciado'];
                        $id_tipo = $placa[0]['id_tipo'];
                    }
                    if ($status) {
                        $row_bien .= '<tr class="resaltar" style="text-align:left">
                            <td>' . $placa[0]['id_prod'] . '</td>
                            <td>' . $key . '</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right">' . pesos($total_bien) . '</td>
                        </tr>' . $row_placa;
                    } else {
                        $row_bien .= $row_placa;
                    }
                }
                $totalaf += $totalTipo;
                $row_tipo .= '<tr class="resaltar">
                    <th style="text-align:left">' . $id_tipo . '</th>
                    <th colspan="4" style="text-align:left">' . $tipo . '</th>
                    <th style="text-align:right">' . pesos($totalTipo) . '</th>
                </tr>' . $row_bien;
            }
            $tabla = '<tr style="font-size: 12px;" class="resaltar">
                <th style="text-align: left;" colspan="5">ACTIVOS FIJOS</th>
                <th style="text-align: right;" colspan="1">' . pesos(round($totalaf)) . '</th>
            </tr>' . $row_tipo;
            echo $tabla;
            ?>
            <tr>
                <td colspan="10" style="height: 30px;"></td>
            </tr>
            <tr>
                <td colspan="10">
                    <table style="width: 100%;">
                        <tr>
                            <td colspan="2">
                                Elaboró:
                            </td>
                            <td colspan="3">
                                _____________________________________________
                            </td>
                            <td colspan="2">
                                Recibido por:
                            </td>
                            <td colspan="3">
                                _____________________________________________
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                Nombre:
                            </td>
                            <td colspan="3">
                                <?php echo mb_strtoupper($usuario['nombre']); ?>
                            </td>
                            <td colspan="2">
                                Nombre:
                            </td>
                            <td colspan="3">
                                <?php echo mb_strtoupper($responsable['responsable']); ?>
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