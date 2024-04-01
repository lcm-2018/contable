<?php
session_start();
set_time_limit(3600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php';
$vigencia = $_SESSION['vigencia'];
$dto = $_POST['id'];
$prefijo = '';
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT detalle,fecha,id_manu,id_tercero,fec_reg,tipo_doc FROM ctb_doc WHERE fecha < $corte";
    $res = $cmd->query($sql);
    $causaciones = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$fecha = date('Y-m-d', strtotime($cdp['fecha']));
$hora = date('H:i:s', strtotime($cdp['fec_reg']));
?>
<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecDoc('areaImprimir',<?php echo $dto; ?>);"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">
        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col lead"><label><strong><?php echo $nombre_doc . ': ' . $cdp['id_manu']; ?></strong></label></div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Datos generales: </strong></div>
                </div>
            </div>
        </div>

        </br>
        <table class="table-bordered" style="width:100% !important; border-collapse: collapse; " cellspacing="2">
            <tr>
                <?php
                if ($cdp['tipo_doc'] == 'CNOM') {
                ?>
                    <td style="text-align: left;border: 1px solid black ">Número Rp</td>
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
            if ($cdp['tipo_doc'] == 'CNOM') {
                $id_t = [];
                foreach ($rubros as $rp) {
                    $id_t[] = $rp['id_tercero_api'];
                }
                $payload = json_encode($id_t);
                //API URL
                $url = $api . 'terceros/datos/res/lista/terceros';
                $ch = curl_init($url);
                //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);
                $terceros = json_decode($result, true);

                foreach ($rubros as $rp) {
                    $key = array_search($rp['id_tercero_api'], array_column($terceros, 'id_tercero'));
                    if ($rp['tipo_mov'] == 'COP') {
                        echo "<tr>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['id_manu'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $terceros[$key]['cc_nit'] . "</td>
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
            if ($cdp['tipo_doc'] == 'CNOM') {
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
                $key = array_search($re['id_tercero_api'], array_column($terceros, 'id_tercero'));
                $tercero = $dat_ter[$key]['apellido1'] . ' ' . $dat_ter[$key]['apellido2'] . ' ' . $dat_ter[$key]['nombre1'] . ' ' . $dat_ter[$key]['nombre2'] . ' ' . $dat_ter[$key]['razon_social'];

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
            if ($cdp['tipo_doc'] == 'CNOM') {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Terceros</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php
                $tot_deb = 0;
                $tot_cre = 0;
                foreach ($movimiento as $mv) {
                    // Consulta terceros en la api ********************************************* API
                    $key = array_search($mv['id_tercero'], array_column($terceros, 'id_tercero'));

                    $ccnit = $terceros[$key]['cc_nit'];

                    echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] .  "</td>
                <td class='text-left' style='border: 1px solid black'>" .   $ccnit  . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
                </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="3">Sumas iguales</td>
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