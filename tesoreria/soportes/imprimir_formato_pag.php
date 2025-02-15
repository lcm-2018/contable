<?php
session_start();
date_default_timezone_set('America/Bogota');
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$vigencia = $_SESSION['vigencia'];
$id_doc = $_POST['id'];
$num_doc = '';
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../permisos.php';
include '../../financiero/consultas.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_tipo_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`estado`
                , `ctb_fuente`.`cod`
                , `ctb_fuente`.`nombre`
                , `ctb_doc`.`id_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `ctb_doc`.`fecha_reg`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`
                , `seg_usuarios_sistema`.`apellido2`) AS `usuario`
            FROM
                `ctb_doc`
                INNER JOIN `seg_usuarios_sistema` 
                    ON (`ctb_doc`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $res = $cmd->query($sql);
    $documento = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$nom_doc = $documento['nombre'];
$cod_doc = $documento['cod'];
$tercero = $documento['nom_tercero'];
$num_doc = $documento['nit_tercero'];
// Valor total del registro
try {
    $sql = "SELECT `id_ctb_doc` , SUM(`debito`) AS `valor` FROM `ctb_libaux` WHERE (`id_ctb_doc` = $id_doc)";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['valor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar el id del crrp para saber si es un pago presupuestal
try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `pto_crp_detalle`.`id_pto_crp`
            FROM
                `pto_pag_detalle`
                INNER JOIN `pto_cop_detalle` 
                    ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                INNER JOIN `ctb_doc` 
                    ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc) LIMIT 1 ";
    $res = $cmd->query($sql);
    $datos_crpp = $res->fetch(PDO::FETCH_ASSOC);
    $id_crpp = !empty($datos_crpp) ? $datos_crpp['id_pto_crp'] : 0;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$rubros = [];
if ($id_crpp > 0) {
    try {
        $sql = "SELECT
                    `ctb_doc`.`id_ctb_doc`
                    , `pto_cop_detalle`.`valor`
                    , `pto_cargue`.`nom_rubro`
                    , `pto_cargue`.`cod_pptal` AS `rubro`
                    , `ctb_doc`.`id_manu`
                FROM
                    `pto_pag_detalle`
                    INNER JOIN `pto_cop_detalle` 
                        ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                    INNER JOIN `ctb_doc` 
                        ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `pto_crp_detalle` 
                        ON (`pto_cop_detalle`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
                    INNER JOIN `pto_cdp_detalle` 
                        ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                    INNER JOIN `pto_cargue` 
                        ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
        $res = $cmd->query($sql);
        $rubros = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Consulto el numero de documentos asociados al pago 
    try {
        $sql = "SELECT
                    `ctb_doc`.`id_manu`
                    , `ctb_factura`.`num_doc`
                    , `ctb_tipo_doc`.`tipo`
                    , `ctb_factura`.`fecha_fact`
                    , `ctb_factura`.`fecha_ven`
                    , `ctb_factura`.`valor_pago`
                    , `ctb_factura`.`valor_iva`
                    , `ctb_factura`.`valor_base`
                    , `descuento`.`dcto`
                FROM
                    `pto_pag_detalle`
                    INNER JOIN `pto_cop_detalle` 
                        ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                    INNER JOIN `ctb_doc` 
                        ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `ctb_factura` 
                        ON (`ctb_factura`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    INNER JOIN `ctb_tipo_doc` 
                        ON (`ctb_factura`.`id_tipo_doc` = `ctb_tipo_doc`.`id_ctb_tipodoc`)
                    LEFT JOIN
                        (SELECT
                            SUM(`ctb_causa_retencion`.`valor_retencion`) AS `dcto`
                            , `pto_pag_detalle`.`id_ctb_doc`
                        FROM
                            `pto_pag_detalle`
                            INNER JOIN `pto_cop_detalle` 
                                ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                            INNER JOIN `ctb_doc` 
                                ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                            INNER JOIN `ctb_causa_retencion` 
                                ON (`ctb_causa_retencion`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        WHERE (`pto_pag_detalle`.`id_ctb_doc` = $id_doc)) AS `descuento`
                        ON (`descuento`.`id_ctb_doc` = `pto_pag_detalle`.`id_ctb_doc`)
                WHERE (`pto_pag_detalle`.`id_ctb_doc` = $id_doc)";
        $rs = $cmd->query($sql);
        $data = $rs->fetch();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
$enletras = numeroLetras($total);
// Movimiento contable
try {
    $sql = "SELECT
                `ctb_libaux`.`id_cuenta`
                , `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `ctb_libaux`.`debito`
                , `ctb_libaux`.`credito`
                , `ctb_libaux`.`id_tercero_api` AS `id_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                `ctb_libaux`
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_libaux`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_libaux`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE (`ctb_libaux`.`id_ctb_doc` = $id_doc)
            ORDER BY `ctb_pgcp`.`cuenta` DESC";
    $res = $cmd->query($sql);
    $movimiento = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

// Consulta para mostrar la forma de pago
try {
    $sql = "SELECT
                `tes_detalle_pago`.`id_detalle_pago`
                ,`tb_bancos`.`nom_banco`
                , `tes_cuentas`.`nombre`
                , `tes_forma_pago`.`forma_pago`
                , `tes_detalle_pago`.`documento`
                , `tes_detalle_pago`.`valor`
                , `tes_detalle_pago`.`id_forma_pago`
            FROM
                `tes_detalle_pago`
                INNER JOIN `tes_forma_pago` 
                    ON (`tes_detalle_pago`.`id_forma_pago` = `tes_forma_pago`.`id_forma_pago`)
                INNER JOIN `tes_cuentas` 
                    ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
            WHERE (`tes_detalle_pago`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $formapago = $rs->fetchAll();
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

if ($documento['id_tipo_doc'] == '9') {
    try {
        $sql = "SELECT
                `tes_causa_arqueo`.`id_causa_arqueo`
                , `tes_causa_arqueo`.`fecha_ini`
                , `tes_causa_arqueo`.`fecha_fin`
                , `tes_causa_arqueo`.`id_tercero`
                , `tes_causa_arqueo`.`valor_arq`
                , `tes_causa_arqueo`.`valor_fac`
                , `tes_causa_arqueo`.`observaciones`
                , `tb_terceros`.`nom_tercero` AS `facturador`
                , `tb_terceros`.`nit_tercero` AS `documento`
            FROM
                `tes_causa_arqueo`
                INNER JOIN `tb_terceros` 
                    ON (`tes_causa_arqueo`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE (`tes_causa_arqueo`.`id_ctb_doc` = $id_doc)";
        $res = $cmd->query($sql);
        $facturadores = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
$fecha = date('Y-m-d', strtotime($documento['fecha']));
$hora = date('H:i:s', strtotime($documento['fecha_reg']));
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
            WHERE (`fin_maestro_doc`.`id_modulo` = 56 AND `ctb_fuente`.`cod` = '$cod_doc'
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
$id_vigencia = $_SESSION['id_vigencia'];
try {
    $sql = "SELECT 	
                `t1`.`consecutivo` AS `cons_asigando`
                , `t2`.`consecutivo` AS `cons_maximo`
            FROM
                (SELECT 
                    MAX(`consecutivo`) AS `consecutivo`
                FROM `tes_resolucion_pago`
                WHERE `id_vigencia` = $id_vigencia) AS `t2`
                LEFT JOIN
                    (SELECT 
                        `consecutivo`
                    FROM `tes_resolucion_pago`
                    WHERE `id_ctb_doc` = $id_doc AND `id_vigencia` = $id_vigencia) AS `t1` 
                ON 1 = 1";
    $res = $cmd->query($sql);
    $consecutivos = $res->fetch(PDO::FETCH_ASSOC);
    $id_user = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $num_resolucion = $vigencia . '0001';

    if ($consecutivos['cons_asigando'] == '' && $consecutivos['cons_maximo'] > 0) {
        $num_resolucion = $consecutivos['cons_maximo'] + 1;
    } else if ($consecutivos['cons_asigando'] > 0) {
        $num_resolucion = $consecutivos['cons_asigando'];
    }
    if ($consecutivos['cons_asigando'] == '' && $cod_doc == 'CEVA') {
        try {
            $sql = "INSERT INTO `tes_resolucion_pago`
	                    (`consecutivo`,`id_ctb_doc`,`id_vigencia`,`id_user_reg`,`fec_reg`)
                    VALUES (?, ?, ?, ?, ?)";
            $cmd->prepare($sql);
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $num_resolucion, PDO::PARAM_INT);
            $sql->bindParam(2, $id_doc, PDO::PARAM_INT);
            $sql->bindParam(3, $id_vigencia, PDO::PARAM_INT);
            $sql->bindParam(4, $id_user, PDO::PARAM_INT);
            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2];
            }
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_forma = 0;
$anulado = $documento['estado'] == '0' ? 'ANULADO' : '';
$meses = [
    '01' => 'enero',
    '02' => 'febrero',
    '03' => 'marzo',
    '04' => 'abril',
    '05' => 'mayo',
    '06' => 'junio',
    '07' => 'julio',
    '08' => 'agosto',
    '09' => 'septiembre',
    '10' => 'octubre',
    '11' => 'noviembre',
    '12' => 'diciembre'
];
?>
<div class="text-right py-3">
    <?php if (PermisosUsuario($permisos, 5601, 6)  || $id_rol == 1) {
        if ($cod_doc == 'CEVA') { ?>
            <a type="button" class="btn btn-info btn-sm" onclick="imprSelecTes('imprimeResolucion',<?php echo $id_doc; ?>);"> Resolución</a>
        <?php } ?>
        <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir',<?php echo $id_doc; ?>);"> Imprimir</a>
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
                <div class="col lead"><label><strong> <?php echo $nom_doc . ' No: ' . $documento['id_manu']; ?></strong></label></div>
            </div>
        </div>
        <div class="watermark">
            <h3><?php echo $anulado ?></h3>
        </div>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Datos generales: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left' style="width:18%">FECHA:</td>
                <td class='text-left'><?php echo $fecha . ' ' . $hora; ?></td>
            </tr>
            <tr>
                <td class='text-left' style="width:18%">TERCERO:</td>
                <td class='text-left'><?php echo $tercero; ?></td>
            </tr>
            <tr>
                <td class='text-left' style="width:18%">CC/NIT:</td>
                <td class='text-left'><?php echo number_format($num_doc, 0, '', '.'); ?></td>
            </tr>
            <tr>
                <td class='text-left'>OBJETO:</td>
                <td class='text-left'><?php echo $documento['detalle']; ?></td>
            </tr>
            <tr>
                <td class='text-left'>VALOR:</td>
                <td class='text-left'><label><?php echo $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
            </tr>
        </table>
        </br>
        <?php if ($id_crpp > 0) {
        ?>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Imputación presupuestal: </strong></div>
                    </div>
                </div>
            </div>
            <table class="table-bordered" style="width:100% !important; border-collapse: collapse; " cellspacing="2">
                <tr>
                    <td style="text-align: left;border: 1px solid black ">Número Rp</td>
                    <td style="border: 1px solid black ">Código</td>
                    <td style="border: 1px solid black ">Nombre</td>
                    <td style="border: 1px solid black;text-align:center">Valor</td>
                </tr>
                <?php
                $total_pto = 0;
                foreach ($rubros as $rp) {
                    echo "<tr>
                <td class='text-left' style='border: 1px solid black '>" . $rp['id_manu'] . "</td>
                <td class='text-left' style='border: 1px solid black '>" . $rp['rubro'] . "</td>
                <td class='text-left' style='border: 1px solid black '>" . $rp['nom_rubro'] . "</td>
                <td class='text-right' style='border: 1px solid black; text-align: right'>" . number_format($rp['valor'], 2, ",", ".")  . "</td>
                </tr>";
                    $total_pto += $rp['valor'];
                }
                ?>
                <tr>
                    <td colspan="3" style="text-align:left;border: 1px solid black ">Total</td>
                    <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_pto, 2, ",", "."); ?></td>
                </tr>
            </table>
            </br>
            <?php
            if (!empty($data)) {
            ?>
                <div class="row">
                    <div class="col-12">
                        <div style="text-align: left">
                            <div><strong>Datos de la factura: </strong></div>
                        </div>
                    </div>
                </div>
                <?php
                $total_pto = 0;
                ?>

                <table class="table-bordered bg-light" style="width:100% !important;">
                    <tr>
                        <td style="text-align: left">Causación</td>
                        <td>Documento</td>
                        <td>Número</td>
                        <td>Fecha</td>
                        <td>Vencimiento</td>
                    </tr>
                    <tr>
                        <td><?php echo   $data['id_manu']; ?></td>
                        <td><?php echo $data['tipo']; ?></td>
                        <td><?php echo $data['num_doc']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($data['fecha_fact'])); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($data['fecha_ven'])); ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Valor factura</td>
                        <td>Valor IVA</td>
                        <td>Base</td>
                        <td>Descuentos</td>
                        <td>Neto</td>
                    </tr>
                    <tr>
                        <td><?php echo number_format($data['valor_pago'], 2, ',', '.'); ?></td>
                        <td><?php echo  number_format($data['valor_iva'], 2, ',', '.');; ?></td>
                        <td><?php echo number_format($data['valor_base'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format($data['dcto'], 2, ',', '.'); ?></td>
                        <td><?php echo number_format(($data['valor_pago'] - $data['dcto']), 2, ',', '.'); ?></td>
                    </tr>
                </table>
                </br>
        <?php
            }
        }
        ?>
        <?php if ($documento['id_tipo_doc'] == '9') { ?>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Detalle facturadores: </strong></div>
                    </div>
                </div>
            </div>
            <table class="table-bordered bg-light" style="width:100% !important; border-collapse: collapse;">
                <tr>
                    <td style="text-align: left;border: 1px solid black">Dcocumento</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Valor arqueo</td>
                    <td style='border: 1px solid black'>Valor entregado</td>
                </tr>
                <?php
                $total_pago = 0;
                foreach ($facturadores as $fac) {
                    echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $fac['id_tercero'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $fac['facturador'] . "</td>
                <td class='text-right' style='border: 1px solid black'>" . number_format($fac['valor_fac'], 2, ',', '.') . "</td>
                <td class='text-right' style='border: 1px solid black'>" . number_format($fac['valor_arq'], 2, ',', '.') . "</td>
                </tr>";
                }
                ?>
            </table>
        <?php }
        ?>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Forma de pago: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important; border-collapse: collapse;">
            <tr>
                <td style="text-align: left;border: 1px solid black">Banco</td>
                <td style='border: 1px solid black'>Cuenta</td>
                <td style='border: 1px solid black'>Forma de pago</td>
                <td style='border: 1px solid black'>Documento</td>
                <td style='border: 1px solid black'>Valor</td>
            </tr>
            <?php
            $total_pago = 0;
            foreach ($formapago as $pg) {
                echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $pg['nom_banco'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $pg['nombre'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $pg['forma_pago'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $pg['documento'] . "</td>
                <td class='text-right' style='border: 1px solid black'>" . number_format($pg['valor'], 2, ',', '.') . "</td>
                </tr>";
                if ($pg['id_forma_pago'] == 2) {
                    $id_forma = 2;
                }
            }
            ?>
        </table>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Movimiento contable: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important; border-collapse: collapse;">
            <?php
            if (true) {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Ccnit</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php
                $tot_deb = 0;
                $tot_cre = 0;
                foreach ($movimiento as $mv) {
                    $ccnit = $mv['nit_tercero'];
                    echo "<tr style='border: 1px solid black'>
                    <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
                    <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] . "</td>
                    <td class='text-left' style='border: 1px solid black'>" .  $ccnit . "</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
                    </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="3">Sumas iguales</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_deb, 2, ",", "."); ?></td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_cre, 2, ",", "."); ?> </td>
                </tr>
            <?php
            } else {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php
                $tot_deb = 0;
                $tot_cre = 0;

                foreach ($movimiento as $mv) {

                    echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
                </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="2">Sumas iguales</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_deb, 2, ",", "."); ?></td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_cre, 2, ",", "."); ?> </td>
                </tr>
            <?php
            }
            ?>

        </table>
        </br>
        </br>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center">
                    <div>___________________________________</div>
                    <div><?php echo $nom_respon; ?> </div>
                    <div><?php echo $cargo_respon; ?> </div>
                </td>
                <?php
                if ($cod_doc == 'CEVA') {
                    if ($_SESSION['nit_emp'] != '844001355' || $id_forma == 2) {
                ?>
                        <td>
                            <div>___________________________________</div>
                            <div><?= $tercero; ?></div>
                            <div>RECIBE CC/NIT:</div>
                        </td>
                <?php
                    }
                }
                ?>
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
                        <?= trim($documento['usuario']) ?>
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
<?php
if ($cod_doc == 'CEVA') {
?>
    <div class="contenedor bg-light" id="imprimeResolucion" style="display: none;">
        <style>
            @media print {
                body {
                    margin: 0;
                    padding: 0;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    page-break-inside: auto;
                }

                thead {
                    display: table-header-group;
                }

                tfoot {
                    display: table-footer-group;
                }

                tbody {
                    display: table-row-group;
                }

                tfoot tr {
                    page-break-inside: avoid;
                    padding-bottom: 50px;
                    width: 100%;
                    text-align: center;
                }

                tr {
                    page-break-inside: avoid;
                }
            }
        </style>
        <?php
        $f_exp = explode('-', $fecha);
        $cadena = [];
        $cad_rubros = [];

        foreach ($rubros as $rp) {
            $cadena[] = $rp['rubro'] . ' - ' . $rp['nom_rubro'];
            $cad_rubros[] = $rp['rubro'] . '-' . $rp['nom_rubro'] . '; según Registro Presupuestal: ' . $rp['id_manu'];
        }
        $cadena = implode(',', $cadena);
        $cad_rubros = implode(',', $cad_rubros);
        ?>
        <div class="px-2 " style="width:90% !important;margin: 0 auto;">
            <table style="width: 100%;" class="page_break_avoid">
                <thead>
                    <tr>
                        <td>
                            <table class="table-bordered bg-light" style="width:100% !important;">
                                <tr>
                                    <td class='text-center' style="width:25%"><label class="small"><img src="../images/logos/logo.png" width="150"></label></td>
                                    <td style="text-align:center">
                                        <strong><?php echo $empresa['nombre']; ?> </strong>
                                        <div>NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:justify">
                            <br>
                            <p style="text-align:center;"><b>RESOLUCIÓN No.: <?php echo $num_resolucion; ?></b></p>
                            <p style="text-align:center;"><b><?= $f_exp[2] . '-' . $meses[$f_exp[1]] . '-' . $f_exp[0] ?></b></p>
                            <p style="text-align:center;">Por medio de la cual se ordena un pago</p>
                            <p>EL GERENTE DE EL(LA) <?= $empresa['nombre'] ?> EN USO DE SUS FACULTADES CONSTITUCIONALES, LEGALES Y ESTATUTARIAS Y CONSIDERANDO</p>
                            <p>Que, dentro del presupuesto de gastos de el(la) <?= $empresa['nombre'] ?>, para la vigencia fiscal del año <?= $vigencia ?>, se encuentra previsto un(os) rubro(s) radicado bajo código(s): <?= $cadena ?>.</p>
                            <p>Que durante la presente vigencia se generaron obligaciones por concepto de: <?= mb_strtoupper($documento['detalle']); ?>, para lo cual se expidieron los respectivos actos administrativos.</p>
                            <p>Por lo anteriormente expuesto:</p>
                            <p style="text-align:center;"><b>RESUELVE</b></p>
                            <p>ARTICULO PRIMERO: Reconocer y ordenar el pago al TESORERO GENERAL, a favor de I<?= $tercero; ?> por la suma de <?php echo $enletras . "  ($" . number_format($total, 2, ",", ".") . ')'; ?> por concepto de <?= mb_strtoupper($documento['detalle']); ?>.</p>
                            <p>ARTICULO SEGUNDO: El valor reconocido en el artículo primero se imputará al (los) rubro(s) <?= $cad_rubros; ?>.</p>
                            <p>ARTICULO TERCERO: Entréguese copia de la presente resolución con sus respectivos anexos para su correspondiente pago a la oficina de Tesorería de el(la) <?= $empresa['nombre'] ?> para lo de su competencia.</p>
                            <p style="text-align:center; padding-bottom:30px;"><b>COMUNÍQUESE Y CÚMPLASE.</b></p>
                            <p style="padding-bottom:40px;">Dada en <?= $empresa['nom_municipio'] ?>, a los <?= $f_exp[2] ?> días del mes de <?= $meses[$f_exp[1]] ?> del año <?= $f_exp[0] ?>.</p>
                            <div class="row">
                                <div class="col-12">
                                    <div style="text-align: center;">
                                        <div>___________________________________</div>
                                        <div><?= 'GERENTE' ?> </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot style="font-size: 10px; color: #aab7b8; text-align: center;">
                    <tr>
                        <td>
                            <?php
                            if ($_SESSION['nit_emp'] == '900190473') {
                            ?>
                                <?= $empresa['nombre'] ?><br>
                                Sede Administrativa calle 26 No 8-114<br>
                                Sede Asistencial carrera 1 con calle 18 esquina vía Pupiales<br>
                                Fax 773 2413 - Teléfono 773 2394 Página web: www.ipsipialesese.gov.co<br>
                                Correo electrónico: gerencia@ipsmunicipalese.gov.co<br>
                                Ipiales Nariño
                            <?php } ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php
}
?>