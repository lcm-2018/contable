<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
header("Content-type: text/html; charset=utf-8");
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
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
    $sql = "SELECT
                `pto_cdp`.`objeto`
                , `pto_cdp`.`id_manu`
                , `pto_cdp`.`fecha`
                , `pto_cdp`.`num_solicitud`
                , `pto_cdp`.`fecha_reg` AS `fec_reg`
                , CONCAT_WS(`seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`
                , `seg_usuarios_sistema`.`apellido2`) AS `usuario`
            FROM
                `pto_cdp`
                LEFT JOIN `seg_usuarios_sistema` 
                    ON (`pto_cdp`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)
            WHERE (`pto_cdp`.`id_pto_cdp` = $dto)";
    $res = $cmd->query($sql);
    $cdp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_pto_cdp`, SUM(`valor`) AS `debito`, SUM(`valor_liberado`) AS `credito`
            FROM
                `pto_cdp_detalle`
            WHERE (`id_pto_cdp` = $dto)";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['debito'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                `pto_cdp_detalle`.`id_pto_cdp`
                , `pto_cdp_detalle`.`valor`
                , `pto_cdp_detalle`.`valor_liberado`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`
                , `pto_tipo`.`nombre`
            FROM
                `pto_cdp_detalle`
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
                INNER JOIN `pto_tipo` 
                    ON (`pto_presupuestos`.`id_tipo` = `pto_tipo`.`id_tipo`)
            WHERE (`pto_cdp_detalle`.`id_pto_cdp`  = $dto)";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                `pto_cargue`.`cod_pptal`
                ,`pto_cargue`.`nom_rubro`
            FROM
                `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE (`pto_presupuestos`.`id_vigencia` = $id_vigencia AND `pto_presupuestos`.`id_tipo` = 2)";
    $res = $cmd->query($sql);
    $codigos = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$data = [];
foreach ($codigos as $cd) {
    $raiz = $cd['cod_pptal'];
    foreach ($rubros as $rp) {
        $codigo = $rp['cod_pptal'];
        if (substr($codigo, 0, strlen($raiz)) === $raiz) {
            $data[$raiz]['valor'] = isset($data[$raiz]['valor']) ? $data[$raiz]['valor'] + $rp['valor'] : $rp['valor'];
            $data[$raiz]['nombre'] = $cd['nom_rubro'];
        }
    }
}

// consulto el nombre de la empresa de la tabla tb_datos_ips
$etiqueta = !empty($rubros) ? mb_strtolower($rubros[0]['nombre']) : '';
$etiqueta1 = 'Presupuesto de ingresos';
$etiqueta2 = 'Presupuesto de gastos';
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT `razon_social_ips` AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver` FROM `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto responsable del documento
try {
    $sql = "SELECT
                `fin_maestro_doc`.`nombre`
                , `fin_respon_doc`.`cargo`
                , `fin_maestro_doc`.`codigo_doc` AS `descripcion`
            FROM
                `fin_respon_doc`
                INNER JOIN `fin_maestro_doc` 
                    ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
            WHERE (`fin_maestro_doc`.`id_maestro` = 1)";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();
    if (empty($responsable)) {
        $responsable['nombre'] = 'XXXXX XXXXX XXXX';
        $responsable['cargo'] = 'XXXXXXXX';
        $responsable['descripcion'] = 'xxxxxxxxxx';
    }
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
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecCdp('areaImprimir', <?php echo $dto ?>);"> Imprimir</a>
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
            foreach ($data as $key => $dt) {
                $rubro = $dt['nombre'];
                $val = $dt['valor'];
                echo "<tr>
                        <td class='text-left'>" . $key . "</td>
                        <td class='text-left'>" . $rubro . "</td>
                        <td style='text-align:right'>" . number_format($val, 2, ",", ".")  . "</td>
                    </tr>";
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
                    <div><?php echo $descrip_respon; ?> </div>
                </div>
            </div>
        </div>
        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;font-size: 10px;">
            <tr>
                <td class='text-left' style="width:33%">
                    <strong>Elaboró:</strong>
                    <div><?php echo $cdp['usuario']; ?></div>
                </td>
                <td style="text-align:center" style="width:33%">
                </td>
                <td class='text-center' style="width:33%"><label class="small"></label></td>
            </tr>
        </table>

    </div>

</div>