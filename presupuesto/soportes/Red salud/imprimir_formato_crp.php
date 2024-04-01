<?php
session_start();
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
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT objeto,fecha,id_manu,tipo_doc,id_tercero,id_auto,num_contrato FROM pto_documento WHERE id_pto_doc =$dto AND tipo_doc='CRP'";
    $res = $cmd->query($sql);
    $cdp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT id_manu FROM pto_documento WHERE id_pto_doc =$cdp[id_auto] AND tipo_doc='CDP'";
    $res = $cmd->query($sql);
    $cdp_auto = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Valor total del cdp
try {
    $sql = "SELECT sum(valor) as valor FROM pto_documento_detalles WHERE id_pto_doc =$dto AND tipo_mov='CRP'";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['valor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
    `pto_cargue`.`cod_pptal`
    , `pto_cargue`.`nom_rubro`
    , `pto_cargue`.`tipo_dato`
    FROM
    `pto_cargue`
    INNER JOIN `pto_presupuestos` 
        ON (`pto_cargue`.`id_pto_presupuestos` = `pto_presupuestos`.`id_pto`)
    WHERE (`pto_cargue`.`vigencia` =$vigencia
    AND `pto_presupuestos`.`id_tipo` =2);";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$ccnit = $cdp['id_tercero'];
try {
    $sql = "SELECT no_doc FROM seg_terceros WHERE id_tercero_api =$ccnit";
    $res = $cmd->query($sql);
    $nit = $res->fetch();
    $num_doc = $nit['no_doc'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto responsable del documento
try {
    $sql = "SELECT
    `fin_respon_doc`.`nombre`
    , `fin_respon_doc`.`cargo`
    FROM
    `fin_respon_doc`
    INNER JOIN `fin_maestro_doc` 
        ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
    WHERE (`fin_maestro_doc`.`tipo_doc` ='CDP'
    AND `fin_respon_doc`.`estado` =1);";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();
    $nom_respon = strtoupper($responsable['nombre']);
    $cargo_respon = $responsable['cargo'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulta terceros en la api ********************************************* API
$url = $api . 'terceros/datos/res/datos/id/' . $ccnit;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
// fin api terceros ******************************************************** 
$enletras = numeroLetras($total);
$fecha = date('Y-m-d', strtotime($cdp['fecha']));
?>
<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecCrp('areaImprimir');"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">
        </br>
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
        </br>


        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col lead"><label><strong>REGISTRO PRESUPUESTAL No: <?php echo $cdp['id_manu']; ?></strong></label></div>
            </div>
        </div>

        </br>
        <div class="row">
            <div class="col-12">
                <div class="text-justify">
                    <p>El suscrito <?php echo $cargo_respon; ?> de la entidad <strong><?php echo $empresa['nombre']; ?></strong>, CERTIFICA que se realizó registro presupuestal de para respaldar un compromiso de acuerdo al siguiente detalle:</p>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left' style="width:22%">FECHA:</td>
                <td class='text-left'><?php echo $fecha; ?></td>
            </tr>
            <tr>
                <td class='text-left'>TERCERO:</td>
                <td class='text-left'><?php echo $tercero; ?></td>
            </tr>
            <tr>
                <td class='text-left'>CC/NIT:</td>
                <td class='text-left'><?php echo $num_doc; ?></td>
            </tr>
            <tr>
                <td class='text-left'><label>OBJETO:</label></td>
                <td style='text-align: justify;'><?php echo $cdp['objeto']; ?></td>
            </tr>
            <tr>
                <td class='text-left'><label>VALOR:</label></td>
                <td class='text-left'><?php echo $enletras . "  ($" . number_format($total, 2, ",", ".") . ")";  ?></td>
            </tr>
            <tr>
                <td class='text-left'><label>NUMERO CDP:</label></td>
                <td class='text-left'><?php echo $cdp_auto['id_manu'];  ?></td>
            </tr>
            <tr>
                <td class='text-left'><label>No. CONTRATO:</label></td>
                <td class='text-left'><?php echo $cdp['num_contrato'];  ?></td>
            </tr>
        </table>
        </br>
        <div class="row">
            <div class="col-12">
                <div class="text-justify">
                    Imputación Presupuestal:
                </div>
            </div>
        </div>

        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td>Código</td>
                <td>Nombre</td>
                <td>Valor</td>
            </tr>
            <?php
            foreach ($rubros as $rp) {
                $rubro = $rp['cod_pptal'];
                $sq2 = "SELECT sum(valor) as valor FROM pto_documento_detalles WHERE rubro LIKE '$rubro%' AND id_pto_doc =$dto and tipo_mov='CRP'";
                $res = $cmd->query($sq2);
                $valor = $res->fetch();
                $afecta = $valor['valor'];
                if ($afecta > 0) {
                    echo "<tr>
                <td class='text-left'>" . $rp['cod_pptal'] . "</td>
                <td class='text-left'>" . $rp['nom_rubro'] . "</td>
                <td class='text-right'>" . number_format($afecta, 2, ",", ".")  . "</td>
                </tr>";
                }
            }
            ?>

        </table>
        </br>

        </br>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: center">
                    <div>___________________________________</div>
                    <div><?php echo $nom_respon; ?> </div>
                    <div><?php echo $cargo_respon; ?> </div>
                    <div><?php echo "Según Resolución No 316 de mayo 03 de 2023"; ?> </div>
                </div>
            </div>
        </div> </br> </br> </br>
    </div>

</div>