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
$id_modulo = 54;
$doc_fte = 23;
include '../../financiero/encabezado_imp.php';
?>
<div class="text-right py-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecMod('areaImprimir',<?= $dto ?>);"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">
        <table style="width: 100%;">
            <thead class="bg-light text-dark">
                <tr>
                    <td>
                        <?= $html; ?>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div>
                            <p style="text-align: center;"><?= $gen_respon == 'M' ? 'EL' : 'LA'; ?> SUSCRIT<?= $gen_respon == 'M' ? 'O' : 'A'; ?> <?php echo strtoupper($cargo_respon); ?></b></p>
                        </div>
                        <div>
                            <br>
                            <p style="text-align: center;">CERTIFICA:</p>
                        </div>
                        <div style="text-align: justify;">
                            <p>Que, en el presupuesto de la entidad <strong><?= $empresa['nombre']; ?></strong>, aprobado para la vigencia fiscal <?= $vigencia; ?>, se realizó una modificación presupuestal de acuerdo al siguiente detalle:</p>
                            <div>
                                <div class="watermark">
                                    <h3><?= $anulado ?></h3>
                                </div>
                                <table style="width:100% !important; text-align: justify;" class="bordeado">
                                    <tr>
                                        <td style="width:22%">TIPO:</td>
                                        <td><label><?= $cdp['tipo']; ?></label></td>
                                    </tr>
                                    <tr>
                                        <td>NÚMERO:</td>
                                        <td><label><?= $cdp['acto'] . '-' . $cdp['id_manu']; ?></label></td>
                                    </tr>
                                    <tr>
                                        <td style="width:22%">FECHA:</td>
                                        <td><?= $fecha; ?></td>
                                    </tr>
                                    <tr>
                                        <td>OBJETO:</td>
                                        <td><?= $cdp['objeto']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>VALOR:</td>
                                        <td><label><?= $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
                                    </tr>
                                </table>
                            </div>
                            <div style="padding-top: 14px;">
                                <div class="row">
                                    <div class="col-12">
                                        <div style="text-align: left">
                                            <div><strong><?php echo $etiqueta1; ?> </strong></div>
                                        </div>
                                    </div>
                                </div>
                                <table class="bordeado" style="width:100% !important;">
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
                                            // todos los $rp['cod_pptal'] que empiecen por 1
                                            if (substr($rp['cod_pptal'], 0, 1) == '1') {
                                                echo "<tr>
                                                        <td class='text-left'>" . $rp['cod_pptal'] . "</td>
                                                        <td class='text-left'>" . $rp['nom_rubro'] . "</td>
                                                        <td style='text-align:right'>" . number_format($afecta, 2, ",", ".")  . "</td>
                                                    </tr>";
                                            }
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
                                <table class="bordeado" style="width:100% !important;">
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
                                            // todos los $rp['cod_pptal'] que empiecen por 1
                                            if (substr($rp['cod_pptal'], 0, 1) == '2') {
                                                echo "<tr>
                                                        <td class='text-left'>" . $rp['cod_pptal'] . "</td>
                                                        <td class='text-left'>" . $rp['nom_rubro'] . "</td>
                                                        <td style='text-align:right'>" . number_format($afecta, 2, ",", ".")  . "</td>
                                                    </tr>";
                                            }
                                        }
                                    }
                                    ?>

                                </table>
                            </div>
                            <div style="text-align: center; padding-top: 60px;">
                                <div>___________________________________</div>
                                <div><?= $nom_respon; ?> </div>
                                <div><?= $cargo_respon; ?> </div>
                            </div>
                            <div style="text-align: center; padding-top: 30px;">
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
                                                <?= trim($cdp['usuario_act']) == '' ? $crp['usuario'] : $crp['usuario_act'] ?>
                                            </td>
                                            <td>
                                                <br><br>
                                                <?php
                                                $key = array_search('2', array_column($responsables, 'tipo_control'));
                                                $nombre = $key !== false ? $responsables[$key]['nom_tercero'] : '';
                                                $cargo = $key !== false ? $responsables[$key]['cargo'] : '';
                                                echo $nombre . '<br> ' . $cargo;
                                                ?>
                                            </td>
                                            <td>
                                                <br><br>
                                                <?php
                                                $key = array_search('3', array_column($responsables, 'tipo_control'));
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
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>