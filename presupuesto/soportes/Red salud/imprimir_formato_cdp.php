<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../../head.php';
header("Content-type: text/html; charset=utf-8");
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
    $sql = "SELECT objeto,fecha,id_manu,num_solicitud FROM pto_documento WHERE id_pto_doc =$dto";
    $res = $cmd->query($sql);
    $cdp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Valor total del cdp
try {
    $sql = "SELECT sum(valor) as valor FROM pto_documento_detalles WHERE id_pto_doc =$dto";
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
// consulto el nombre de la empresa de la tabla tb_datos_ips
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
    , `fin_respon_doc`.`descripcion`
    FROM
    `fin_respon_doc`
    INNER JOIN `fin_maestro_doc` 
        ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
    WHERE (`fin_maestro_doc`.`tipo_doc` ='CDP'
    AND `fin_respon_doc`.`estado` =1);";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();
    $nom_respon = mb_strtoupper($responsable['nombre'], 'UTF-8');
    $cargo_respon = $responsable['cargo'];
    $descrip_respon = $responsable['descripcion'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$enletras = numeroLetras($total);
$fecha = date('Y-m-d', strtotime($cdp['fecha']));
?>
<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecCdp('areaImprimir');"> Imprimir</a>
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
                <div class="col lead"><label><strong>CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL No: <?php echo $cdp['id_manu']; ?></strong></label></div>
            </div>
        </div>

        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col-lg"><label>EL SUSCRITO <?php echo strtoupper($cargo_respon); ?></label></div>
            </div>
        </div>
        </br>
        </br>
        <div class="row">
            <div class="col-12" style="text-align: center">
                <div class="col lead"><label><strong>CERTIFICA:</strong></label></div>
            </div>
        </div>
        </br>
        <div class="row">
            <div class="col-12">
                <div class="text-justify">
                    <p>Que, en el presupuesto de gastos de la entidad <strong><?php echo $empresa['nombre']; ?></strong>, aprobado para la vigencia fiscal <?php echo $vigencia; ?> existe saldo disponible y libre de afectación para respaldar un compromiso de conformidad con la siguiente imputación presupuestal y detalle:</p>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left' style="width:22%">FECHA:</td>
                <td class='text-left'><?php echo $fecha; ?></td>
            </tr>
            <tr>
                <td class='text-left'>OBJETO:</td>
                <td class='text-left'><?php echo $cdp['objeto']; ?></td>
            </tr>
            <tr>
                <td class='text-left'>VALOR:</td>
                <td class='text-left'><label><?php echo $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
            </tr>
            <tr>
                <td class='text-left'>NO SOLICITUD:</td>
                <td class='text-left'><label><?php echo $cdp['num_solicitud']; ?></label></td>
            </tr>
        </table>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td>Código</td>
                <td>Nombre</td>
                <td>Valor</td>
            </tr>
            <?php
            foreach ($rubros as $rp) {
                $rubro = $rp['cod_pptal'];
                $sql = "SELECT sum(valor) as valor FROM pto_documento_detalles WHERE rubro LIKE '$rubro%' AND id_pto_doc =$dto";
                $res = $cmd->query($sql);
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
                    <div><?php echo 'ddd' . $descrip_respon; ?> </div>
                </div>
            </div>
        </div>
        </br> </br> </br>
    </div>

</div>