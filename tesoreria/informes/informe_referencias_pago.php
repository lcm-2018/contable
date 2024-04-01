<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
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
    header("Content-Disposition: attachment; filename=FORMATO_201101_F07_AGR.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
$id = $_POST['referencia'];
// fecha de hoy en formato y-m-d
$hoy = date("Y-m-d");
$fecha = strtotime('+1 day', strtotime($hoy));
$concepto = 'PAGO SEGÚN REFERENCIA NUMERO ' . $id;
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
                id_plano
                , fecha
                , id_manu
                , id_tercero
                , detalle
                , ctb_doc.id_ctb_doc
                , pto_documento_detalles.id_ctb_cop
                , causacion.causado AS casusado
                , IFNULL(descuentos.descuento,0) AS descuento
            FROM ctb_doc
            INNER JOIN pto_documento_detalles ON (ctb_doc.id_ctb_doc=pto_documento_detalles.id_ctb_doc)
            LEFT JOIN (
                SELECT 
                    SUM(pto_documento_detalles.valor) AS causado
                    ,id_ctb_cop
                FROM 
                    pto_documento_detalles 
                GROUP BY pto_documento_detalles.id_ctb_cop
            ) AS  causacion ON (pto_documento_detalles.id_ctb_cop =causacion.id_ctb_cop)
            LEFT JOIN(
                SELECT 
                    SUM(seg_ctb_causa_retencion.valor_retencion) AS descuento
                    ,seg_ctb_causa_retencion.id_ctb_doc
                FROM seg_ctb_causa_retencion
                GROUP BY seg_ctb_causa_retencion.id_ctb_doc
            ) AS descuentos ON (pto_documento_detalles.id_ctb_cop=descuentos.id_ctb_doc)
            WHERE (ctb_doc.id_plano =$id);
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
                <td colspan="13" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="13" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo 'FORMATO PAGO BANCOS '; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo 'Número de referencia: ' . $id; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Identificación</td>
                <td>Tipo Id</td>
                <td>Dv</td>
                <td>Nombre</td>
                <td>Forma de pago</td>
                <td>Numero de cuenta</td>
                <td>Banco</td>
                <td>Tipo de cuenta</td>
                <td>Cuenta</td>
                <td>Fecha limite</td>
                <td>Valor</td>
                <td>Concepto</td>
                <td>Correo</td>
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
            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            $bancos = json_decode($result, true);
            echo $bancos;
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
                //$key = array_search($rp['id_tercero'], array_column($bancos, 'id_tercero'));
                $key = false;
                if ($key !== false) {
                    $cod_banco = $bancos[$key]['cod_banco'];
                    $cod_banco = str_pad($cod_banco, 3, "0", STR_PAD_LEFT);
                    $num_cuenta = $bancos[$key]['num_cuenta'];
                    $tipo_cuenta = $bancos[$key]['tipo_cuenta'];
                } else {
                    $cod_banco = '';
                    $num_cuenta = '';
                    $tipo_cuenta = '';
                    $correo = '';
                }
                $saldo = $rp['casusado'] - $rp['descuento'];
                echo "<tr>
                <td class='text'>" . $ccnit .  "</td>
                <td class='text-left'>1 </td>
                <td class='text-right'>0</td>
                <td class='text-right'>" . $tercero . "</td>
                <td class='text-right'>1</td>
                <td class='text'>" . $cod_banco . "</td>
                <td class='text-right'>" . $num_cuenta . "</td>
                <td class='text-right'>" . $tipo_cuenta . "</td>
                <td class='text-right'>" . $tipo_cuenta . "</td>
                <td class='text-right'>" . $hoy   . "</td>
                <td class='text-right'>" . number_format($saldo, 2, ".", ",")  . "</td>
                <td class='text-right'>" . $concepto  . "</td>
                <td class='text-right'>" . $correo . "</td>
                </tr>";
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>

</html>