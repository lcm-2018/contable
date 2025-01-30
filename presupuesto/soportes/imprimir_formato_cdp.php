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
                , `pto_cdp`.`estado`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`
                , `seg_usuarios_sistema`.`apellido2`) AS `usuario`
                , CONCAT_WS(' ', `seg_usuarios_sistema_1`.`nombre1`
                , `seg_usuarios_sistema_1`.`nombre2`
                , `seg_usuarios_sistema_1`.`apellido1`
                , `seg_usuarios_sistema_1`.`apellido2`) AS `usuario_act`
            FROM
                `pto_cdp`
                LEFT JOIN `seg_usuarios_sistema` 
                    ON (`pto_cdp`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)
                LEFT JOIN `seg_usuarios_sistema` AS `seg_usuarios_sistema_1`
                    ON (`pto_cdp`.`id_user_act` = `seg_usuarios_sistema_1`.`id_usuario`)
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
$anulado = $cdp['estado'] == '0' ? 'ANULADO' : '';
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
$fecha = date('Y-m-d', strtotime($cdp['fecha']));
$id_modulo = 54;
$doc_fte = 21;
include '../../financiero/encabezado_imp.php';
try {
    $sql = "SELECT
                `pto_cargue`.`cod_pptal`
                ,`pto_cargue`.`nom_rubro`
            FROM
                `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE (`pto_presupuestos`.`id_vigencia` = $id_vigencia AND `pto_presupuestos`.`id_tipo` = 2 $where)";
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

$enletras = numeroLetras($total);

$dto = $cdp['estado'] == '0' ? 0 : $dto;
?>
<div class="text-right py-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecCdp('areaImprimir', <?php echo $dto ?>);"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="p-2 " style="width:90% !important;margin: 0 auto;">
        <table style="width: 100%;">
            <thead class="bg-light text-dark">
                <tr>
                    <td>
                        <?php
                        echo $html;
                        ?>
                    </td>
                </tr>

            </thead>
            <tbody>
                <tr>
                    <td>
                        <div>
                            <p style="text-align: center;"><b>CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL No: <?php echo $cdp['id_manu']; ?></b></p>
                        </div>
                        <div>
                            <p style="text-align: center;"><?= $gen_respon == 'M' ? 'EL' : 'LA'; ?> SUSCRIT<?= $gen_respon == 'M' ? 'O' : 'A'; ?> <?php echo strtoupper($cargo_respon); ?></p>
                        </div>
                        <div>
                            <br>
                            <p style="text-align: center;">CERTIFICA:</p>
                        </div>
                        <div style="text-align: justify;">
                            <p>Que, en el presupuesto de gastos de la entidad <strong><?php echo $empresa['nombre']; ?></strong>, aprobado para la vigencia fiscal <?php echo $vigencia; ?> existe saldo disponible y libre de afectación para respaldar un compromiso de conformidad con la siguiente imputación presupuestal y detalle:</p>
                            <div>
                                <div class="watermark">
                                    <h3><?php echo $anulado ?></h3>
                                </div>
                                <table style="width:100% !important; text-align: justify;" class="bordeado">
                                    <tr>
                                        <td style="width:22%">FECHA:</td>
                                        <td><?php echo $fecha; ?></td>
                                    </tr>
                                    <tr>
                                        <td>OBJETO:</td>
                                        <td><?php echo $cdp['objeto']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>VALOR:</td>
                                        <td><label><?php echo $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
                                    </tr>
                                    <tr>
                                        <td>NO SOLICITUD:</td>
                                        <td><label><?php echo $cdp['num_solicitud']; ?></label></td>
                                    </tr>
                                </table>
                            </div>
                            <div style="padding-top: 14px;">
                                <table style="width:100% !important; text-align:center;" class="bordeado">
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
                                            <td style='text-align:left'>" . $key . "</td>
                                            <td style='text-align:left'>" . $rubro . "</td>
                                            <td style='text-align:right'>" . number_format($val, 2, ",", ".")  . "</td>
                                        </tr>";
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
                                            <td style="width:34%">
                                                <strong>Aprobó:</strong>
                                            </td>
                                        </tr>
                                        <tr style="text-align:center">
                                            <td>
                                                <?= trim($cdp['usuario_act']) == '' ? $cdp['usuario'] : $cdp['usuario_act'] ?>
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
            <tfoot></tfoot>
        </table>
    </div>
</div>