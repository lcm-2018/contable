<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
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
include '../../terceros.php';
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
                , `pto_crp`.`estado`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`apellido2`
                , `seg_usuarios_sistema`.`apellido1`) AS `usuario`
                , CONCAT_WS(' ', `seg_usuarios_sistema_1`.`nombre1`
                , `seg_usuarios_sistema_1`.`nombre2`
                , `seg_usuarios_sistema_1`.`apellido1`
                , `seg_usuarios_sistema_1`.`apellido2`) AS `usuario_act`
                , `pto_cdp`.`id_manu` AS `num_cdp`
                , `tb_terceros`.`nit_tercero` AS `no_doc`
            FROM
                `pto_crp`
                INNER JOIN `seg_usuarios_sistema` 
                    ON (`pto_crp`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)
                INNER JOIN `pto_cdp`
                    ON (`pto_crp`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                LEFT JOIN `tb_terceros` 
                    ON (`pto_crp`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN `seg_usuarios_sistema` AS `seg_usuarios_sistema_1`
                    ON (`pto_cdp`.`id_user_act` = `seg_usuarios_sistema_1`.`id_usuario`)
            WHERE (`pto_crp`.`id_pto_crp` = $id_crp)";
    $res = $cmd->query($sql);
    $crp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$anulado = $crp['estado'] == '0' ? 'ANULADO' : '';
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
$fecha = date('Y-m-d', strtotime($crp['fecha']));
// Consulto responsable del documento
try {
    $sql = "SELECT
                `fin_maestro_doc`.`control_doc`
                , `fin_maestro_doc`.`acumula`
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
            WHERE (`fin_maestro_doc`.`id_modulo` = 54 AND `fin_maestro_doc`.`id_doc_fte` = 22 
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
    $ver_acumula = $responsables[0]['acumula'] == 1 ?  true : false;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$where = '';
if ($ver_acumula) {
    $where = "AND `pto_cargue`.`tipo_dato` = 1";
}
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
try {
    $sql = "SELECT `razon_social_ips` AS `nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver` FROM `tb_datos_ips`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$enletras = numeroLetras($total);

// Consulta terceros en la api ********************************************* API
$id_t[] = $crp['id_tercero_api'];
$ids = implode(',', $id_t);
$dat_ter = getTerceros($ids, $cmd);
$tercero = !empty($dat_ter) ? $dat_ter[0]['nom_tercero'] : '---';
// fin api terceros ******************************************************** 
$id_crp = $crp['estado'] == '0' ? 0 : $id_crp;
?>
<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecCrp('areaImprimir', <?php echo $id_crp ?>);"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <style>
        /* Estilos para la pantalla */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            /* Se añade rotación */
            font-size: 100px;
            color: rgba(255, 0, 0, 0.2);
            /* Cambia la opacidad para que sea tenue */
            z-index: 1000;
            pointer-events: none;
            /* Para que no interfiera con el contenido */
            white-space: nowrap;
            /* Evita que el texto se divida en varias líneas */
        }

        /* Estilos específicos para la impresión */
        @media print {

            body {
                position: relative;
            }

            .watermark {
                position: fixed;
                /* Cambiar a 'fixed' para impresión */
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 100px;
                color: rgba(255, 0, 0, 0.2);
                /* Asegura que el color y opacidad se mantengan */
                z-index: -1;
                /* Colocar detrás del contenido impreso */
            }
        }
    </style>
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
                    <p><?= $gen_respon == 'M' ? 'El' : 'La'; ?> suscrit<?= $gen_respon == 'M' ? 'o' : 'a'; ?> <?php echo $cargo_respon; ?> de la entidad <strong><?php echo $empresa['nombre']; ?></strong>, CERTIFICA que se realizó registro presupuestal para respaldar un compromiso de acuerdo al siguiente detalle:</p>
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
        <div class="watermark">
            <h3><?php echo $anulado ?></h3>
        </div>
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
                </div>
            </div>
        </div> </br>
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
                        <?= trim($crp['usuario_act']) == '' ? $crp['usuario'] : $crp['usuario_act'] ?>
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