<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include_once '../../conexion.php';
include_once '../../permisos.php';
include_once '../../terceros.php';
$key = array_search('53', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$id_adq = isset($_POST['detalles']) ? $_POST['detalles'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_adquisiciones`.`id_adquisicion`
                , `tb_tipo_compra`.`id_tipo`
                , `tb_tipo_compra`.`tipo_compra`
                , `tb_tipo_bien_servicio`.`id_tipo_cotrato`
                , `ctt_adquisiciones`.`id_tipo_bn_sv`
            FROM
                `tb_tipo_contratacion`
                INNER JOIN `tb_tipo_compra` 
                    ON (`tb_tipo_contratacion`.`id_tipo_compra` = `tb_tipo_compra`.`id_tipo`)
                INNER JOIN `tb_tipo_bien_servicio` 
                    ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisiciones`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE `ctt_adquisiciones`.`id_adquisicion` = $id_adq";
    $rs = $cmd->query($sql);
    $tipo_adq = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tp_bs = $tipo_adq['id_tipo_bn_sv'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_relacion`, `id_formato`
            FROM
                `ctt_formatos_doc_rel`
            WHERE (`id_tipo_bn_sv` = $tp_bs)";
    $rs = $cmd->query($sql);
    $formatos = $rs->fetchAll();
    $posicion = [];
    foreach ($formatos as $f) {
        $posicion[$f['id_formato']] = $f['id_relacion'];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `nom_sede` AS `nombre` FROM `tb_sedes`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `far_centrocosto_area`.`id_area`,`far_centrocosto_area`.`id_centrocosto`, `tb_centrocostos`.`nom_centro`
            FROM
                `far_centrocosto_area`
                INNER JOIN `tb_centrocostos` 
                    ON (`far_centrocosto_area`.`id_centrocosto` = `tb_centrocostos`.`id_centro`) 
            ORDER BY `tb_centrocostos`.`nom_centro` ASC";
    $rs = $cmd->query($sql);
    $centros_costo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id`, `descripcion` FROM `ctt_estado_adq`";
    $rs = $cmd->query($sql);
    $estado_adq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_destino_contrato`.`id_destino`
                , `far_centrocosto_area`.`id_area`
                , `far_centrocosto_area`.`id_sede`
                , `far_centrocosto_area`.`id_centrocosto`
                , `ctt_destino_contrato`.`horas_mes`
            FROM
                `ctt_destino_contrato`
            INNER JOIN `far_centrocosto_area` 
                ON (`ctt_destino_contrato`.`id_area_cc` = `far_centrocosto_area`.`id_area`)
            WHERE `ctt_destino_contrato`.`id_adquisicion` = $id_adq ORDER BY `ctt_destino_contrato`.`id_destino` ASC";
    $rs = $cmd->query($sql);
    $destinos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `ctt_adquisiciones`.`id_tipo_bn_sv`
                , `ctt_modalidad`.`modalidad`
                , `ctt_adquisiciones`.`id_adquisicion`
                , `ctt_adquisiciones`.`fecha_adquisicion`
                , `ctt_adquisiciones`.`estado`
                , `ctt_adquisiciones`.`objeto`
                , `ctt_adquisiciones`.`id_cont_api`
                , `ctt_adquisiciones`.`id_supervision`
                , `ctt_adquisiciones`.`id_orden`
                , `tb_tipo_bien_servicio`.`filtro_adq`
                , `ctt_adquisiciones`.`id_tercero`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`nom_tercero`
            FROM
                `ctt_adquisiciones`
            INNER JOIN `ctt_modalidad` 
                ON (`ctt_adquisiciones`.`id_modalidad` = `ctt_modalidad`.`id_modalidad`)
            INNER JOIN `tb_tipo_bien_servicio` 
                ON (`ctt_adquisiciones`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
            LEFT JOIN `tb_terceros` 
                ON (`ctt_adquisiciones`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE `id_adquisicion` = $id_adq";
    $rs = $cmd->query($sql);
    $adquisicion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_adquisiciones`.`id_adquisicion`
                , `tb_tipo_contratacion`.`id_tipo_compra`
            FROM
                `ctt_adquisiciones`
                INNER JOIN `tb_tipo_bien_servicio` 
                    ON (`ctt_adquisiciones`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `tb_tipo_contratacion` 
                    ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
            WHERE  `ctt_adquisiciones`.`id_adquisicion` = $id_adq";
    $rs = $cmd->query($sql);
    $tipo_compra = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    if ($adquisicion['id_orden'] == '') {
        $sql = "SELECT
                    `ctt_bien_servicio`.`bien_servicio`
                    , `ctt_orden_compra_detalle`.`cantidad`
                    , `ctt_orden_compra_detalle`.`val_unid`
                    , `ctt_orden_compra_detalle`.`id_detalle`
                FROM
                    `ctt_orden_compra_detalle`
                    INNER JOIN `ctt_orden_compra` 
                        ON (`ctt_orden_compra_detalle`.`id_oc` = `ctt_orden_compra`.`id_oc`)
                    INNER JOIN `ctt_bien_servicio` 
                        ON (`ctt_orden_compra_detalle`.`id_servicio` = `ctt_bien_servicio`.`id_b_s`)
                WHERE (`ctt_orden_compra`.`id_adq` = $id_adq)";
    } else {
        $sql = "SELECT
                    `far_alm_pedido_detalle`.`id_ped_detalle` AS `id_detalle`
                    , `far_medicamentos`.`nom_medicamento` AS `bien_servicio`
                    , `far_alm_pedido_detalle`.`cantidad`
                    , `far_alm_pedido_detalle`.`valor` AS `val_unid`
                    , `far_alm_pedido_detalle`.`aprobado`
                FROM
                    `far_alm_pedido_detalle`
                    INNER JOIN `far_medicamentos` 
                        ON (`far_alm_pedido_detalle`.`id_medicamento` = `far_medicamentos`.`id_med`)
                WHERE (`far_alm_pedido_detalle`.`id_pedido` = {$adquisicion['id_orden']})";
    }
    $rs = $cmd->query($sql);
    $detalles_orden = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_est_prev` , `id_compra`, `fec_ini_ejec`, `fec_fin_ejec`, `id_forma_pago`, `id_supervisor`, `id_user_reg`
            FROM
                `ctt_estudios_previos`
            WHERE `id_compra` = '$id_adq' LIMIT 1";
    $rs = $cmd->query($sql);
    $estudios = $rs->fetch();

    $id_estudio = !empty($estudios['id_est_prev']) ? $estudios['id_est_prev'] : '';
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_adquisiciones`.`id_adquisicion`
                , `pto_cdp`.`id_manu`
                , `pto_cdp`.`objeto`
                , `pto_cdp`.`fecha`
                , `pto_cdp_detalle`.`valor`
            FROM
                `ctt_adquisiciones`
                INNER JOIN `pto_cdp` 
                    ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
            WHERE (`ctt_adquisiciones`.`id_adquisicion` = $id_adq)
            LIMIT 1";
    $rs = $cmd->query($sql);
    $cdp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_crp`.`id_manu`
                , `pto_crp`.`fecha`
                , `pto_crp_detalle`.`valor`
                , `ctt_adquisiciones`.`id_adquisicion`
                , `pto_crp`.`objeto`
            FROM
                `pto_crp`
                INNER JOIN `pto_cdp` 
                    ON (`pto_crp`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
            WHERE (`ctt_adquisiciones`.`id_adquisicion` = $id_adq)
            LIMIT 1";
    $rs = $cmd->query($sql);
    $crp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_contrato_compra`
                , `id_compra`
                , `fec_ini`
                , `fec_fin`
                , `val_contrato`
                , `id_forma_pago`
                , `id_supervisor`
                , `id_secop`
                , `num_contrato`
            FROM
                `ctt_contratos`
            WHERE (`id_compra` = $id_adq) LIMIT 1";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
function pesos($valor)
{
    if ($valor >= 0) {
        return '$' . number_format($valor, 2, ",", ".");
    } else {
        return '-$' . number_format($valor * (-1), 2);
    }
}
if (!empty($adquisicion)) {
    $idtbnsv = $adquisicion['id_tipo_bn_sv'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT 
                    `id_b_s`, `tipo_compra`,`tb_tipo_contratacion`.`id_tipo`, `tipo_contrato`, `tipo_bn_sv`, `bien_servicio`
                FROM
                    `tb_tipo_contratacion`
                INNER JOIN `tb_tipo_compra` 
                    ON (`tb_tipo_contratacion`.`id_tipo_compra` = `tb_tipo_compra`.`id_tipo`)
                INNER JOIN `tb_tipo_bien_servicio` 
                    ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
                INNER JOIN `ctt_bien_servicio` 
                    ON (`ctt_bien_servicio`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
                WHERE `id_tipo_b_s` = $idtbnsv
                ORDER BY `tipo_compra`,`tipo_contrato`, `tipo_bn_sv`, `bien_servicio`";
        $rs = $cmd->query($sql);
        $bnsv = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $j = 0;
?>
    <!DOCTYPE html>
    <html lang="es">
    <?php include '../../head.php' ?>

    <body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] == '1' ? 'sb-sidenav-toggled' : '' ?>">
        <?php include '../../navsuperior.php' ?>
        <div id="layoutSidenav">
            <?php include '../../navlateral.php' ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid p-2">
                        <?php
                        $boton = $guardar = $cerrar = '';
                        if ((PermisosUsuario($permisos, 5302, 1) || $id_rol == 1) && $adquisicion['estado'] < 6) {
                            $peRegValue = '0'; // Valor por defecto

                            if ($adquisicion['filtro_adq'] == '0') {
                                if ($adquisicion['estado'] == 1) {
                                    $peRegValue = '1';
                                    $cerrar = '<button type="button" class="btn btn-secondary btn-sm mr-1" id="cerrarOrdenServicio">Cerrar</button>';
                                }
                            } elseif (in_array($adquisicion['filtro_adq'], ['1', '2'])) {
                                if (empty($adquisicion['id_orden'])) {
                                    $buttonText = $adquisicion['filtro_adq'] == '1' ? 'Orden Almacén' : 'Orden Activos Fijos';
                                    $boton = '<button type="button" class="btn btn-primary btn-sm listOrdenes mr-1" text="' . $adquisicion['filtro_adq'] . '">' . $buttonText . '</button>';
                                }
                                if ($adquisicion['estado'] == 1) {
                                    $guardar = '<button type="button" class="btn btn-success btn-sm mr-1" id="guardarOrden">Guardar</button>';
                                    $cerrar = '<button type="button" class="btn btn-secondary btn-sm mr-1" id="cerrarOrden">Cerrar</button>';
                                }
                            }
                            echo '<input type="hidden" id="peReg" value="' . $peRegValue . '">';
                        } else {
                            echo '<input type="hidden" id="peReg" value="0">';
                        }
                        ?>
                        <div class="card mb-4">
                            <div class="card-header" id="divTituloPag">
                                <div class="row">
                                    <div class="col-md-11">
                                        <i class="fas fa-copy fa-lg" style="color:#1D80F7"></i>
                                        DETALLES DE ADQUISICIÓN
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" id="divCuerpoPag">
                                <div id="accordion">
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#datosperson" aria-expanded="true" aria-controls="collapseOne">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="fas fa-clipboard-list fa-lg" style="color: #3498DB;"></span>
                                                        </div>
                                                        <div>
                                                            <?php $j++;
                                                            echo $j ?>. DETALLES DE CONTRATACIÓN
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="datosperson" class="collapse show" aria-labelledby="headingOne">
                                            <div class="card-body">
                                                <div class="shadow detalles-empleado">
                                                    <div class="row">
                                                        <div class="div-mostrar bor-top-left col-md-4">
                                                            <span class="lbl-mostrar pb-2">MODALIDAD CONTRATACIÓN</span>
                                                            <div class="div-cont pb-2"><?php echo $adquisicion['modalidad'] ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-2">
                                                            <span class="lbl-mostrar pb-2">ADQUISICIÓN</span>
                                                            <input type="hidden" id="id_compra" value="<?php echo $id_adq ?>">
                                                            <input type="hidden" id="id_contrato_compra" value="<?php echo isset($contrato['id_contrato_compra']) ? $contrato['id_contrato_compra'] : '' ?>">
                                                            <div class="div-cont pb-2">ADQ-<?php echo mb_strtoupper($adquisicion['id_adquisicion']) ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-3">
                                                            <span class="lbl-mostrar pb-2">FECHA</span>
                                                            <div class="div-cont pb-2"><?php echo $adquisicion['fecha_adquisicion'] ?></div>
                                                        </div>
                                                        <div class="div-mostrar bor-top-right col-md-3">
                                                            <span class="lbl-mostrar pb-2">ESTADO</span>
                                                            <?php
                                                            $estad = $adquisicion['estado'];
                                                            $key = array_search($estad, array_column($estado_adq, 'id'));
                                                            ?>
                                                            <div class="div-cont pb-2"><?php echo $estado_adq[$key]['descripcion'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="div-mostrar bor-down-right bor-down-left col-md-12">
                                                            <span class="lbl-mostrar pb-2">OBJETO</span>
                                                            <div class="div-cont text-left pb-2"><?php echo $adquisicion['objeto'] ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--parte-->
                                    <?php
                                    $tipo_contrato = '0';
                                    foreach ($bnsv as $bs) {
                                        if ($bs['id_tipo'] == '1') {
                                            $tipo_contrato = '1';
                                        }
                                    }
                                    if (false) { ?>
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingBnSv">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseBnSv" aria-expanded="true" aria-controls="collapseBnSv">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-swatchbook fa-lg" style="color: #EC7063;"></span>
                                                            </div>
                                                            <div>
                                                                <?php $j++;
                                                                echo $j ?>. ORDEN DE BIEN O SERVICIOS
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseBnSv" class="collapse" aria-labelledby="headingBnSv">
                                                <div class="card-body">
                                                    <?php
                                                    ?>
                                                    <div id="divEstadoBnSv">
                                                        <?php
                                                        if (true) {
                                                        ?>
                                                            <form id="formDetallesAdq">
                                                                <input type="hidden" name="idAdq" value="<?php echo $id_adq ?>">
                                                                <table id="tableAdqBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Seleccionar</th>
                                                                            <?php echo $tipo_contrato == '1' ? '<th>Pago</th>' : '' ?>
                                                                            <th>Bien o Servicio</th>
                                                                            <th>Cantidad</th>
                                                                            <th>Valor Unitario</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        foreach ($bnsv as $bs) {
                                                                        ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <div class="text-center listado">
                                                                                        <input type="checkbox" name="check[]" value="<?php echo $bs['id_b_s'] ?>">
                                                                                    </div>
                                                                                </td>
                                                                                <?php if ($tipo_contrato == '1') { ?>
                                                                                    <td>
                                                                                        <select class="form-control form-control-sm altura py-0" id="tipo_<?php echo $bs['id_b_s'] ?>">
                                                                                            <option value="H">Horas</option>
                                                                                            <option value="M">Mensual</option>
                                                                                        </select>
                                                                                    </td>
                                                                                <?php } ?>
                                                                                <td class="text-left"><i><?php echo $bs['bien_servicio'] ?></i></td>
                                                                                <td><input type="number" name="bnsv_<?php echo $bs['id_b_s'] ?>" id="bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura cantidad"></td>
                                                                                <td><input type="number" name="val_bnsv_<?php echo $bs['id_b_s'] ?>" id="val_bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura" value="0"></td>
                                                                            </tr>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <th>Seleccionar</th>
                                                                            <?php echo $tipo_contrato == '1' ? '<th>Pago</th>' : '' ?>
                                                                            <th>Bien o Servicio</th>
                                                                            <th>Cantidad</th>
                                                                            <th>Valor Unitario</th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </form>
                                                        <?php
                                                        } else {
                                                            echo '<div class="p-3 mb-2 bg-success text-white">ORDEN AGREGADA CORRECTAMENTE</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <input type="hidden" id="tipo_contrato" value="<?php echo $tipo_contrato ?>">
                                    <input type="hidden" id="tipo_servicio" value="<?php echo $idtbnsv ?>">
                                    <?php
                                    if ($tipo_contrato == '1' && $adquisicion['estado'] >= 1) { ?>
                                        <!--parte-->
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingDestContrato">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseDestContrato" aria-expanded="true" aria-controls="collapseDestContrato">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-people-arrows fa-lg" style="color: #1ABC9C;"></span>
                                                            </div>
                                                            <div>
                                                                <?php $j++;
                                                                echo $j ?>. DESTINACIÓN DEL CONTRATO
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseDestContrato" class="collapse" aria-labelledby="headingDestContrato">
                                                <?php
                                                $accion = empty($destinos) ? 'Guardar' : 'Actualizar';
                                                $value = empty($destinos) ? '0' : '1';
                                                ?>
                                                <div class="card-body">
                                                    <form id="formDestContra">
                                                        <fieldset class="border p-2 bg-light">
                                                            <div id="contenedor">
                                                                <?php
                                                                $disabled = $adquisicion['estado'] <= 5 ? '' : 'disabled';
                                                                if ($value == '0') {
                                                                ?>
                                                                    <div class="form-row px-4 pt-2">
                                                                        <div class="form-group col-md-4 mb-2">
                                                                            <label class="small">SEDE</label>
                                                                            <select name="slcSedeAC[]" class="form-control form-control-sm slcSedeAC" <?php echo $disabled ?>>
                                                                                <option value="0">--Seleccione--</option>
                                                                                <?php
                                                                                foreach ($sedes as $s) {
                                                                                    echo '<option value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group col-md-4 mb-2">
                                                                            <label class="small">CENTRO DE COSTO</label>
                                                                            <select name="slcCentroCosto[]" class="form-control form-control-sm slcCentroCosto" <?php echo $disabled ?>>
                                                                                <option value="0">--Seleccionar Sede--</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group col-md-4 mb-2">
                                                                            <label class="small">Horas asignadas / mes</label>
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="number" name="numHorasMes[]" class="form-control" <?php echo $disabled ?>>
                                                                                <?php if ($disabled == '') { ?>
                                                                                    <div class="input-group-append">
                                                                                        <button class="btn btn-outline-success" type="button" id="addRowSedes"><i class="fas fa-plus"></i></button>
                                                                                    </div>
                                                                                <?php } ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                } else {
                                                                    $control = 0;
                                                                    foreach ($destinos as $d) {
                                                                    ?>
                                                                        <div class="form-row px-4 pt-2">
                                                                            <div class="form-group col-md-4 mb-2">
                                                                                <?php echo $control == 0 ? '<label class="small">SEDE</label>' : '' ?>
                                                                                <select name="slcSedeAC[]" class="form-control form-control-sm slcSedeAC" <?php echo $disabled ?>>
                                                                                    <?php
                                                                                    foreach ($sedes as $s) {
                                                                                        if ($s['id_sede'] == $d['id_sede']) {
                                                                                            echo '<option value="' . $s['id_sede'] . '" selected>' . $s['nombre'] . '</option>';
                                                                                        } else {
                                                                                            echo '<option value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="form-group col-md-4 mb-2">
                                                                                <?php echo $control == 0 ? '<label class="small">CENTRO DE COSTO</label>' : '' ?>
                                                                                <select name="slcCentroCosto[]" class="form-control form-control-sm slcCentroCosto" <?php echo $disabled ?>>
                                                                                    <?php
                                                                                    foreach ($centros_costo as $cc) {
                                                                                        if ($cc['id_area'] == $d['id_area']) {
                                                                                            if ($cc['id_area'] == $d['id_area']) {
                                                                                                echo '<option value="' . $cc['id_area'] . '" selected>' . $cc['nom_centro'] . '</option>';
                                                                                            } else {
                                                                                                echo '<option value="' . $cc['id_area'] . '">' . $cc['nom_centro'] . '</option>';
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </div>
                                                                            <div class="form-group col-md-4 mb-2">
                                                                                <?php echo $control == 0 ? '<label for="numHorasMes" class="small">Horas asignadas / mes</label>' : '' ?>
                                                                                <div class="input-group input-group-sm">
                                                                                    <input type="number" name="numHorasMes[]" class="form-control" value="<?php echo $d['horas_mes'] ?>" <?php echo $disabled ?>>
                                                                                    <div class="input-group-append">
                                                                                        <?php
                                                                                        if ($adquisicion['estado'] <= 5) {
                                                                                            if ($control == 0) {
                                                                                                echo '<button class="btn btn-outline-success" type="button" id="addRowSedes"><i class="fas fa-plus"></i></button>';
                                                                                            } else {
                                                                                                echo '<button class="btn btn-outline-danger delRowSedes" type="button"><i class="fas fa-minus"></i></button>';
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                <?php
                                                                        $control++;
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </fieldset>
                                                    </form>
                                                    <?php if ($adquisicion['estado'] <= 5) {  ?>
                                                        <div class="text-center pt-3">
                                                            <?php if (PermisosUsuario($permisos, 5302, 2) || PermisosUsuario($permisos, 5302, 3) || $id_rol == 1) { ?>
                                                                <button type="button" class="btn btn-success btn-sm" id="btnDestContra" value="<?php echo $value ?>"><?php echo $accion ?></button>
                                                            <?php } ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <!--parte-->
                                    <?php if ($adquisicion['estado'] >= 1) { ?>
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingCotRec">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseCotRec" aria-expanded="true" aria-controls="collapseCotRec">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-clipboard-check fa-lg" style="color: #2ECC71;"></span>
                                                            </div>
                                                            <div>
                                                                <?php $j++;
                                                                echo $j ?>. ORDEN DE COMPRA.
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseCotRec" class="collapse" aria-labelledby="headingCotRec">
                                                <div class="card-body">
                                                    <div id="accordion">
                                                        <?php
                                                        echo '<div class="text-right mb-2">' . $boton . $guardar . $cerrar . '</div>';
                                                        ?>
                                                        <form id="formOrdenCompra">
                                                            <?php
                                                            echo $adquisicion['id_orden'] == '' ? '' : '<input type="hidden" name="id_orden" id="id_orden" value="' . $adquisicion['id_orden'] . '">';
                                                            ?>
                                                            <table class="table table-striped table-bordered table-sm nowrap table-hover shadow tableCotRecibidas" style="width:100%">
                                                                <thead>
                                                                    <tr class="text-center">
                                                                        <th>TERCERO:</th>
                                                                        <th colspan="4"><?php echo  $adquisicion['nom_tercero']; ?></th>
                                                                        <th><?php echo  $adquisicion['nit_tercero']; ?></th>
                                                                    </tr>
                                                                    <tr class="text-center">
                                                                        <th>#</th>
                                                                        <th>Bien o Servicio</th>
                                                                        <th><?php echo $adquisicion['id_orden'] > 0 ? 'Solicita/Ordena' : 'Cantidad'; ?></th>
                                                                        <th>Val. Unidad</th>
                                                                        <th>Total</th>
                                                                        <th><?php echo $adquisicion['id_orden'] == '' ? 'Acciones' : '<input type="checkbox" id="selectAll" title="Desmarcar todos" checked>'; ?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="modificarCotizaciones">
                                                                    <?php
                                                                    foreach ($detalles_orden as $dc) {
                                                                        if ($adquisicion['id_orden'] > 0 && $adquisicion['estado'] < 5) {
                                                                            $aprobado = $dc['aprobado'] > 0 ? $dc['aprobado'] : $dc['cantidad'];
                                                                            $val_unid = '<input type="number" name="val_unid[' . $dc['id_detalle'] . ']" class="form-control form-control-sm text-right" value="' . $dc['val_unid'] . '">';
                                                                            $cantidad = '<div class="input-group input-group-sm">
                                                                                        <div class="input-group-prepend">
                                                                                            <span class="input-group-text">' . $dc['cantidad'] . '</span>
                                                                                        </div>
                                                                                        <input type="number" class="form-control" name="cantidad[' . $dc['id_detalle'] . ']" value="' . $aprobado . '" max="' . $dc['cantidad'] . '">
                                                                                    </div>';
                                                                        } else {
                                                                            $val_unid = pesos($dc['val_unid']);
                                                                            $cantidad = isset($dc['aprobado']) ? $dc['aprobado'] : $dc['cantidad'];
                                                                        }
                                                                    ?>
                                                                        <tr>
                                                                            <td><?php echo $dc['id_detalle'] ?></td>
                                                                            <td><?php echo $dc['bien_servicio'] ?></td>
                                                                            <td><?php echo $cantidad ?></td>
                                                                            <td class="text-right"><?php echo $val_unid ?></td>
                                                                            <td class="text-right">
                                                                                <?php echo pesos($dc['val_unid'] * (isset($dc['aprobado']) ? $dc['aprobado'] : $dc['cantidad'])) ?>
                                                                                <input type="hidden" name="total[]" class="sumTotal" value="<?php echo $dc['val_unid'] * (isset($dc['aprobado']) ? $dc['aprobado'] : $dc['cantidad']) ?>">
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <?php
                                                                                if ($adquisicion['id_orden'] == '') {
                                                                                    if ($adquisicion['estado'] >= 1 && $adquisicion['estado'] < 6 && $adquisicion['id_orden'] == '') { ?>
                                                                                        <button value="<?php echo $dc['id_detalle'] ?>" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>
                                                                                        <button value="<?php echo $dc['id_detalle'] ?>" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>
                                                                                <?php }
                                                                                } else {
                                                                                    if ($adquisicion['estado'] < 5) {
                                                                                        echo '<input type="checkbox" class="aprobado" name="aprobado[' . $dc['id_detalle'] . ']" checked>';
                                                                                    }
                                                                                } ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--parte-->
                                        <?php if ($adquisicion['estado'] >= 5) { ?>
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingCDP">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseCDP" aria-expanded="true" aria-controls="collapseCDP">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-invoice-dollar fa-lg" style="color: #7D3C98;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. CERTIFICADO DE DISPONIBILIDAD PRESUPUESTAL (CDP).
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseCDP" class="collapse" aria-labelledby="headingCDP">
                                                    <div class="card-body">
                                                        <?php
                                                        if (!empty($cdp)) {
                                                        ?>
                                                            <input type="hidden" id="num_cdp" value="<?php echo $cdp['id_manu'] ?>">
                                                            <table class="table table-striped table-bordered table-sm nowrap table-hover shadow tableCDP" style="width:100%">
                                                                <thead class="text-center">
                                                                    <tr>
                                                                        <th>Número</th>
                                                                        <th>Fecha</th>
                                                                        <th>Objeto</th>
                                                                        <th>Valor</th>
                                                                        <!--<th>Acción</th>-->
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="modificarCDP">
                                                                    <tr>
                                                                        <td><?php echo $cdp['id_manu'] ?></td>
                                                                        <td><?php echo $cdp['fecha'] ?></td>
                                                                        <td><?php echo $cdp['objeto'] ?></td>
                                                                        <td class="text-right"><?php echo pesos($cdp['valor']) ?></td>
                                                                        <!--<td class="text-center">
                                                                            <a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Descargar CDP" onclick="generarFormatoCdp(<?php echo $cdp['id_pto_doc'] ?>)"><span class="fas fa-download fa-lg"></span></a>
                                                                        </td>-->
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        <?php
                                                        } else {
                                                            echo '<div class="p-3 mb-2 bg-warning text-white">AÚN <b>NO</b> SE HA ASIGNADO UN CDP</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingEstPrev">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseEstPrev" aria-expanded="true" aria-controls="collapseEstPrev">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-folder-open fa-lg" style="color: #DC7633;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. ESTUDIOS PREVIOS.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseEstPrev" class="collapse" aria-labelledby="headingEstPrev">
                                                    <div class="card-body">
                                                        <?php if ($id_estudio == '') {
                                                            if (PermisosUsuario($permisos, 5302, 2) || $id_rol == 1) {
                                                        ?>
                                                                <button type="button" class="btn btn-success btn-sm" id='btnAddEstudioPrevio' value="<?php echo $id_adq ?>">INICIAR ESTUDIOS PREVIOS</button>
                                                            <?php }
                                                        } else {
                                                            include 'datos/listar/datos_estudio_previo.php';
                                                            if ($adquisicion['estado'] <= 7) {
                                                            ?>
                                                                <a type="button" text="<?= isset($posicion[1]) ? $posicion[1] : 0 ?>" class="btn btn-warning btn-sm downloadFormsCtt" id="btnFormatoEstudioPrevio" style="color:white">DESCARGAR FORMATO&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                                <a type="button" class="btn btn-info btn-sm" id="x-x" style="color:white">MATRIZ DE RIESGOS&nbsp&nbsp;<span class="fas fa-download fa-lg"></span></a>
                                                                <a type="button" text="<?= isset($posicion[2]) ? $posicion[2] : 0 ?>" class="btn btn-primary btn-sm downloadFormsCtt" id="btnAnexos" style="color:white">ANEXOS&nbsp&nbsp;<span class="far fa-copy fa-lg"></span></a>
                                                        <?php }
                                                        } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingContrata">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseContrata" aria-expanded="true" aria-controls="collapseContrata">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-contract fa-lg" style="color: #26C6DA;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. CONTRATACION.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseContrata" class="collapse" aria-labelledby="headingContrata">
                                                    <div class="card-body">
                                                        <?php if ($id_estudio == '') { ?>
                                                            <div class="alert alert-warning" role="alert">
                                                                AUN NO SE HA REGISTRADO ESTUDIOS PREVIOS
                                                            </div>
                                                            <?php } else {
                                                            if ($adquisicion['estado'] == 6) {
                                                                if (PermisosUsuario($permisos, 5302, 2) || $id_rol == 1) {
                                                            ?>
                                                                    <button type="button" class="btn btn-success btn-sm" id='btnAddContrato' value="<?php echo $id_estudio ?>">INICIAR CONTRATACIÓN</button>
                                                                <?php
                                                                }
                                                            } else if ($adquisicion['estado'] >= 7) {
                                                                if ($adquisicion['estado'] == 7) {
                                                                ?>
                                                                    <div class="text-right">
                                                                        <a type="button" class="btn btn-secondary btn-sm mb-2" id="btnCerrarContrato">Cerrar</a>
                                                                    </div>
                                                                    <?php
                                                                }
                                                                include 'datos/listar/datos_contrato_compra.php';
                                                                if ($adquisicion['estado'] == 7) {
                                                                    if ($tipo_compra['id_tipo_compra'] != '2') {
                                                                        //btnFormatoCompraVenta
                                                                    ?>
                                                                        <button type="button" class="btn btn-warning btn-sm" id="xx" style="color:white" disabled>DESCARGAR FORMATO COMPRAVENTA&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></button>
                                                                    <?php } else {
                                                                        //btnFormatoServicios
                                                                    ?>
                                                                        <button type="button" class="btn btn-warning btn-sm" id="xxx" style="color:white" disabled>DESCARGAR FORMATO SERVICIOS&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></button>
                                                                    <?php } ?>
                                                            <?php }
                                                            }
                                                        }
                                                        if ($adquisicion['estado'] == 9) { ?>
                                                            <a type="button" class="btn btn-warning btn-sm" id="btnFormatoDesigSuper" style="color:white">DESCARGAR FORMATO DESIGNACIÓN DE SUPERVISIÓN&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                            <a type="button" text="<?= isset($posicion[3]) ? $posicion[3] : 0 ?>" class="btn btn-success btn-sm downloadFormsCtt" id="btnFormatoContrato" style="color:white">DESCARGAR FORMATO CONTRATO&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                            <?php if (false) { ?>
                                                                <a type="button" class="btn btn-success btn-sm" id="btnEnviarActaSupervision" value="<?php echo $adquisicion['id_supervision'] ?>" style="color:white">ENVIAR SUPERVISIÓN&nbsp&nbsp;<span class="fas fa-file-upload fa-lg"></span></a>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingDocSoporte">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseDocSoporte" aria-expanded="true" aria-controls="collapseDocSoporte">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-invoice fa-lg" style="color: #AFB42B;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. DOCUMENTOS DE SOPORTE.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseDocSoporte" class="collapse" aria-labelledby="headingDocSoporte">
                                                    <div class="card-body">
                                                        <table id="tableDocSopContrato" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                            <thead>
                                                                <tr class="text-center">
                                                                    <th>#</th>
                                                                    <th>Nombre Documento</th>
                                                                    <th>Archivo</th>
                                                                    <th>Aprobado</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="DocsSoportContrato">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingCRP">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseCRP" aria-expanded="true" aria-controls="collapseCRP">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-file-prescription fa-lg" style="color: #795548;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. REGISTRO PRESUPUESTAL (CRP).
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseCRP" class="collapse" aria-labelledby="headingCRP">
                                                    <div class="card-body">
                                                        <?php
                                                        if (!empty($crp)) {
                                                        ?>
                                                            <table class="table table-striped table-bordered table-sm nowrap table-hover shadow tableCDP" style="width:100%">
                                                                <thead class="text-center">
                                                                    <tr>
                                                                        <th>Número</th>
                                                                        <th>Fecha</th>
                                                                        <th>Objeto</th>
                                                                        <th>Valor</th>
                                                                        <!--<th>Acción</th>-->
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="modificarCDP">
                                                                    <tr>
                                                                        <td><?php echo $crp['id_manu'] ?></td>
                                                                        <td><?php echo $crp['fecha'] ?></td>
                                                                        <td><?php echo $crp['objeto'] ?></td>
                                                                        <td class="text-right"><?php echo pesos($crp['valor']) ?></td>
                                                                        <!--<td class="text-center">
                                                                            <a class="btn btn-outline-warning btn-sm btn-circle shadow-gb" title="Descargar CDP" onclick="generarFormatoCdp(<?php echo $cdp['id_pto_doc'] ?>)"><span class="fas fa-download fa-lg"></span></a>
                                                                        </td>-->
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        <?php
                                                        } else {
                                                            echo '<div class="p-3 mb-2 bg-warning text-white">AÚN <b>NO</b> SE HA REGISTRADO UN CRP</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingActIni">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseActIni" aria-expanded="true" aria-controls="collapseActIni">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-map-pin fa-lg" style="color: #2471A3;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. ACTA DE INICIO.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <div id="collapseActIni" class="collapse" aria-labelledby="headingActIni">
                                                    <div class="card-body">
                                                        <?php
                                                        if ($adquisicion['estado'] >= 9) {
                                                        ?>
                                                            <a type="button" text="<?= isset($posicion[4]) ? $posicion[4] : 0 ?>" class="btn btn-warning btn-sm downloadFormsCtt" id="btnFormActaInicio" style="color:white">DESCARGAR FORMATO ACTA DE INICIO&nbsp&nbsp;<span class="fas fa-file-download fa-lg"></span></a>
                                                        <?php
                                                        } else { ?>
                                                            <div class="alert alert-warning" role="alert">
                                                                SE DEBE ASIGNAR UN SUPERVISOR PARA GENERAR ACTA DE INICIO.
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--parte-->
                                            <div class="card">
                                                <div class="card-header card-header-detalles py-0 headings" id="headingNovedad">
                                                    <h5 class="mb-0">
                                                        <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseNovedad" aria-expanded="true" aria-controls="collapseNovedad">
                                                            <div class="form-row">
                                                                <div class="div-icono">
                                                                    <span class="fas fa-bullhorn fa-lg" style="color: #F1C40F;"></span>
                                                                </div>
                                                                <div>
                                                                    <?php $j++;
                                                                    echo $j ?>. NOVEDADES.
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </h5>
                                                </div>
                                                <?php
                                                //API URL
                                                $url = $api . 'terceros/datos/res/listar/novedades_contrato/' . $adquisicion['id_cont_api'];
                                                $ch = curl_init($url);
                                                //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                $result = curl_exec($ch);
                                                curl_close($ch);
                                                $nvdds = json_decode($result, true);
                                                $t_novdad = [];
                                                if (isset($nvdds)) {
                                                    while (current($nvdds)) {
                                                        $t_novdad[] =  key($nvdds);
                                                        next($nvdds);
                                                    }
                                                }
                                                $keyliq = array_search('liquidacion', $t_novdad);
                                                $keyter = array_search('liquidacion', $t_novdad);
                                                $inactivo = '';
                                                $activar = 'novedadC';
                                                if (false !== $keyliq || false !== $keyter) {
                                                    $inactivo = 'disabled';
                                                    $activar = '';
                                                }
                                                ?>
                                                <div id="collapseNovedad" class="collapse" aria-labelledby="headingNovedad">
                                                    <div class="card-body">
                                                        <div class="form-row pb-3">
                                                            <div class=" col-md-2">
                                                                <button value="1" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Adición o Prorroga</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="2" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Cesión</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="3" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Suspención</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="4" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Reinicio</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="5" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Terminación</button>
                                                            </div>
                                                            <div class=" col-md-2">
                                                                <button value="6" type="button" <?php echo $inactivo ?> class="btn btn-outline-info w-100 btn-sm <?php echo $activar ?>">Liquidación</button>
                                                            </div>
                                                        </div>
                                                        <table id="tableNovedadesContrato" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                            <thead>
                                                                <tr class="text-center">
                                                                    <th>Novedad</th>
                                                                    <th>Fecha</th>
                                                                    <th>Valor 1</th>
                                                                    <th>Valor 2</th>
                                                                    <th>Fecha Inicia</th>
                                                                    <th>Fecha Fin</th>
                                                                    <th>Observación</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="modificarNovContrato">
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                            if (false) {
                                            ?>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="headingInfoActv">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseInfoActv" aria-expanded="true" aria-controls="collapseInfoActv">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-info-circle fa-lg" style="color: #29B6F6;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php $j++;
                                                                        echo $j ?>. INFORME DE ACTIVIDADES.
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseInfoActv" class="collapse" aria-labelledby="headingInfoActv">
                                                        <div class="card-body">
                                                            <?php
                                                            $id_c = $seleccionada['id_tercero_api'] . '|' . $_SESSION['nit_emp'] . '|' . $id_adq;
                                                            //API URL
                                                            $url = $api . 'terceros/datos/res/lista/compra_entregado/' . $id_c;
                                                            $ch = curl_init($url);
                                                            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                            $result = curl_exec($ch);
                                                            curl_close($ch);
                                                            $separar = explode('|', $id_c);
                                                            $compra_entregada = json_decode($result, true);
                                                            if ($compra_entregada != '0') {
                                                                //API URL
                                                                /*$id_empresa = $compra_entregada['nit'];
                                                            $url = $api . 'terceros/datos/res/listar/empresas/' . $id_empresa;
                                                            $ch = curl_init($url);
                                                            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                            $result = curl_exec($ch);
                                                            curl_close($ch);
                                                            $datos_empresa = json_decode($result, true);*/
                                                            ?>
                                                                <div id="contTablaEntrega">
                                                                    <form id="formCantEntrega">
                                                                        <input type="hidden" name="id_cnt" value="<?php echo $compra_entregada['id_c'] ?>">
                                                                        <table id="tableListProdRecibidos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" width="100%">
                                                                            <thead class="alinear-head">
                                                                                <tr>
                                                                                    <th>Bien o servicio</th>
                                                                                    <th>Cant. Contratada</th>
                                                                                    <th>Entrega # 1</th>
                                                                                    <?php
                                                                                    for ($i = 2; $i <= $compra_entregada['num_entregas']['entregas']; $i++) {
                                                                                        echo '<th>Entrega # ' . $i . ' </th>';
                                                                                    }
                                                                                    ?>
                                                                                    <th>Cant. Pendiente</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody id="modificarCompraRec">
                                                                                <?php
                                                                                $total_entrega = 0;
                                                                                foreach ($compra_entregada['listado'] as $ce) {
                                                                                ?>
                                                                                    <tr class="text-center">
                                                                                        <td class="text-left"><?php echo $ce['bien_servicio'] ?></td>
                                                                                        <td><?php echo $ce['cantid'] ?></td>
                                                                                        <?php
                                                                                        $array_entregado = $compra_entregada['entregas'];
                                                                                        $c_entregado = 0;
                                                                                        foreach ($array_entregado as $ae) {
                                                                                            if ($ae['id_val_cot'] == $ce['id_val_cot']) {
                                                                                                $c_entregado += $ae['cantidad_entrega'];
                                                                                                echo '<td>' . $ae['cantidad_entrega'] . '</td>';
                                                                                                $maxim =  $ae['cantidad_entrega'];
                                                                                                $id_ent = $ae['id_entrega'];
                                                                                                $estado_ent = $ae['cantidad_entrega'] > 0 ? $ae['estado'] : 4;
                                                                                            }
                                                                                        }
                                                                                        ?>
                                                                                        <td>
                                                                                            <?php
                                                                                            if ($ce['cantid'] > $c_entregado) {
                                                                                                $pendiente = $ce['cantid'] - $c_entregado;
                                                                                                $total_entrega += $pendiente;
                                                                                                echo $pendiente;
                                                                                            }
                                                                                            ?>
                                                                                        </td>
                                                                                    </tr>
                                                                                <?php
                                                                                }
                                                                                ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </form>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <?php
                                                if ($tipo_adq['id_tipo'] == '1') {
                                                ?>
                                                    <div class="card">
                                                        <div class="card-header card-header-detalles py-0 headings" id="headingActaEntrada">
                                                            <h5 class="mb-0">
                                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseActaEntrada" aria-expanded="true" aria-controls="collapseActaEntrada">
                                                                    <div class="form-row">
                                                                        <div class="div-icono">
                                                                            <span class="fas fa-file-signature fa-lg" style="color: #F0B27A;"></span>
                                                                        </div>
                                                                        <div>
                                                                            <?php $j++;
                                                                            echo $j ?>. ACTA DE ENTRADA (ALMACÉN).
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                            </h5>
                                                        </div>
                                                        <div id="collapseActaEntrada" class="collapse" aria-labelledby="headingActaEntrada">
                                                            <div class="card-body">
                                                                <table class="table-striped table-bordered table-sm nowrap table-hover shadow" width="100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>DESCRIPCIÓN</th>
                                                                            <th>ACCIÓN</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="detallesXEntrega">
                                                                        <?php
                                                                        for ($i = 1; $i <= $compra_entregada['num_entregas']['entregas']; $i++) {
                                                                            echo '<tr>';
                                                                            echo '<td>' . $i . ' </td>';
                                                                            echo '<td>Entrega #' . $i . ' </td>';
                                                                            echo '<td><div clasS="text-center"><a value="' . $id_c . '&' . $i . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb details" title="Detalles"><span class="fas fa-eye fa-lg"></span></a></div></td>';
                                                                            echo '</tr>';
                                                                        }
                                                                        ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php
                                                }
                                                ?>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="headingXXXX">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseXXXX" aria-expanded="true" aria-controls="collapseXXXX">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-search-dollar fa-lg" style="color: #F48FB1;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php $j++;
                                                                        echo $j ?>. SUPERVISIÓN O INTERVENTORIA.
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseXXXX" class="collapse" aria-labelledby="headingXXXX">
                                                        <div class="card-body">

                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="headingXXXX">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseXXXX" aria-expanded="true" aria-controls="collapseXXXX">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-money-check-alt fa-lg" style="color: #663399;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php $j++;
                                                                        echo $j ?>. CAUSACIÓN CONTABLE.
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseXXXX" class="collapse" aria-labelledby="headingXXXX">
                                                        <div class="card-body">

                                                        </div>
                                                    </div>
                                                </div>
                                                <!--parte-->
                                                <div class="card">
                                                    <div class="card-header card-header-detalles py-0 headings" id="headingXXXX">
                                                        <h5 class="mb-0">
                                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseXXXX" aria-expanded="true" aria-controls="collapseXXXX">
                                                                <div class="form-row">
                                                                    <div class="div-icono">
                                                                        <span class="fas fa-sign-out-alt fa-lg" style="color: #1ABC9C;"></span>
                                                                    </div>
                                                                    <div>
                                                                        <?php $j++;
                                                                        echo $j ?>. EGRESO TESORERIA.
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapseXXXX" class="collapse" aria-labelledby="headingXXXX">
                                                        <div class="card-body">

                                                        </div>
                                                    </div>
                                                </div>
                                    <?php }
                                        }
                                    } ?>
                                    <div class="text-center pt-3">
                                        <a type="button" class="btn btn-secondary  btn-sm" href="lista_adquisiciones.php">Regresar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <?php include '../../footer.php' ?>
            </div>
            <?php include '../../modales.php' ?>
        </div>
        <?php include '../../scripts.php' ?>
    </body>

    </html>
<?php
} else {
    echo 'Error al intentar obtener datos';
} ?>