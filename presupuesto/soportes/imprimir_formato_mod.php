<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$vigencia = $_SESSION['vigencia'];
$dto = $_POST['id'];
$filtro_ccred = '';
$filtro_cred = '';
$vertabla = '';
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
                `pto_mod`.`id_manu`
                , `pto_mod`.`fecha`
                , `pto_mod`.`id_tipo_mod` AS `tipo_doc`
                , `pto_mod`.`objeto`
                , `pto_mod`.`estado`
                , `pto_tipo_mvto`.`nombre` AS `tipo`
                , `pto_mod`.`id_tipo_acto`
                , `pto_actos_admin`. `nombre` AS `acto`
                , `pto_mod`.`fecha_reg` AS `fec_reg`
                , CONCAT_WS(' ',`seg_usuarios_sistema`.`nombre1`, `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`, `seg_usuarios_sistema`.`apellido2`) AS `usuario`
                , CONCAT_WS(' ', `seg_usuarios_sistema_1`.`nombre1`
                , `seg_usuarios_sistema_1`.`nombre2`
                , `seg_usuarios_sistema_1`.`apellido1`
                , `seg_usuarios_sistema_1`.`apellido2`) AS `usuario_act`
            FROM
                `pto_mod`
                INNER JOIN `pto_tipo_mvto` ON (`pto_mod`.`id_tipo_mod` = `pto_tipo_mvto`.`id_tmvto`)
                INNER JOIN `pto_actos_admin` ON (`pto_mod`.`id_tipo_acto` = `pto_actos_admin`.`id_acto`)
                LEFT JOIN `seg_usuarios_sistema` ON (`pto_mod`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)
                LEFT JOIN `seg_usuarios_sistema` AS `seg_usuarios_sistema_1` ON (`pto_mod`.`id_user_act` = `seg_usuarios_sistema_1`.`id_usuario`)
            WHERE (`pto_mod`.`id_pto_mod` = $dto)";
    $res = $cmd->query($sql);
    $cdp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$anulado = $cdp['estado'] == '0' ? 'ANULADO' : '';
// Valor total del cdp
try {
    $sql = "SELECT
                `id_pto_mod`, SUM(`valor_deb`) AS `debito`, SUM(`valor_cred`) AS `credito`
            FROM
                `pto_mod_detalle`
            WHERE (`id_pto_mod` = $dto)";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['debito'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los rubros del ingreso afectados en la adición o reducción presupuestal si es ADI o RED
try {
    $sql = "SELECT
                `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`
                , `pto_tipo`.`nombre`
                ,`pto_mod_detalle`.`valor_deb`
                ,`pto_mod_detalle`.`valor_cred`
            FROM
                `pto_mod_detalle`
                INNER JOIN `pto_cargue` 
                    ON (`pto_mod_detalle`.`id_cargue` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
                INNER JOIN `pto_tipo` 
                    ON (`pto_presupuestos`.`id_tipo` = `pto_tipo`.`id_tipo`)
            WHERE (`pto_mod_detalle`.`id_pto_mod` = $dto)";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$etiqueta = !empty($rubros) ? mb_strtolower($rubros[0]['nombre']) : '';
$etiqueta1 = 'Presupuesto de ingresos';
$etiqueta2 = 'Presupuesto de gastos';
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT `razon_social_ips` AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver` FROM `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto responsable del documento
$enletras = numeroLetras($total);
$fecha = date('Y-m-d', strtotime($cdp['fecha']));
try {
    $sql = "SELECT
                `fin_maestro_doc`.`control_doc`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`genero`
                , `fin_respon_doc`.`cargo`
                , `fin_respon_doc`.`tipo_control`
                , `fin_tipo_control`.`descripcion` AS `nom_control`
                , `fin_respon_doc`.`fecha_ini`
                , `fin_respon_doc`.`fecha_fin`
            FROM
                `fin_respon_doc`
                INNER JOIN `fin_maestro_doc` 
                    ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
                INNER JOIN `tb_terceros` 
                    ON (`fin_respon_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `fin_tipo_control` 
                    ON (`fin_respon_doc`.`tipo_control` = `fin_tipo_control`.`id_tipo`)
            WHERE (`fin_maestro_doc`.`id_modulo` = 54 AND `fin_maestro_doc`.`id_doc_fte` = 23 
                AND `fin_respon_doc`.`fecha_fin` >= '$fecha' 
                AND `fin_respon_doc`.`fecha_ini` <= '$fecha'
                AND `fin_respon_doc`.`estado` = 1
                AND `fin_maestro_doc`.`estado` = 1)";
    $res = $cmd->query($sql);
    $responsables = $res->fetchAll();
    $key = array_search('4', array_column($responsables, 'tipo_control'));
    $nom_respon = $key !== false ? $responsables[$key]['nom_tercero'] : '';
    $cargo_respon = $key !== false ? $responsables[$key]['cargo'] : '';
    $gen_respon = $key !== false ? $responsables[$key]['genero'] : '';
    $control = $key !== false ? $responsables[$key]['control_doc'] : '';
    $control = $control == '' || $control == '0' ? false : true;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
                <div class="col-lg"><label><?= $gen_respon == 'M' ? 'EL' : 'LA'; ?> SUSCRIT<?= $gen_respon == 'M' ? 'O' : 'A'; ?> <?php echo strtoupper($cargo_respon); ?></label></div>
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
                    <p>Que, en el presupuesto de la entidad <strong><?php echo $empresa['nombre']; ?></strong>, aprobado para la vigencia fiscal <?php echo $vigencia; ?>, se realizó una modificación presupuestal de acuerdo al siguiente detalle:</p>
                </div>
            </div>
        </div>
        <div class="col lead">
            <h3><?php echo $anulado ?></h3>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left'>TIPO:</td>
                <td class='text-left'><label><?php echo $cdp['tipo']; ?></label></td>
            </tr>
            <tr>
                <td class='text-left'>NÚMERO:</td>
                <td class='text-left'><label><?php echo $cdp['acto'] . '-' . $cdp['id_manu']; ?></label></td>
            </tr>
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

        </table>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong><?php echo $etiqueta1; ?> </strong></div>
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
                $afecta = $rp['valor_deb'];
                if ($afecta > 0) {
                    echo "<tr>
                            <td class='text-left'>" . $rp['cod_pptal'] . "</td>
                            <td class='text-left'>" . $rp['nom_rubro'] . "</td>
                            <td style='text-align:right'>" . number_format($afecta, 2, ",", ".")  . "</td>
                        </tr>";
                }
            }
            ?>

        </table>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong><?php echo $etiqueta2; ?> </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;<?php echo $vertabla; ?>">
            <tr>
                <td>Código</td>
                <td>Nombre</td>
                <td>Valor</td>
            </tr>
            <?php
            foreach ($rubros as $rp) {
                $rubro = $rp['cod_pptal'];
                $afecta = $rp['valor_cred'];
                if ($afecta > 0) {
                    echo "<tr>
                            <td class='text-left'>" . $rp['cod_pptal'] . "</td>
                            <td class='text-left'>" . $rp['nom_rubro'] . "</td>
                            <td style='text-align:right'>" . number_format($afecta, 2, ",", ".")  . "</td>
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
                    <div><?= $nom_respon; ?> </div>
                    <div><?= $cargo_respon; ?> </div>
                </div>
            </div>
        </div>
        </br>
        </br>
        <?php
        if ($control) {
        ?>
            <table class="table-bordered bg-light" style="width:100% !important;font-size: 10px;">
                <tr style="text-align:left">
                    <td style="width:33%">
                        <strong>Elaboró:</strong>
                    </td>
                    <td style="width:33%">
                        <strong>Revisó:</strong>
                    </td>
                    <td style="width:33%">
                        <strong>Aprobó:</strong>
                    </td>
                </tr>
                <tr style="text-align:center">
                    <td>
                        <?= trim($cdp['usuario_act']) == '' ? $cdp['usuario'] : $cdp['usuario_act'] ?>
                    </td>
                    <td>
                        <?php
                        $key = array_search('2', array_column($responsables, 'tipo_control'));
                        $nombre = $key !== false ? $responsables[$key]['nom_tercero'] : '';
                        $cargo = $key !== false ? $responsables[$key]['cargo'] : '';
                        echo $nombre . '<br> ' . $cargo;
                        ?>
                    </td>
                    <td>
                        <?php
                        $key = array_search('2', array_column($responsables, 'tipo_control'));
                        $nombre = $key !== false ? $responsables[$key]['nom_tercero'] : '';
                        $cargo = $key !== false ? $responsables[$key]['cargo'] : '';
                        echo $nombre . '<br> ' . $cargo;
                        ?>
                    </td>
                </tr>
            </table>
        <?php
        }
        ?>

    </div>

</div>