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
                `pto_rad`.`objeto`
                , `pto_rad`.`id_manu`
                , `pto_rad`.`fecha`
                , `pto_rad`.`num_factura`
                , `pto_rad`.`fecha_reg` AS `fec_reg`
                , `pto_rad`.`estado`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`
                , `seg_usuarios_sistema`.`apellido2`) AS `usuario`
                , CONCAT_WS(' ', `seg_usuarios_sistema_1`.`nombre1`
                , `seg_usuarios_sistema_1`.`nombre2`
                , `seg_usuarios_sistema_1`.`apellido1`
                , `seg_usuarios_sistema_1`.`apellido2`) AS `usuario_act`
                , `seg_usuarios_sistema`.`descripcion` AS `cargo`
                , `tb_terceros`.`nom_tercero` AS `tercero`
                , `tb_terceros`.`nit_tercero` AS `nit_tercero`
            FROM
                `pto_rad`
                LEFT JOIN `seg_usuarios_sistema` 
                    ON (`pto_rad`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)
                LEFT JOIN `seg_usuarios_sistema` AS `seg_usuarios_sistema_1`
                    ON (`pto_rad`.`id_user_act` = `seg_usuarios_sistema_1`.`id_usuario`)
                LEFT JOIN `tb_terceros` 
                    ON (`pto_rad`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE (`pto_rad`.`id_pto_rad` = $dto)";
    $res = $cmd->query($sql);
    $rad = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_pto_rad`, SUM(`valor`) AS `debito`, SUM(`valor_liberado`) AS `credito`
            FROM
                `pto_rad_detalle`
            WHERE (`id_pto_rad` = $dto)";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['debito'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$anulado = $rad['estado'] == '0' ? 'ANULADO' : '';
try {
    $sql = "SELECT
                `pto_rad_detalle`.`id_pto_rad`
                , `pto_rad_detalle`.`valor`
                , `pto_rad_detalle`.`valor_liberado`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`
                , `pto_tipo`.`nombre`
            FROM
                `pto_rad_detalle`
                INNER JOIN `pto_cargue` 
                    ON (`pto_rad_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
                INNER JOIN `pto_tipo` 
                    ON (`pto_presupuestos`.`id_tipo` = `pto_tipo`.`id_tipo`)
            WHERE (`pto_rad_detalle`.`id_pto_rad`  = $dto)";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date('Y-m-d', strtotime($rad['fecha']));
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

// consulto el nombre de la empresa de la tabla tb_datos_ips

$enletras = numeroLetras($total);

$dto = $rad['estado'] == '0' ? 0 : $dto;
?>
<div class="text-right py-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecRad('areaImprimir', <?php echo $dto ?>);"> Imprimir</a>
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
                            <p style="text-align: center;"><b>RECONOCIMIENTO PRESUPUESTAL No: <?php echo $rad['id_manu']; ?></b></p>
                        </div>
                        <div style="text-align: justify;">
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
                                        <td>TERCERO:</td>
                                        <td><?= $rad['tercero'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>CC/NIT:</td>
                                        <td><?= number_format($rad['nit_tercero'], 0, ",", ".") ?></td>
                                    </tr>
                                    <tr>
                                        <td>OBJETO:</td>
                                        <td><?php echo $rad['objeto']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>VALOR:</td>
                                        <td><label><?php echo $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
                                    </tr>
                                    <tr>
                                        <td>No. FACTURA:</td>
                                        <td><label><?php echo $rad['num_factura']; ?></label></td>
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
                                    $total = 0;
                                    foreach ($rubros as $rb) {
                                        $key = $rb['cod_pptal'];
                                        $rubro = $rb['nom_rubro'];
                                        $val = $rb['valor'];
                                        $total += $val;
                                        echo "<tr>
                                            <td style='text-align:left'>" . $key . "</td>
                                            <td style='text-align:left'>" . $rubro . "</td>
                                            <td style='text-align:right'>" . number_format($val, 2, ",", ".")  . "</td>
                                        </tr>";
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="2" style="text-align:right"><strong>TOTAL</strong></td>
                                        <td style="text-align:center"><strong><?php echo number_format($total, 2, ",", "."); ?></strong></td>
                                </table>
                            </div>
                            <div style="text-align: center; padding-top: 60px; font-size: 13px;">
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
                                                <br><br>
                                                <?= trim($rad['usuario_act']) == '' ? $rad['usuario'] : $rad['usuario_act'] ?>
                                                <br>
                                                <?= $rad['cargo'] ?>

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