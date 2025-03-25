<?php
session_start();
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$vigencia = $_SESSION['vigencia'];
$id = $_POST['id'];
$mes = $_POST['mes'];
$vigencia = $_SESSION['vigencia'];

function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../permisos.php';
include '../../financiero/consultas.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT `fin_mes`, `nom_mes` FROM `nom_meses` WHERE (`codigo` = '$mes')";
    $rs = $cmd->query($sql);
    $dia = $rs->fetch(PDO::FETCH_ASSOC);
    $fin_mes = !(empty($dia)) ? $vigencia . '-' . $mes . '-' . $dia['fin_mes'] : 0;
    $nom_mes = !(empty($dia)) ? mb_strtoupper($dia['nom_mes']) : '';
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `ctb_doc`.`fecha`
                , `ctb_fuente`.`cod`
                , `ctb_doc`.`id_manu`
                , `ctb_libaux`.`id_tercero_api`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , `ctb_libaux`.`id_ctb_libaux`
                , `tes_conciliacion_detalle`.`id_ctb_libaux` AS `conciliado`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `tes_cuentas` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                INNER JOIN `ctb_doc` 
                    ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `tes_conciliacion_detalle`
                    ON (`tes_conciliacion_detalle`.`id_ctb_libaux` = `ctb_libaux`.`id_ctb_libaux`)
                LEFT JOIN `tes_conciliacion`
                    ON (`tes_conciliacion`.`id_conciliacion` = `tes_conciliacion_detalle`.`id_concilia`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_libaux`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE (`tes_cuentas`.`id_tes_cuenta` = $id AND `ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` <= '$fin_mes'
                    AND (`tes_conciliacion`.`mes` = '$mes' OR `tes_conciliacion`.`mes` IS NULL)
                    AND (`tes_conciliacion`.`vigencia` = '$vigencia' OR `tes_conciliacion`.`vigencia` IS NULL))";
    $rs = $cmd->query($sql);
    $lista = $rs->fetchAll();
    $tot_deb = 0;
    $tot_cre = 0;
    $tdc = 0;
    $tcc = 0;
    foreach ($lista as $lp) {
        $tot_deb += $lp['debito'];
        $tot_cre += $lp['credito'];
        if ($lp['conciliado'] > 0) {
            $tdc += $lp['debito'];
            $tcc += $lp['credito'];
        }
    }
    $tot_deb = $tot_deb - $tdc;
    $tot_cre = $tot_cre - $tcc;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `tes_conciliacion`.`id_conciliacion`
                , `tes_conciliacion`.`saldo_extracto`
                , `tes_conciliacion`.`estado`
                , IFNULL(`t1`.`debito`,0) AS `debito`
                , IFNULL(`t1`.`credito`,0) AS `credito`
            FROM
                `tes_conciliacion`
                INNER JOIN `tes_cuentas` 
                    ON (`tes_conciliacion`.`id_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                LEFT JOIN
                (SELECT
                    `tes_conciliacion_detalle`.`id_concilia`
                    , SUM(`ctb_libaux`.`debito`) AS `debito`
                    , SUM(`ctb_libaux`.`credito`) AS `credito`
                FROM
                    `tes_conciliacion_detalle`
                    INNER JOIN `ctb_libaux` 
                        ON (`tes_conciliacion_detalle`.`id_ctb_libaux` = `ctb_libaux`.`id_ctb_libaux`)
                GROUP BY `tes_conciliacion_detalle`.`id_concilia`) AS `t1`
                ON (`t1`.`id_concilia` = `tes_conciliacion`.`id_conciliacion`)
            WHERE (`tes_cuentas`.`id_tes_cuenta` = $id AND `tes_conciliacion`.`vigencia` = '$vigencia' AND `tes_conciliacion`.`mes` = '$mes')";
    $rs = $cmd->query($sql);
    $data = $rs->fetch(PDO::FETCH_ASSOC);
    if (!empty($data)) {
        $id_conciliacion = $data['id_conciliacion'];
        $saldo = $data['saldo_extracto'];
        $estado = $data['estado'];
        $debito = $data['debito'];
        $credito = $data['credito'];
    } else {
        $id_conciliacion = 0;
        $saldo = 0;
        $estado = 0;
        $debito = 0;
        $credito = 0;
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                    `tb_bancos`.`id_banco`
                    , `tes_cuentas`.`id_cuenta`
                    , `tes_cuentas`.`id_tes_cuenta`
                    , `tb_bancos`.`nom_banco`
                    , `tes_tipo_cuenta`.`tipo_cuenta`
                    , `tes_cuentas`.`numero`
                    , `tes_cuentas`.`nombre` AS `descripcion`
                    , `t1`. `debito`
                    , `t1`.`credito`
                    , `ctb_pgcp`.`cuenta` AS `cta_contable`
                FROM
                    `tes_cuentas`
                    INNER JOIN `ctb_pgcp` 
                        ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    INNER JOIN `tb_bancos` 
                        ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                    INNER JOIN `tes_tipo_cuenta` 
                        ON (`tes_cuentas`.`id_tipo_cuenta` = `tes_tipo_cuenta`.`id_tipo_cuenta`)
                    INNER JOIN 
                        (SELECT
                            `ctb_libaux`.`id_cuenta`
                            , SUM(`ctb_libaux`.`debito`) AS `debito` 
                            , SUM(`ctb_libaux`.`credito`) AS `credito`
                            , `ctb_doc`.`fecha`
                        FROM
                            `ctb_libaux`
                            INNER JOIN `ctb_doc` 
                                ON (`ctb_libaux`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                            LEFT JOIN `tes_conciliacion_detalle`
                                ON (`ctb_libaux`.`id_ctb_libaux` = `tes_conciliacion_detalle`.`id_ctb_libaux`)
                            LEFT JOIN `tes_conciliacion`
                                ON (`tes_conciliacion`.`id_conciliacion` = `tes_conciliacion_detalle`.`id_concilia`)
                        WHERE (`ctb_doc`.`estado` = 2 AND `ctb_doc`.`fecha` <= '$fin_mes' 
                                AND (`tes_conciliacion`.`mes` = '$mes' OR `tes_conciliacion`.`mes` IS NULL)
                                AND (`tes_conciliacion`.`vigencia` = '$vigencia' OR `tes_conciliacion`.`vigencia` IS NULL))
                        GROUP BY `ctb_libaux`.`id_cuenta`)AS `t1`  
                        ON (`t1`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                WHERE `tes_cuentas`.`id_tes_cuenta` = $id";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT 
                `tb_datos_ips`.`razon_social_ips` AS `nombre`, `tb_datos_ips`.`nit_ips` AS `nit`, `tb_datos_ips`.`dv` AS `dig_ver`, `tb_municipios`.`nom_municipio`
            FROM `tb_datos_ips`
                INNER JOIN `tb_municipios`
                    ON (`tb_datos_ips`.`idmcpio` = `tb_municipios`.`id_municipio`)";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// si tipo de documento es CICP es un recibo de caja

$fecha = date('Y-m-d', strtotime($fin_mes));
// fechas para factua
// Consulto responsable del documento
try {
    $sql = "SELECT
                `fin_maestro_doc`.`control_doc`
                , `fin_maestro_doc`.`id_doc_fte`
                , `fin_maestro_doc`.`costos`
                , `ctb_fuente`.`nombre`
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
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_fuente`.`id_doc_fuente` = `fin_maestro_doc`.`id_doc_fte`)
                INNER JOIN `tb_terceros` 
                    ON (`fin_respon_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `fin_tipo_control` 
                    ON (`fin_respon_doc`.`tipo_control` = `fin_tipo_control`.`id_tipo`)
            WHERE (`fin_maestro_doc`.`id_modulo` = 56 AND `ctb_fuente`.`cod` = 'CONC'
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
    $nombre_doc = $key !== false ? $responsables[$key]['nombre'] : '';
    $ver_costos = isset($responsables[0]) && $responsables[0]['costos'] == 1 ? false : true;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$anulado = '';

?>
<div class="text-right py-3">
    <?php if (PermisosUsuario($permisos, 5601, 6)  || $id_rol == 1) { ?>
        <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir',0);"> Imprimir</a>
    <?php } ?>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <style>
        /* CSS para replicar la clase .row */
        .row-custom {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .row-custom>div {
            padding-right: 15px;
            padding-left: 15px;
        }

        /* Opcional: columnas */
        .col-6-custom {
            flex: 0 0 50%;
            /* Toma el 50% del ancho */
            max-width: 50%;
            /* Limita el ancho máximo al 50% */
        }

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


        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col lead"><label><strong>CONCILIACIÓN BANCARIA</strong></label></div>
            </div>
        </div>
        <div class="watermark">
            <h3><?php echo $anulado ?></h3>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left' style="width:25%">CÓDIGO CUENTA:</td>
                <td class='text-left'><?= $detalles['cta_contable'] ?></td>
            </tr>
            <tr>
                <td class='text-left'>NOMBRE CUENTA:</td>
                <td class='text-left'><?= $detalles['descripcion']; ?></td>
            </tr>
            <tr>
                <td class='text-left'>MES CONCILIADO:</td>
                <td class='text-left'><?= $nom_mes; ?></td>
            </tr>
            <tr>
                <td class='text-left'>AÑO:</td>
                <td class='text-left'><?php echo $vigencia; ?></td>
            </tr>
        </table>
        <br>
        <table style="width:100% !important; border-collapse: collapse;" border="1">
            <tr>
                <td style="text-align: left; width: 50%;">SALDO EN LIBROS (contable)</td>
                <td style="text-align: right; width: 25%"><?= pesos($detalles['debito'] - $detalles['credito']); ?></td>
                <td style="text-align: left; width: 25%"></td>
            </tr>
            <tr>
                <td style="text-align: left;">Total Débitos Pendientes (++)</td>
                <td style="text-align: left;"></td>
                <td style="text-align: right;"><?= pesos($tot_deb); ?></td>
            </tr>
            <tr>
                <td style="text-align: left;">Total Créditos Pendientes (-)</td>
                <td style="text-align: left;"></td>
                <td style="text-align: right;"><?= pesos($tot_cre); ?></td>
            </tr>
            <tr>
                <td style="text-align: left;">SALDO EN LIBROS EXTRACTO</td>
                <td style="text-align: left;"></td>
                <td style="text-align: right;"><?= pesos($saldo); ?></td>
            </tr>
            <tr>
                <td style="text-align: left;">SUMAS IGUALES</td>
                <td style="text-align: right;"><?= pesos($detalles['debito'] - $detalles['credito']); ?></td>
                <td style="text-align: right;"><?= pesos($tot_deb + $tot_cre + $saldo); ?></td>
            </tr>
        </table>
        </br>
        <table style="width:100% !important; border-collapse: collapse; font-size: 12px;" border="1">
            <tr>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Tercero</th>
                <th>Documento</th>
                <th>Débito</th>
                <th>Crédito</th>
            </tr>
            <?php
            $tdebito = 0;
            $tcredito = 0;
            foreach ($lista as $l) {
                if ($l['conciliado'] == '') {
                    $tdebito += $l['debito'];
                    $tcredito += $l['credito'];

            ?>
                    <tr style="text-align: left;">
                        <td><?= date('Y-m-d', strtotime($l['fecha'])); ?></td>
                        <td><?= $l['cod'] . $l['id_manu']; ?></td>
                        <td><?= $l['nom_tercero'] . ' - ' . $l['nit_tercero']; ?></td>
                        <td><?= '' ?></td>
                        <td style="text-align: right;"><?= pesos($l['debito']); ?></td>
                        <td style="text-align: right;"><?= pesos($l['credito']); ?></td>
                    </tr>
            <?php
                }
            }
            ?>
            <tr>
                <th colspan="4">TOTAL</th>
                <th style="text-align: right;"><?= pesos($tdebito); ?></th>
                <th style="text-align: right;"><?= pesos($tcredito); ?></th>
            </tr>
        </table>
        <br><br><br>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center">
                    <div>___________________________________</div>
                    <div><?php echo $nom_respon; ?> </div>
                    <div><?php echo $cargo_respon; ?> </div>
                </td>
            </tr>
        </table>
        </br> </br> </br>
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
                        <br><br>
                        <?= trim($documento['usuario']) ?>
                        <br>
                        <?= trim($documento['cargo']) ?>
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
        </br> </br>
    </div>

</div>