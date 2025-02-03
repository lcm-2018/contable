<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>CONTAFACIL</title>
    <style>
        .text {
            mso-number-format: "\@"
        }
    </style>

    <?php

    header("Content-type: application/vnd.ms-excel charset=utf-8");
    header("Content-Disposition: attachment; filename=Relacion_tercero_causacion_pago.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
$tercero = $_POST['tercero'];
$fecha_inicial = $_POST['fecha_ini'];
$fecha_final = $_POST['fecha_fin'];
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT
                `ctb_doc`.`fecha`
                , IF(`pto_documento_detalles`.`id_tercero_api`,`pto_documento_detalles`.`id_tercero_api`,`ctb_doc`.`id_tercero`) AS id_tercero
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_manu`
                , `ctb_factura`.`num_doc`
                , `pto_documento_detalles`.`valor`
                , `pto_documento_detalles`.`rubro`
                , `ctb_doc`.`id_ctb_doc`
            FROM
                `ctb_factura`
                INNER JOIN `ctb_doc` 
                    ON (`ctb_factura`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_documento_detalles` 
                    ON (`ctb_doc`.`id_ctb_doc` = `pto_documento_detalles`.`id_ctb_doc`)
            WHERE (`ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_final'
                AND (`ctb_doc`.`tipo_doc` ='NCXP' OR `ctb_doc`.`tipo_doc` ='CNOM') AND
                IF(`pto_documento_detalles`.`id_tercero_api`,`pto_documento_detalles`.`id_tercero_api`,`ctb_doc`.`id_tercero`) = $tercero
                );";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT
    `nombre`
    , `nit`
    , `dig_ver`
FROM
    `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?> <div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="14" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="14" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="14" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="14" style="text-align:center"><?php echo 'RELACION DE TERCEROS CON CAUSACION Y PAGOS'; ?></td>
            </tr>
            <tr>
                <td colspan="14" style="text-align:center"><?php echo 'Fecha de inicial: ' . $fecha_inicial . ' Fecha final: ' . $fecha_final; ?></td>
            </tr>
            <tr>
                <td colspan="14" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Fecha causación</td>
                <td>No causación</td>
                <td>Tercero</td>
                <td>cc/nit</td>
                <td>Concepto</td>
                <td>Banco</td>
                <td>Cuenta bancaria</td>
                <td>No Factura</td>
                <td>Rubro</td>
                <td>Valor bruto</td>
                <td>Descuentos</td>
                <td>Valor a pagar</td>
                <td>Valor pagado</td>
                <td>Saldo por pagar</td>
            </tr>
            <?php
            $id_t = [];
            foreach ($causaciones as $ca) {
                if ($ca['id_tercero'] !== null) {
                    $id_t[] = $ca['id_tercero'];
                }
            }

            foreach ($causaciones as $rp) {
                $url = $api . 'terceros/datos/res/datos/id/' . $rp['id_tercero'];
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $res_api = curl_exec($ch);
                curl_close($ch);
                $dat_ter = json_decode($res_api, true);
                $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
                $ccnit = $dat_ter[0]['cc_nit'];
                // fin api terceros **************************
                $pagos = 0;
                $retenido = 0;
                $val_retenido = 0;
                $val_neto = 0;
                $cxp = 0;
                $saldo = 0;
                // Consulta valor pagado por documento y rubro
                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                $sql = "SELECT
                            SUM(`pto_documento_detalles`.`valor`) as pagado
                            , `pto_documento_detalles`.`rubro`
                        FROM
                            `pto_documento_detalles`
                            INNER JOIN `ctb_doc` 
                                ON (`pto_documento_detalles`.`id_ctb_cop` = `ctb_doc`.`id_ctb_doc`)
                        WHERE (`pto_documento_detalles`.`id_ctb_cop` ='{$rp['id_ctb_doc']}'
                            AND `pto_documento_detalles`.`rubro` ='{$rp['rubro']}'
                            AND `ctb_doc`.`fecha` <='$fecha_final');";
                $res = $cmd->query($sql);
                $pago = $res->fetch();
                $pagos = $pago['pagado'];
                //Consulta cuenta de banco donde se realizó el pago
                $sql = "SELECT
                            `tes_detalle_pago`.`id_ctb_pag` 
                            , `tes_cuentas`.`numero` 
                            , CONCAT(`tb_bancos`.`cod_banco`, ' - ', `tb_bancos`.`nom_banco`) AS banco
                        FROM
                            `tes_cuentas`
                            INNER JOIN `tb_bancos` 
                                ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                            INNER JOIN `tes_detalle_pago` 
                                ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                        WHERE (`tes_detalle_pago`.`id_ctb_pag` ='{$rp['id_ctb_doc']}');";
                $res = $cmd->query($sql);
                $cuenta = $res->fetch();
                $banco = $cuenta['banco'];
                // consulto valor retenido a cada documento
                $sql = "SELECT
                                SUM(`ctb_causa_retencion`.`valor_retencion`) as retenido
                        FROM
                            `ctb_causa_retencion`
                            INNER JOIN `ctb_doc` 
                                ON (`ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        WHERE (`ctb_causa_retencion`.`id_ctb_doc` ={$rp['id_ctb_doc']})
                        GROUP BY `ctb_causa_retencion`.`id_ctb_doc`;";
                $res = $cmd->query($sql);
                $reten = $res->fetch();
                $retenido = $reten['retenido'];
                // Consulto el valor total del pago realizado
                $sql = "SELECT
                        `id_ctb_doc`
                        , `tipo_mov`
                        , SUM(`valor`) as total_pago
                    FROM
                        `pto_documento_detalles`
                    WHERE (`id_ctb_doc` ={$rp['id_ctb_doc']}
                        AND `tipo_mov` ='COP')
                    GROUP BY `id_ctb_doc`;";
                $res = $cmd->query($sql);
                $pago_total = $res->fetch();
                $pago_total = $pago_total['total_pago'];
                // saco el valor retenido proporcional al valor pagado / total pagado
                $val_retenido = $retenido *  ($rp['valor'] / $pago_total);
                // redondear val_retenido
                $val_retenido = round($val_retenido, 0);
                $val_neto = $rp['valor'] - $val_retenido;
                if ($pagos > 0) {
                    $pagos = $pagos - $val_retenido;
                    $cta_banco = $cuenta['numero'];
                    $nom_banco = $cuenta['banco'];
                } else {
                    $pagos = 0;
                    $cta_banco = '';
                    $nom_banco = '';
                }
                $cxp = $val_neto - $pagos;
                $saldo = 1;
                if ($saldo > 0) {
                    echo "<tr>
                    <td class='text-right'>" . $fecha  . "</td>
                    <td class='text-right'>" . $rp['id_manu'] . "</td>
                    <td class='text'>" . $tercero .  "</td>
                    <td class='text-left'>" . $ccnit . "</td>
                    <td class='text-right'>" .  $rp['detalle']  . "</td>
                    <td class='text'>" . $nom_banco .  "</td>
                    <td class='text'>" . $cta_banco .  "</td>
                    <td class='text'>" . $rp['num_doc'] . "</td>
                    <td class='text-right'>" . $rp['rubro'] . "</td>
                    <td class='text-right'>" . $rp['valor']   . "</td>
                    <td class='text-right'>" . $val_retenido . "</td>
                    <td class='text-right'>" . $val_neto . "</td>
                    <td class='text-right'>" . $pagos  . "</td>
                    <td class='text-right'>" . $cxp . "</td>
                    </tr>";
                }
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>

</html>