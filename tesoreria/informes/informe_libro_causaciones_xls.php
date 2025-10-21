<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

$vigencia = $_SESSION['vigencia'];
$fecha_corte = $_POST['fecha'];
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
    `pto_documento_detalles`.`id_documento`
    , SUM(`pto_documento_detalles`.`valor`) as valor
    , `pto_documento_detalles`.`tipo_mov`
    , `ctb_doc`.`id_manu`
    , `ctb_doc`.`id_tercero`
    , `ctb_doc`.`detalle`
    , `ctb_doc`.`fecha`
    , `ctb_doc`.`id_ctb_doc`
    , `pto_documento_detalles`.`estado`
    FROM
    `pto_documento_detalles`
    INNER JOIN `ctb_doc` 
        ON (`pto_documento_detalles`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
    WHERE (`pto_documento_detalles`.`tipo_mov` ='COP'
      AND `pto_documento_detalles`.`estado` = 0)
    GROUP BY `pto_documento_detalles`.`id_documento`, `ctb_doc`.`id_manu`;
";
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
                <td colspan="11" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="11" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo 'RELACION DE CUENTAS POR PAGAR'; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Fecha</td>
                <td>No causaci&oacute;n</td>
                <td>Tercero</td>
                <td>cc/nit</td>
                <td>banco</td>
                <td>Numero de cuenta</td>
                <td>Tipo de cuenta</td>
                <td>detalle</td>
                <td>valor causado</td>
                <td>Valor retenido</td>
                <td>Saldo</td>
            </tr>
            <?php
            $id_t = [];
            foreach ($causaciones as $ca) {
                if ($ca['id_tercero'] !== null) {
                    $id_t[] = $ca['id_tercero'];
                }
            }
            $payload = json_encode($id_t);
            //API URL
            $url = $api . 'terceros/datos/res/datos/cuenta_bancaria';
            $ch = curl_init($url);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            $bancos = json_decode($result, true);

            foreach ($causaciones as $rp) {

                $url = $api . 'terceros/datos/res/datos/id/' . $rp['id_tercero'];
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                $res_api = curl_exec($ch);
                curl_close($ch);
                $dat_ter = json_decode($res_api, true);
                $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
                $ccnit = $dat_ter[0]['cc_nit'];
                // fin api terceros **************************
                $key = array_search($rp['id_tercero'], array_column($bancos, 'id_tercero'));
                if ($key !== false) {
                    $cod_banco = $bancos[$key]['cod_banco'];
                    $cod_banco = str_pad($cod_banco, 3, "0", STR_PAD_LEFT);
                    $num_cuenta = $bancos[$key]['num_cuenta'];
                    $tipo_cuenta = $bancos[$key]['tipo_cuenta'];
                } else {
                    $cod_banco = '';
                    $num_cuenta = '';
                    $tipo_cuenta = '';
                }

                $id_ctb_doc = $rp['id_ctb_doc'];
                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                $sql = "SELECT
                SUM(`valor`) as pagos
                , `id_ctb_cop`
                FROM
                `pto_documento_detalles`
                WHERE (`id_ctb_cop` =$id_ctb_doc);";
                $res = $cmd->query($sql);
                $pagado = $res->fetch();
                $pago = $pagado['pagos'];
                $saldo = $rp['valor'] - $pago;
                // consulto valor retenido a cada documento
                $sql = "SELECT
                SUM(`valor_retencion`) as retenido
                , `id_ctb_doc`
                FROM
                `ctb_causa_retencion`
                WHERE (`id_ctb_doc` =$id_ctb_doc);";
                $res = $cmd->query($sql);
                $reten = $res->fetch();
                $retenido = $reten['retenido'];
                $saldo = $rp['valor'] - $pago - $retenido;
                if ($saldo > 0) {
                    echo "<tr>
                <td class='text'>" . $fecha .  "</td>
                <td class='text-left'>" . $rp['id_manu'] . "</td>
                <td class='text-right'>" .   $tercero   . "</td>
                <td class='text-right'>" . $ccnit . "</td>
                <td class='text'>" . $cod_banco . "</td>
                <td class='text-right'>" . $num_cuenta . "</td>
                <td class='text-right'>" . $tipo_cuenta . "</td>
                <td class='text-right'>" . $rp['detalle']   . "</td>
                <td class='text-right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($retenido, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($saldo, 2, ".", ",")  . "</td>
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