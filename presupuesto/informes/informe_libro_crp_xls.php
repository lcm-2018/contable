<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}

$vigencia = $_SESSION['vigencia'];
$fecha_corte = file_get_contents("php://input");
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
    `pto_documento_detalles`.`tipo_mov`
    , `pto_documento`.`id_manu`
    , `pto_documento`.`fecha`
    , `pto_documento`.`id_tercero`
    , `pto_documento_detalles`.`id_tercero_api`
    , `pto_documento`.`objeto`
    , `pto_documento`.`num_contrato`
    , `pto_documento`.`id_auto`
    , `pto_documento_detalles`.`rubro`
    , `pto_cargue`.`nom_rubro`
    , `pto_documento_detalles`.`valor`
    , `pto_documento_detalles`.`id_documento`
FROM
    `pto_documento_detalles`
    LEFT JOIN `pto_cargue` 
        ON (`pto_documento_detalles`.`rubro` = `pto_cargue`.`cod_pptal`)
    INNER JOIN `pto_documento` 
        ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
WHERE `pto_documento_detalles`.`tipo_mov` ='CRP' AND `pto_documento`.`fecha` <= '$fecha_corte' AND `pto_documento`.`estado` = 0
ORDER BY `pto_documento`.`fecha` ASC;
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
?>
<style>
    .resaltar:nth-child(even) {
        background-color: #F8F9F9;
    }

    .resaltar:nth-child(odd) {
        background-color: #ffffff;
    }
</style>
<table style="width:100% !important; border-collapse: collapse;">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="11" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'RELACION DE CERTIFICADOS DE REGISTRO PRESUPUESTAL'; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <td>Tipo</td>
            <td>No CDP</td>
            <td>Fecha CDP</td>
            <td>No Registro</td>
            <td>Fecha RP</td>
            <td>Tercero</td>
            <td>Cc/Nit</td>
            <td>No Contrato</td>
            <td>Objeto</td>
            <td>Rubro</td>
            <td>Nombre rubro</td>
            <td>Valor</td>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        $id_t = [];
        foreach ($causaciones as $ca) {
            if ($ca['id_tercero_api'] == null) {
                $id_t[] = $ca['id_tercero'];
            } else {
                $id_t[] = $ca['id_tercero_api'];
            }
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
        foreach ($causaciones as $rp) {
            // Consulta de datos del cdp
            try {
                $sql = "SELECT `id_manu`, `fecha` FROM `pto_documento` WHERE id_pto_doc ={$rp['id_auto']} ;";
                $res = $cmd->query($sql);
                $datos_cdp = $res->fetch();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }
            // Consulto datos de liberación de saldos
            try {
                $sql = "SELECT
                                `pto_documento`.`tipo_doc`
                                , SUM(`pto_documento_detalles`.`valor`) as liquidado
                            FROM
                                `pto_documento_detalles`
                                INNER JOIN `pto_documento` 
                                    ON (`pto_documento_detalles`.`id_documento` = `pto_documento`.`id_doc`)
                            WHERE (`pto_documento`.`tipo_doc` ='LRP'
                                AND `pto_documento_detalles`.`rubro` ='{$rp['rubro']}'
                                AND `pto_documento_detalles`.`id_auto_crp` ={$rp['id_pto_doc']}
                                AND `pto_documento`.`fecha` <= '$fecha_corte')
                            GROUP BY `pto_documento_detalles`.`rubro`, `pto_documento_detalles`.`id_documento`;";
                $res = $cmd->query($sql);
                $liberaciones = $res->fetch();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }
            if ($rp['id_tercero_api'] == null) {
                $id_tercero = $rp['id_tercero'];
            } else {
                $id_tercero = $rp['id_tercero_api'];
            }
            $key = array_search($id_tercero, array_column($terceros, 'id_tercero'));
            $tercero = $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['razon_social'];
            $ccnit = $terceros[$key]['cc_nit'];

            $fecha = date('Y-m-d', strtotime($rp['fecha']));
            $fecha_cdp = date('Y-m-d', strtotime($datos_cdp['fecha']));
            $valor = $rp['valor'] + $liberaciones['liquidado'];
            echo "<tr>
                <td style='text-aling:left'>" . $rp['tipo_mov'] .  "</td>
                <td style='text-aling:left'>" . $datos_cdp['id_manu'] . "</td>
                <td style='text-aling:left'>" .   $fecha_cdp   . "</td>
                <td style='text-aling:left'>" . $rp['id_manu'] . "</td>
                <td style='text-aling:left'>" .   $fecha   . "</td>
                <td style='text-aling:left'>" .   $tercero . "</td>
                <td style='text-aling:left'>" . $ccnit . "</td>
                <td style='text-aling:left'>" . $rp['num_contrato'] . "</td>
                <td style='text-aling:left'>" . $rp['objeto'] . "</td>
                <td style='text-aling:left'>" . $rp['rubro'] . "</td>
                <td style='text-aling:left'>" .  $rp['nom_rubro'] . "</td>
                <td style='text-aling:right'>" . number_format($valor, 2, ".", ",")  . "</td>
                </tr>";
        }
        ?>
    </tbody>
</table>