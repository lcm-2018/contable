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
    `ctb_doc`.`tipo_doc`
    , `ctb_doc`.`fecha`
    , `ctb_doc`.`id_manu`
    , `ctb_doc`.`fecha`
    , `ctb_libaux`.`id_tercero` AS id_tercero_api
    , `ctb_doc`.`id_tercero`
    , `ctb_doc`.`detalle`
    , `ctb_doc`.`id_ctb_doc`
    , `ctb_libaux`.`id_crp`
    , `pto_documento_detalles`.`rubro`
    , `pto_documento_detalles`.`id_ctb_cop`
FROM
    `ctb_libaux`
    INNER JOIN `ctb_doc` 
        ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
    LEFT JOIN `pto_documento_detalles`
	ON (`ctb_libaux`.`id_ctb_doc` = `pto_documento_detalles`.`id_ctb_doc`)
WHERE (`ctb_doc`.`tipo_doc` ='CEVA' )
GROUP BY `ctb_doc`.`id_manu`
ORDER BY `ctb_doc`.`id_manu` ASC ;
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
                <td colspan="8" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="8" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:center"><?php echo 'RELACION DE PAGOS REALIZADOS'; ?></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Fecha</td>
                <td>No Egreso</td>
                <td>Tercero</td>
                <td>cc/nit</td>
                <td>detalle</td>
                <td>Rubro</td>
                <td>Causación</td>
                <td>Fecha causación</td>
                <td>Cuenta Banco</td>
                <td>valor causado</td>
                <td>Valor retenido</td>
                <td>Pagado</td>
                <td>Saldo por pagar</td>
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
                $id_ctb_doc = $rp['id_ctb_doc'];
                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                $sql = "SELECT
                            SUM(`valor`) as valor
                            , `id_ctb_cop`
                            , `id_ctb_doc`
                        FROM
                            `pto_documento_detalles`
                        WHERE (`id_ctb_doc` =$id_ctb_doc)";
                $res = $cmd->query($sql);
                $causa = $res->fetch();
                $causado = $causa['valor'];
                $id_cop = $causa['id_ctb_cop'];
                // Suma de valor pagado cuando no afecta presupuesto
                if ($rp['id_crp'] == 0) {
                    $sql = "SELECT
                            SUM(`debito`) as pagos
                            , `id_ctb_doc`
                        FROM
                            `ctb_libaux`
                        WHERE (`id_ctb_doc` =$id_ctb_doc);";
                    $res = $cmd->query($sql);
                    $pago = $res->fetch();
                    $causado = $pago['pagos'];
                }
                // consulto valor retenido a cada documento
                if ($id_cop > 0) {
                    $sql = "SELECT
                        SUM(`valor_retencion`) as retenido
                        , `id_ctb_doc`
                        FROM
                        `ctb_causa_retencion`
                        WHERE (`id_ctb_doc` =$id_cop)";
                    $res = $cmd->query($sql);
                    $reten = $res->fetch();
                    $retenido = $reten['retenido'];
                } else {
                    $retenido = 0;
                }
                // Consulto la cuenta bancaria 
                try {
                    $sql = "SELECT
                    `tes_detalle_pago`.`id_ctb_doc`
                    , `tes_cuentas`.`nombre`
                    , `tes_cuentas`.`cta_contable`
                FROM
                    `tes_detalle_pago`
                    INNER JOIN `tes_cuentas` 
                        ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                WHERE (`tes_detalle_pago`.`id_ctb_doc` ='$id_ctb_doc');";
                    $res = $cmd->query($sql);
                    $cuenta_ban = $res->fetch();
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
                // Consulto la fecha de la causación
                if ($rp['id_ctb_cop'] > 0) {
                    try {
                        $sql = "SELECT
                    `fecha`
                    , `id_manu`
                FROM
                    `ctb_doc`
                WHERE `id_ctb_doc` ={$rp['id_ctb_cop']};";
                        $res = $cmd->query($sql);
                        $causa = $res->fetch();
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                    }
                    $fecha_causa = date('Y-m-d', strtotime($causa['fecha']));
                    $id_manu = $causa['id_manu'];
                } else {
                    $fecha_causa = '';
                    $id_manu = '';
                }
                $pagado = $causado - $retenido;
                $saldo = $pagado - $pagado;
                echo "<tr>
                <td class='text'>" . $fecha .  "</td>
                <td class='text-left'>" . $rp['id_manu'] . "</td>
                <td class='text-right'>" .   $tercero   . "</td>
                <td class='text-right'>" . $ccnit . "</td>
                <td class='text-right'>" . $rp['detalle']   . "</td>
                <td class='text-right'>" . $rp['rubro']   . "</td>
                <td class='text-right'>" . $id_manu   . "</td>
                <td class='text-right'>" . $fecha_causa   . "</td>
                <td class='text-right'>" . $cuenta_ban['cta_contable'] . $cuenta_ban['nombre']   . "</td>
                <td class='text-right'>" . number_format($causado, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($retenido, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($pagado, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($saldo, 2, ".", ",")  . "</td>
                </tr>";
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>