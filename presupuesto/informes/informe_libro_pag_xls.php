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
    , `ctb_doc`.`id_manu`
    , `ctb_doc`.`fecha`
    , IF(`pto_documento_detalles`.`id_tercero_api`,`pto_documento_detalles`.`id_tercero_api`,`ctb_doc`.`id_tercero`) AS id_terceroapi
    , `ctb_doc`.`detalle`
    , `pto_documento_detalles`.`rubro`
    , `pto_cargue`.`nom_rubro`
    , `pto_documento_detalles`.`valor`
    , `pto_documento_detalles`.`id_ctb_cop`
FROM
    `pto_documento_detalles`
    INNER JOIN `ctb_doc` 
        ON (`pto_documento_detalles`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
    INNER JOIN `pto_cargue` 
        ON (`pto_documento_detalles`.`rubro` = `pto_cargue`.`cod_pptal`)
WHERE `ctb_doc`.`fecha` <='$fecha_corte' AND `pto_documento_detalles`.`tipo_mov` = 'PAG' AND `pto_documento_detalles`.`estado` <> 5
ORDER BY `ctb_doc`.`fecha` ASC;
";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$sql2 = $sql;
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
<table style="width:100% !important; border-collapse: collapse;">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="9" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="9" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="9" style="text-align:center"><?php echo 'RELACION DE OBLIGACIONES PRESUPUESTALES'; ?></td>
        </tr>
        <tr>
            <td colspan="9" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <th>Tipo</th>
            <th>No Registro</th>
            <th>No Egreso</th>
            <th>Fecha</th>
            <th>Tercero</th>
            <th>Cc/Nit</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Nombre rubro</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        try {
            $sql = "SELECT DISTINCT
            `id_tercero_api`
        FROM
            `seg_terceros`;";
            $res = $cmd->query($sql);
            $id_terceros = $res->fetchAll();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
        $id_t = [];
        foreach ($id_terceros as $ter) {
            $id_t[] = $ter['id_tercero_api'];
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
            $key = array_search($rp['id_terceroapi'], array_column($terceros, 'id_tercero'));
            $tercero = $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['razon_social'];
            $ccnit = $terceros[$key]['cc_nit'];
            if ($tercero == null) {
                $recero = 'NOMINA DE EMPLEADOS';
            }
            //Consultar el numero del registro
            try {
                $sql = "SELECT
                `pto_documento`.`id_manu`
                , `pto_documento_detalles`.`id_ctb_cop`
            FROM
                `ctb_libaux`
                INNER JOIN `pto_documento` 
                    ON (`ctb_libaux`.`id_crp` = `pto_documento`.`id_doc`)
                INNER JOIN `pto_documento_detalles` 
                    ON (`pto_documento_detalles`.`id_ctb_cop` = `ctb_libaux`.`id_ctb_doc`)
            WHERE `pto_documento_detalles`.`id_ctb_cop` =$rp[id_ctb_cop] LIMIT 1;";
                $res = $cmd->query($sql);
                $registro = $res->fetch();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }

            $fecha = date('Y-m-d', strtotime($rp['fecha']));
            echo "<tr>
                <td style='text-aling:left'>" . $rp['tipo_mov'] .  "</td>
                <td style='text-aling:left'>" . $registro['id_manu'] . "</td>
                <td style='text-aling:left'>" . $rp['id_manu'] . "</td>
                <td style='text-aling:left'>" .   $fecha   . "</td>
                <td style='text-aling:left'>" .   $tercero . "</td>
                <td style='text-aling:left'>" . $ccnit . "</td>
                <td style='text-aling:left'>" . $rp['detalle'] . "</td>
                <td style='text-aling:left'>" . $rp['rubro'] . "</td>
                <td style='text-aling:left'>" .  $rp['nom_rubro'] . "</td>
                <td style='text-aling:right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
                </tr>";
        }
        ?>
    </tbody>
</table>