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
    header("Content-Disposition: attachment; filename=Descuentos_municipio.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
// estraigo las variables que llegan por post en json
$fecha_inicial = $_POST['fec_inicial'];
$fecha_corte = $_POST['fec_final'];
$sede = $_POST['mpio'];
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto la tabla seg_terceros para obtener el id_tercero_api
try {
    $sql = "SELECT
                `seg_terceros`.`id_tercero_api`
            FROM
                `seg_terceros`
            WHERE (`seg_terceros`.`id_tercero` = $sede);";
    $res = $cmd->query($sql);

    $tercero_api = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT DISTINCT
    `seg_ctb_retencion_tipo`.`id_retencion_tipo`
    , `seg_ctb_causa_retencion`.`id_retencion`
    , `seg_ctb_retenciones`.`nombre_retencion`
FROM
    `seg_ctb_causa_retencion`
    INNER JOIN `ctb_doc` 
        ON (`seg_ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
    INNER JOIN `seg_ctb_retenciones` 
        ON (`seg_ctb_retenciones`.`id_retencion` = `seg_ctb_causa_retencion`.`id_retencion`)
    INNER JOIN `seg_ctb_retencion_tipo` 
        ON (`seg_ctb_retenciones`.`id_retencion_tipo` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
WHERE (`seg_ctb_retencion_tipo`.`id_retencion_tipo` =3 AND `seg_ctb_causa_retencion`.`id_terceroapi` = {$tercero_api['id_tercero_api']}
    AND `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte' );
";
    $res = $cmd->query($sql);
    $retenciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consutla para sobretasa
try {
    $sql = "SELECT DISTINCT
    `seg_ctb_retencion_tipo`.`id_retencion_tipo`
    , `seg_ctb_causa_retencion`.`id_retencion`
    , `seg_ctb_retenciones`.`nombre_retencion`
FROM
    `seg_ctb_causa_retencion`
    INNER JOIN `ctb_doc` 
        ON (`seg_ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
    INNER JOIN `seg_ctb_retenciones` 
        ON (`seg_ctb_retenciones`.`id_retencion` = `seg_ctb_causa_retencion`.`id_retencion`)
    INNER JOIN `seg_ctb_retencion_tipo` 
        ON (`seg_ctb_retenciones`.`id_retencion_tipo` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
WHERE (`seg_ctb_retencion_tipo`.`id_retencion_tipo` =4 AND `seg_ctb_causa_retencion`.`id_terceroapi` = {$tercero_api['id_tercero_api']}
    AND `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte');
";
    $res = $cmd->query($sql);
    $sobretasa = $res->fetchAll();
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
// Consulto los id de terceros creado en la tabla ctb_doc
try {
    $sql = "SELECT DISTINCT
    `ctb_doc`.`id_tercero`
FROM
    `ctb_doc`
WHERE ( `ctb_doc`.`id_tercero` >0);";
    $res = $cmd->query($sql);
    $id_terceros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
foreach ($id_terceros as $ter) {
    $id_t[] = $ter['id_tercero'];
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
// buscar datos del tercero
// Consulta terceros en la api ********************************************* API
$url = $api . 'terceros/datos/res/datos/id/' . $tercero_api['id_tercero_api'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
$ccnit = $dat_ter[0]['cc_nit'];
?>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">
        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="6" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="6" style="text-align:center"><?php echo '<h3>' . $empresa['nombre'] . '</h3>'; ?></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center"><?php echo 'RELACION DE DESCUENTOS Y RETENCIONES DETALLADO'; ?></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center"></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>
        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td>MUNICIPIO</td>
                <td style='text-align: left;'><?php echo $tercero; ?></td>
            </tr>
            <tr>
                <td>NIT</td>
                <td style='text-align: left;'><?php echo $ccnit; ?></td>
            </tr>
            <tr>
                <td>FECHA INICIO</td>
                <td style='text-align: left;'><?php echo $fecha_inicial; ?></td>
            </tr>
            <tr>
                <td>FECHA FIN</td>
                <td style='text-align: left;'><?php echo $fecha_corte; ?></td>
            </tr>
        </table>
        </br> &nbsp;
        </br>
        <?php
        foreach ($retenciones as $tp) {
            $total_base =   0;
            $total_ret = 0;
            $pago = 0;
            // Conulto los pagos que se realizaron retenciones
            $sql = "SELECT
                `ctb_doc`.`id_manu`
                , `ctb_doc`.`id_tercero`
                , `seg_ctb_causa_retencion`.`valor_base`
                , `seg_ctb_causa_retencion`.`valor_retencion`
                , `ctb_doc`.`fecha`
                , `seg_ctb_retencion_tipo`.`id_retencion_tipo`
            FROM
                `seg_ctb_causa_retencion`
                INNER JOIN `ctb_doc` 
                    ON (`seg_ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `seg_ctb_retenciones` 
                    ON (`seg_ctb_retenciones`.`id_retencion` = `seg_ctb_causa_retencion`.`id_retencion`)
                INNER JOIN `seg_ctb_retencion_tipo` 
                    ON (`seg_ctb_retenciones`.`id_retencion_tipo` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
            WHERE (`seg_ctb_causa_retencion`.`id_retencion` ={$tp['id_retencion']}
                AND `seg_ctb_causa_retencion`.`id_terceroapi` ={$tercero_api['id_tercero_api']}
                AND `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte');";
            $res = $cmd->query($sql);
            $pagos_realizados = $res->fetchAll();
            // Iniciar la tabla
            echo "<h4>" . strtoupper($tp['nombre_retencion']) . "</h4>";
            echo '<table class="table-bordered bg-light" style="width:100% !important;" border=1>
                    <tr>
                        <td>Comprobante</td>
                        <td>Fecha</td>
                        <td>Nombre</td>
                        <td>CC / Nit</td>
                        <td>Valor base</td>
                        <td>Valor pago</td>
                    </tr>';
            // recorrer pagos realizados con for
            foreach ($pagos_realizados as $pr) {
                // Nombres terceros
                $key = array_search($pr['id_tercero'], array_column($terceros, 'id_tercero'));
                $nom_ter =  $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['razon_social'];
                $fecha = date('Y-m-d', strtotime($pr['fecha']));
                echo "<tr>
                    <td class='text-right'>" . $pr['id_manu'] . "</td>
                    <td class='text-right'>" . $fecha . "</td>
                    <td class='text-right'>" . $nom_ter  . "</td>
                    <td class='text'>" . $terceros[$key]['cc_nit'] . "</td>
                    
                    <td class='text-right'>" . number_format($pr['valor_base'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($pr['valor_retencion'], 2, ".", ",")  . "</td>
                    </tr>";
                $total_base =   $total_base + $pr['valor_base'];
                $total_ret = $total_ret + $pr['valor_retencion'];
            }
            echo "<tr>
                <td class='text-right' colspan='4'> Total</td>
                <td class='text-right'>" . number_format($total_base, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($total_ret, 2, ".", ",")  . "</td>
                </tr>
                </table>
                </br> &nbsp;
                ";
        }

        // ================================ ESPACIO PARA RETENCIONES DE SOBRETASA BOMBERIL =================================

        foreach ($sobretasa as $tp) {
            $total_base =   0;
            $total_ret = 0;
            $pago = 0;
            // Conulto los pagos que se realizaron retenciones
            $sql = "SELECT
                `ctb_doc`.`id_manu`
                , `ctb_doc`.`id_tercero`
                , `seg_ctb_causa_retencion`.`valor_base`
                , `seg_ctb_causa_retencion`.`valor_retencion`
                , `ctb_doc`.`fecha`
                , `seg_ctb_retencion_tipo`.`id_retencion_tipo`
            FROM
                `seg_ctb_causa_retencion`
                INNER JOIN `ctb_doc` 
                    ON (`seg_ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `seg_ctb_retenciones` 
                    ON (`seg_ctb_retenciones`.`id_retencion` = `seg_ctb_causa_retencion`.`id_retencion`)
                INNER JOIN `seg_ctb_retencion_tipo` 
                    ON (`seg_ctb_retenciones`.`id_retencion_tipo` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
            WHERE (`seg_ctb_causa_retencion`.`id_retencion` ={$tp['id_retencion']}
                AND `seg_ctb_causa_retencion`.`id_terceroapi` ={$tercero_api['id_tercero_api']}
                AND `ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_corte');";
            $res = $cmd->query($sql);
            $pagos_realizados = $res->fetchAll();
            // Iniciar la tabla
            echo "<h4>" . strtoupper($tp['nombre_retencion']) . "</h4>";
            echo '<table class="table-bordered bg-light" style="width:100% !important;" border=1>
                    <tr>
                        <td>Comprobante</td>
                        <td>Fecha</td>
                        <td>Nombre</td>
                        <td>CC / Nit</td>
                        <td>Valor base</td>
                        <td>Valor pago</td>
                    </tr>';
            // recorrer pagos realizados con for
            foreach ($pagos_realizados as $pr) {
                // Nombres terceros
                $key = array_search($pr['id_tercero'], array_column($terceros, 'id_tercero'));
                $nom_ter =  $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['razon_social'];
                $fecha = date('Y-m-d', strtotime($pr['fecha']));
                echo "<tr>
                    <td class='text-right'>" . $pr['id_manu'] . "</td>
                    <td class='text-right'>" . $fecha . "</td>
                    <td class='text-right'>" . $nom_ter  . "</td>
                    <td class='text'>" . $terceros[$key]['cc_nit'] . "</td>
                    <td class='text-right'>" . number_format($pr['valor_base'], 2, ".", ",")  . "</td>
                    <td class='text-right'>" . number_format($pr['valor_retencion'], 2, ".", ",")  . "</td>
                    </tr>";
                $total_base =   $total_base + $pr['valor_base'];
                $total_ret = $total_ret + $pr['valor_retencion'];
            }
            echo "<tr>
                <td class='text-right' colspan='4'> Total</td>
                <td class='text-right'>" . number_format($total_base, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($total_ret, 2, ".", ",")  . "</td>
                </tr>
                </table>
                </br> &nbsp;
                ";
        }


        ?>


    </div>

</div>

</html>