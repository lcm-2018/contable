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
                , `seg_usuarios_sistema`.`descripcion` AS `cargo`
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
                <div class="col lead"><label><strong>CONCILIACIÓN BANCARIA</strong></label></div>
            </div>
        </div>
        <div class="watermark">
            <h3><?php echo $anulado ?></h3>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left' style="width:25%">CÓDIGO CUENTA:</td>
                <td class='text-left'><?php echo $fecha . ' ' . $hora; ?></td>
            </tr>
            <tr>
                <td class='text-left'>NOMBRE CUENTA:</td>
                <td class='text-left'><?php echo $tercero; ?></td>
            </tr>
            <tr>
                <td class='text-left'>MES CONCILIADO:</td>
                <td class='text-left'><?php echo number_format($num_doc, 0, '', '.'); ?></td>
            </tr>
            <tr>
                <td class='text-left'>AÑO:</td>
                <td class='text-left'><?php echo $documento['detalle']; ?></td>
            </tr>
        </table>
        <br>
        <table style="width:100% !important; border-collapse: collapse;" border="1">
            <tr>
                <td style="text-align: left; width: 50%; background-color: #f1948a">SALDO EN LIBROS (contable)</td>
                <td style="text-align: left; width: 25%"></td>
                <td style="text-align: left; width: 25%"></td>
            </tr>
            <tr>
                <td style="text-align: left; background-color: #f1948a">Total Débitos Pendientes (++)</td>
                <td style="text-align: left;"></td>
                <td style="text-align: left;"></td>
            </tr>
            <tr>
                <td style="text-align: left; background-color: #f1948a">Total Créditos Pendientes (-)</td>
                <td style="text-align: left;"></td>
                <td style="text-align: left;"></td>
            </tr>
            <tr>
                <td style="text-align: left; background-color: #f1948a">SALDO EN LIBROS EXTRACTO</td>
                <td style="text-align: left;"></td>
                <td style="text-align: left;"></td>
            </tr>
            <tr>
                <td style="text-align: left; background-color: #f1948a">SUMAS IGUALES</td>
                <td style="text-align: left; background-color: #f1948a"></td>
                <td style="text-align: left; background-color: #f1948a"></td>
            </tr>
        </table>
        </br>
        <table style="width:100% !important; border-collapse: collapse; font-size: 12px;" border="1">
            <tr>
                <th>Fecha</th>
                <th>Comprobante</th>
                <th>Tercero</th>
                <th>Documento</th>
                <th>Debito</th>
                <th>Credito</th>
            </tr>
            <tr>
                <td><?php echo date('Y-m-d', strtotime($documento['fecha'])); ?></td>
                <td><?php echo $documento['cod'] . $documento['id_manu']; ?></td>
                <td><?php echo $tercero; ?></td>
                <td><?php echo $num_doc; ?></td>
                <td><?php echo pesos($total); ?></td>
                <td></td>
            </tr>

        </table>

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