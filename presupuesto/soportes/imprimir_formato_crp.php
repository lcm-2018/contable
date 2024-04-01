<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_vigencia = $_SESSION['id_vigencia'];
$id_crp = $_POST['id'];
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
                `pto_crp`.`objeto`
                , `pto_crp`.`fecha`
                , `pto_crp`.`id_manu`
                , `pto_crp`.`id_tercero_api`
                , `pto_crp`.`num_contrato`
                , `pto_crp`.`fecha_reg`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`apellido2`
                , `seg_usuarios_sistema`.`apellido1`) AS `usuario`
                , `pto_cdp`.`id_manu` AS `num_cdp`
                , `seg_terceros`.`no_doc`
            FROM
                `pto_crp`
                INNER JOIN `seg_usuarios_sistema` 
                    ON (`pto_crp`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)
                INNER JOIN `pto_cdp`
                    ON (`pto_crp`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                INNER JOIN `seg_terceros` 
                    ON (`pto_crp`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
            WHERE (`pto_crp`.`id_pto_crp` = $id_crp)";
    $res = $cmd->query($sql);
    $crp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Valor total del cdp
try {
    $sql = "SELECT
                (IFNULL(SUM(`valor`),0) - IFNULL(SUM(`valor_liberado`),0)) AS `valor`
            FROM
                `pto_crp_detalle`
            WHERE (`id_pto_crp` = $id_crp)";
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
                , (IFNULL(`pto_crp_detalle`.`valor`,0) - IFNULL(`pto_crp_detalle`.`valor_liberado`,0)) AS `valor`
            FROM
                `pto_crp_detalle`
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
            WHERE (`pto_crp_detalle`.`id_pto_crp` = $id_crp)";
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
try {
    $sql = "SELECT `razon_social_ips` AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver` FROM `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto responsable del documento
try {
    /*
    $sql = "SELECT
                `fin_respon_doc`.`nombre`
                , `fin_respon_doc`.`cargo`
                , `fin_respon_doc`.`descripcion`
            FROM
                `fin_respon_doc`
            INNER JOIN `fin_maestro_doc` 
                ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
            WHERE (`fin_maestro_doc`.`tipo_doc` ='CDP' AND `fin_respon_doc`.`estado` = 1);";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();*/
    $responsable['nombre'] = 'XXXXX XXXXX XXXX';
    $responsable['cargo'] = 'XXXXXXXX';
    $responsable['descripcion'] = 'xxxxxxxxxx';

    $nom_respon = mb_strtoupper($responsable['nombre'], 'UTF-8');
    $cargo_respon = $responsable['cargo'];
    $descrip_respon = $responsable['descripcion'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulta terceros en la api ********************************************* API
$id_t[] = $crp['id_tercero_api'];
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
$dat_ter = json_decode($result, true);
$tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
// fin api terceros ******************************************************** 
$enletras = numeroLetras($total);
$fecha = date('Y-m-d', strtotime($crp['fecha']));
?>
<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecCrp('areaImprimir', <?php echo $id_crp ?>);"> Imprimir</a>
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
                <div class="col lead"><label><strong>REGISTRO PRESUPUESTAL No: <?php echo $crp['id_manu']; ?></strong></label></div>
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
                <td class='text-left'><?php echo $crp['no_doc']; ?></td>
            </tr>
            <tr>
                <td class='text-left'><label>OBJETO:</label></td>
                <td style='text-align: justify;'><?php echo $crp['objeto']; ?></td>
            </tr>
            <tr>
                <td class='text-left'><label>VALOR:</label></td>
                <td class='text-left'><?php echo $enletras . "  ($" . number_format($total, 2, ",", ".") . ")";  ?></td>
            </tr>
            <tr>
                <td class='text-left'><label>NUMERO CDP:</label></td>
                <td class='text-left'><?php echo $crp['num_cdp'];  ?></td>
            </tr>
            <tr>
                <td class='text-left'><label>No. CONTRATO:</label></td>
                <td class='text-left'><?php echo $crp['num_contrato'];  ?></td>
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
        </div> </br>
        <table class="table-bordered bg-light" style="width:100% !important;font-size: 10px;">
            <tr>
                <td class='text-left' style="width:33%">
                    <strong>Elaboró:</strong>
                    <div><?php echo $crp['usuario']; ?></div>
                </td>
                <td style="text-align:center" style="width:33%">
                </td>
                <td class='text-center' style="width:33%"><label class="small"></label></td>
            </tr>
        </table>

    </div>

</div>