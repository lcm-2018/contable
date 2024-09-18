<?php
session_start();
set_time_limit(3600);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$vigencia = $_SESSION['vigencia'];
$dto = $_POST['id'];
$prefijo = '';
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../permisos.php';
include '../../financiero/consultas.php';
include '../../terceros.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$id_t = [];
try {
    $sql = "SELECT 
                `detalle`,`fecha`,`id_manu`,`id_tercero`,`fecha_reg`, `id_tipo_doc` AS `tipo_doc` 
            FROM `ctb_doc` 
            WHERE `id_ctb_doc` = $dto";
    $res = $cmd->query($sql);
    $doc = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t[] = $doc['id_tercero'] > 0 ? $doc['id_tercero'] : 0;
$id_tercero_gen = $doc['id_tercero'];
$num_doc = '';
// Valor total del cdp
try {
    $sql = "SELECT SUM(`debito`) as `valor` FROM `ctb_libaux` WHERE `id_ctb_doc` = $dto";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['valor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$enletras = numeroLetras($total);
try {
    $sql = "SELECT
                `ctb_doc`.`id_manu`
                , `pto_cargue`.`cod_pptal` AS `rubro`
                , `pto_cargue`.`nom_rubro`
                , `pto_cop_detalle`.`id_tercero_api`
                , `pto_cop_detalle`.`valor` -`pto_cop_detalle`.`valor_liberado` AS `valor`
                , 'COP' AS `tipo_mov`
            FROM
                `pto_cop_detalle`
                INNER JOIN `ctb_doc` 
                    ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $dto)";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
foreach ($rubros as $rp) {
    if ($rp['id_tercero_api'] != '') {
        $id_t[] = $rp['id_tercero_api'];
    }
}
// Datos de la factura 
try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_tipo_doc`.`tipo` AS `tipo_doc`
                , `ctb_fuente`.`nombre` AS `tipo`
                , `ctb_factura`.`num_doc`
                , `ctb_factura`.`fecha_fact`
                , `ctb_factura`.`fecha_ven`
                , `ctb_factura`.`valor_pago`
                , `ctb_factura`.`valor_iva`
                , `ctb_factura`.`valor_base`
            FROM
                `ctb_factura`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_factura`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                INNER JOIN `ctb_tipo_doc` 
                    ON (`ctb_factura`.`id_tipo_doc` = `ctb_tipo_doc`.`id_ctb_tipodoc`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $dto)";
    $res = $cmd->query($sql);
    $factura = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (empty($factura)) {
    $factura['id_ctb_doc'] = '';
    $factura['tipo_doc'] = '';
    $factura['tipo'] = '';
    $factura['num_doc'] = '';
    $factura['fecha_fact'] = date('Y-m-d');
    $factura['fecha_ven'] = date('Y-m-d');
    $factura['valor_pago'] = 0;
    $factura['valor_iva'] = 0;
    $factura['valor_base'] = 0;
}
// Movimiento contable
try {
    $sql = "SELECT
                `ctb_libaux`.`id_tercero_api` AS `id_tercero`
                , `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , `ctb_fuente`.`nombre` AS `fuente`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $dto)
            ORDER BY `ctb_pgcp`.`cuenta`,`ctb_pgcp`.`nombre` DESC";
    $res = $cmd->query($sql);
    $movimiento = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
foreach ($movimiento as $mov) {
    if ($mov['id_tercero'] != '') {
        $id_t[] = $mov['id_tercero'];
    }
}
// consulta para motrar cuadro de retenciones
try {
    $sql = "SELECT
                `ctb_causa_retencion`.`id_ctb_doc`
                , `ctb_causa_retencion`.`id_causa_retencion`
                , `ctb_retencion_tipo`.`tipo`
                , `ctb_retenciones`.`nombre_retencion`
                , `ctb_causa_retencion`.`valor_base`
                , `ctb_causa_retencion`.`tarifa`
                , `ctb_causa_retencion`.`valor_retencion`
                , `ctb_causa_retencion`.`id_terceroapi`
            FROM
                `ctb_retenciones`
                INNER JOIN `ctb_retencion_tipo` 
                    ON (`ctb_retenciones`.`id_retencion_tipo` = `ctb_retencion_tipo`.`id_retencion_tipo`)
                INNER JOIN `ctb_retencion_rango` 
                    ON (`ctb_retencion_rango`.`id_retencion` = `ctb_retenciones`.`id_retencion`)
                INNER JOIN `ctb_causa_retencion` 
                    ON (`ctb_causa_retencion`.`id_rango` = `ctb_retencion_rango`.`id_rango`)
            WHERE (`ctb_causa_retencion`.`id_ctb_doc` = $dto)";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
foreach ($retenciones as $ret) {
    if ($ret['id_terceroapi'] != '') {
        $id_t[] = $ret['id_terceroapi'];
    }
}
$terceros = [];
if (!empty($id_t)) {
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT `razon_social_ips` AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver` FROM `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el tipo de control del documento
try {
    $sql = "SELECT 
                `control_doc` , `nombre` , `id_proceso`
            FROM
                `fin_maestro_doc`";
    $res = $cmd->query($sql);
    $control = $res->fetch();
    $num_control = $control['control_doc'];
    $nombre_doc = $control['nombre'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el tipo de control del documento
try {
    $sql = "SELECT
                `fin_respon_doc`.`cargo`
                , `fin_respon_doc`.`tipo_control`
            FROM
                `fin_maestro_doc`
                LEFT JOIN `fin_respon_doc` 
                    ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)";
    $res = $cmd->query($sql);
    $firmas = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date('Y-m-d', strtotime($doc['fecha']));
$hora = date('H:i:s', strtotime($doc['fecha_reg']));
// fechas para factua
$fecha_fact = isset($factura['fecha_fact']) ? date('Y-m-d', strtotime($factura['fecha_fact'])) : '';
$fecha_ven = isset($factura['fecha_ven']) ? date('Y-m-d', strtotime($factura['fecha_ven'])) : '';
if ($empresa['nit'] == 844001355 && $factura['tipo_doc'] == 3) {
    $prefijo = 'RSC-';
}
?>
<div class="text-right py-3">
    <?php if (PermisosUsuario($permisos, 5501, 6)  || $id_rol == 1) { ?>
        <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecDoc('areaImprimir',<?php echo $dto; ?>);"> Imprimir</a>
    <?php } ?>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-center' style="width:18%"><label class="small"><img src="../images/logos/logo.png" width="100"></label></td>
                <td style="text-align:center">
                    <strong><?php echo $empresa['nombre']; ?> </strong>
                    <div>NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></div>
                </td>
            </tr>
        </table>

        </br>


        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col lead"><label><strong><?php echo $nombre_doc . ': ' . $doc['id_manu']; ?></strong></label></div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Datos generales: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left' style="width:18%">FECHA:</td>
                <td class='text-left'><?php echo $fecha . ' ' . $hora; ?></td>
            </tr>
            <tr>
                <td class='text-left' style="width:18%">TERCERO:</td>
                <td class='text-left'>
                    <?php
                    if ($id_tercero_gen  > 0) {
                        $response = NombreTercero($id_tercero_gen, $terceros);
                    } else {
                        $response['nombre'] = '---';
                        $response['cc_nit'] = '---';
                    }
                    echo $response['nombre'];
                    ?>
                </td>
            </tr>
            <tr>
                <td class='text-left' style="width:18%">CC/NIT:</td>
                <td class='text-left'><?php echo $response['cc_nit'] ?></td>
            </tr>
            <tr>
                <td class='text-left'>OBJETO:</td>
                <td class='text-left'><?php echo mb_strtoupper($doc['detalle']); ?></td>
            </tr>
            <tr>
                <td class='text-left'>VALOR:</td>
                <td class='text-left'><label><?php echo $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
            </tr>
        </table>

        <?php if ($doc['tipo_doc'] == '3' || $doc['tipo_doc'] == '5') { ?>
            </br>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Imputación presupuestal: </strong></div>
                    </div>
                </div>
            </div>
            <table class="table-bordered" style="width:100% !important; border-collapse: collapse; " cellspacing="2">
                <tr>
                    <?php
                    if ($doc['tipo_doc'] == '5') {
                    ?>
                        <td style="text-align: left;border: 1px solid black ">Número Rp </td>
                        <td style="text-align: left;border: 1px solid black ">Cc/nit</td>
                        <td style="border: 1px solid black ">Código</td>
                        <td style="border: 1px solid black ">Nombre</td>
                        <td style="border: 1px solid black;text-align:center">Valor</td>
                    <?php
                    } else {
                    ?>
                        <td style="text-align: left;border: 1px solid black ">Número Rp</td>
                        <td style="border: 1px solid black ">Código</td>
                        <td style="border: 1px solid black ">Nombre</td>
                        <td style="border: 1px solid black;text-align:center">Valor</td>
                    <?php
                    }
                    ?>
                </tr>
                <?php
                $total_pto = 0;
                if ($doc['tipo_doc'] == '5') {
                    foreach ($rubros as $rp) {
                        $key = array_search($rp['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
                        if ($rp['tipo_mov'] == 'COP') {
                            echo "<tr>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['id_manu'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $terceros[$key]['nit_tercero'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['rubro'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['nom_rubro'] . "</td>
                    <td class='text-right' style='border: 1px solid black; text-align: right'>" . number_format($rp['valor'], 2, ",", ".")  . "</td>
                    </tr>";
                            $total_pto += $rp['valor'];
                        }
                    }
                } else {
                    foreach ($rubros as $rp) {
                        if ($rp['tipo_mov'] == 'COP') {
                            echo "<tr>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['id_manu'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['rubro'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['nom_rubro'] . "</td>
                    <td class='text-right' style='border: 1px solid black; text-align: right'>" . number_format($rp['valor'], 2, ",", ".")  . "</td>
                    </tr>";
                            $total_pto += $rp['valor'];
                        }
                    }
                }
                ?>
                <?php
                if ($doc['tipo_doc'] == '5') {
                ?>
                    <tr>
                        <td colspan="4" style="text-align:left;border: 1px solid black ">Total</td>
                        <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_pto, 2, ",", "."); ?></td>
                    </tr>
                <?php
                } else {
                ?>
                    <tr>
                        <td colspan="3" style="text-align:left;border: 1px solid black ">Total</td>
                        <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_pto, 2, ",", "."); ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
            </br>
            <?php
            if ($doc['tipo_doc'] != '5') {
            ?>
                <div class="row">
                    <div class="col-12">
                        <div style="text-align: left">
                            <div><strong>Datos de la factura: </strong></div>
                        </div>
                    </div>
                </div>
                <table class="table-bordered bg-light" style="width:100% !important;">
                    <tr>
                        <td style="text-align: left">Documento</td>
                        <td>Número</td>
                        <td>Fecha</td>
                        <td>Vencimiento</td>
                    </tr>
                    <tr>
                        <td style="text-align: left"><?php echo $factura['tipo']; ?></td>
                        <td><?php echo $prefijo . $factura['num_doc']; ?></td>
                        <td><?php echo $fecha_fact; ?></td>
                        <td><?php echo $fecha_ven; ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Valor factura</td>
                        <td>Valor IVA</td>
                        <td>Base</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo number_format($factura['valor_pago'], 2, ',', '.'); ?></td>
                        <td><?php echo  number_format($factura['valor_iva'], 2, ',', '.');; ?></td>
                        <td><?php echo number_format($factura['valor_base'], 2, ',', '.'); ?></td>
                        <td></td>
                    </tr>
                </table>
                </br>
                <div class="row">
                    <div class="col-12">
                        <div style="text-align: left">
                            <div><strong>Retenciones y descuentos: </strong></div>
                        </div>
                    </div>
                </div>
                <table class="table-bordered bg-light" style="width:100% !important;border-collapse: collapse;">
                    <tr>
                        <td style="text-align: left;border: 1px solid black">Entidad</td>
                        <td style='border: 1px solid black'>Descuento</td>
                        <td style='border: 1px solid black'>Valor base</td>
                        <td style='border: 1px solid black'>Valor rete</td>
                    </tr>
                    <?php
                    $total_rete = 0;
                    foreach ($retenciones as $re) {
                        // Consulto el valor del tercero de la api
                        // Consulta terceros en la api ********************************************* API
                        $key = array_search($re['id_terceroapi'], array_column($terceros, 'id_tercero_api'));
                        $tercero = $terceros[$key]['nom_tercero'];
                        // fin api terceros **************************
                        echo "<tr>
                <td style='text-align: left;border: 1px solid black'>" . $tercero . "</td>
                <td style='text-align: left;border: 1px solid black'>" . $re['nombre_retencion'] . "</td>
                <td style='text-align: right;border: 1px solid black'>" . number_format($re['valor_base'], 2, ',', '.') . "</td>
                <td style='text-align: right;border: 1px solid black'>" . number_format($re['valor_retencion'], 2, ',', '.') . "</td>
                </tr>";
                        $total_rete += $re['valor_retencion'];
                    }
                    ?>
                    <tr>
                        <td colspan="3" style="text-align:left;border: 1px solid black ">Total</td>
                        <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_rete, 2, ",", "."); ?></td>
                    </tr>

                </table>
            <?php
            }
            ?>
        <?php } ?>


        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Movimiento contable: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important; border-collapse: collapse;">
            <?php
            if ($doc['tipo_doc'] == '5') {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Terceros</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php
                $tot_deb = 0;
                $tot_cre = 0;
                foreach ($movimiento as $mv) {
                    // Consulta terceros en la api ********************************************* API
                    $key = array_search($mv['id_tercero'], array_column($terceros, 'id_tercero_api'));
                    $ccnit = $terceros[$key]['nit_tercero'];
                    $nom_ter =  $terceros[$key]['nom_tercero'];

                    echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] .  "</td>
                <td class='text-left' style='border: 1px solid black'>" . $ccnit . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $nom_ter . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
                </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="4">Sumas iguales</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_deb, 2, ",", "."); ?></td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_cre, 2, ",", "."); ?> </td>
                </tr>
            <?php
            } else {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php

                $tot_deb = 0;
                $tot_cre = 0;
                foreach ($movimiento as $mv) {
                    // Consulta terceros en la api ********************************************* API


                    echo "<tr style='border: 1px solid black'>
            <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
            <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] .  "</td>
            <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
            <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
            </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="2">Sumas iguales</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_deb, 2, ",", "."); ?></td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_cre, 2, ",", "."); ?> </td>
                </tr>
            <?php
            }
            ?>
        </table>
        </br>
        </br>
        <?php if ($num_control == 1) { ?>

            <table class="table-bordered bg-light firmas" style="width:100% !important;" rowspan="8">
                <tr>
                    <td style="text-align: center;height: 70px;">
                        <div>__________________________</div>
                        <div>Elaboró</div>
                        <div>&nbsp;</div>
                    </td>
                    <td style="text-align: center;">
                        <div>__________________________</div>
                        <div>Revisó contabilidad</div>
                        <div>&nbsp;</div>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <div>__________________________</div>
                        <div>Jefe financiero</div>
                        <div>Aprobó</div>
                    </td>
                    <td style="text-align: center;height: 70px;">
                        <div>__________________________</div>
                        <div>Ordenador del pago</div>
                        <div></div>
                    </td>
                </tr>
            </table>
        <?php } else { ?>
            <table class="table-bordered bg-light firmas" style="width:100% !important;" rowspan="8">
                <tr>
                    <?php foreach ($firmas as $mv) {
                        echo '
                    <td style="text-align: center;height: 70px;">
                        <div>__________________________</div>
                        <div>' . $mv['cargo'] . '</div>
                    </td>';
                    }
                    ?>
                </tr>
            </table>
        <?php } ?>
        </br> </br> </br>
    </div>

</div>
<?php
function NombreTercero($id_tercero, $terceros)
{
    $key = array_search($id_tercero, array_column($terceros, 'id_tercero_api'));
    $data['nombre'] = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
    $data['cc_nit'] = $key !== false ? number_format($terceros[$key]['nit_tercero'], 0, '', '.') : '---';
    return $data;
}
