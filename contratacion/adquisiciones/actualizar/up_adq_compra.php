<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_adq = isset($_POST['up_adq_compra']) ? $_POST['up_adq_compra'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM ctt_adquisiciones WHERE id_adquisicion = '$id_adq'";
    $rs = $cmd->query($sql);
    $adq_compra = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM ctt_adquisicion_detalles WHERE id_adquisicion = '$id_adq'";
    $rs = $cmd->query($sql);
    $listBnSv = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$idtbnsv = $adq_compra['id_tipo_bn_sv'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_b_s, tipo_compra, tipo_contrato, tipo_bn_sv, bien_servicio
            FROM
                tb_tipo_contratacion
            INNER JOIN tb_tipo_compra 
                ON (tb_tipo_contratacion.id_tipo_compra = tb_tipo_compra.id_tipo)
            INNER JOIN tb_tipo_bien_servicio 
                ON (tb_tipo_bien_servicio.id_tipo_cotrato = tb_tipo_contratacion.id_tipo)
            INNER JOIN ctt_bien_servicio 
                ON (ctt_bien_servicio.id_tipo_bn_sv = tb_tipo_bien_servicio.id_tipo_b_s)
            WHERE id_tipo_b_s = '$idtbnsv'
            ORDER BY tipo_compra,tipo_contrato, tipo_bn_sv, bien_servicio";
    $rs = $cmd->query($sql);
    $bnsv = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($adq_compra)) {
    if ($adq_compra['estado'] <= 2) {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT * FROM ctt_modalidad ORDER BY modalidad ASC";
            $rs = $cmd->query($sql);
            $modalidad = $rs->fetchAll();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT 
                    id_tipo_b_s, tipo_compra, tipo_contrato, tipo_bn_sv
                FROM
                    tb_tipo_bien_servicio
                INNER JOIN tb_tipo_contratacion 
                    ON (tb_tipo_bien_servicio.id_tipo_cotrato = tb_tipo_contratacion.id_tipo)
                INNER JOIN tb_tipo_compra 
                    ON (tb_tipo_contratacion.id_tipo_compra = tb_tipo_compra.id_tipo)
                ORDER BY tipo_compra, tipo_contrato, tipo_bn_sv";
            $rs = $cmd->query($sql);
            $tbnsv = $rs->fetchAll();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
?>
        <!DOCTYPE html>
        <html lang="es">
        <?php include '../../../head.php' ?>

        <body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                        echo 'sb-sidenav-toggled';
                                    } ?>">
            <?php include '../../../navsuperior.php' ?>
            <div id="layoutSidenav">
                <?php include '../../../navlateral.php' ?>
                <div id="layoutSidenav_content">
                    <main>
                        <div class="container-fluid p-2">
                            <div class="card mb-4">
                                <div class="card-header" id="divTituloPag">
                                    <div class="row">
                                        <div class="col-md-11">
                                            <i class="fas fa-copy fa-lg" style="color:#1D80F7"></i>
                                            ACTUALIZAR DETALLES DE ORDEN DE COMPRA
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
                                                                <span class="fas fa-book fa-lg" style="color: #3498DB;"></span>
                                                            </div>
                                                            <div>
                                                                1. DETALLES DE CONTRATACIÓN
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="datosperson" class="collapse" aria-labelledby="headingOne">
                                                <div class="card-body">
                                                    <form id="formuPAdqCompra">
                                                        <input type="hidden" name="idAdqCompra" value="<?php echo $id_adq ?>">
                                                        <div class="form-row">
                                                            <div class="form-group col-md-4">
                                                                <label for="datUpFecAdqCompra" class="small">FECHA ORDEN</label>
                                                                <input type="date" name="datUpFecAdqCompra" id="datUpFecAdqCompra" class="form-control form-control-sm" value="<?php echo $adq_compra['fecha_adquisicion'] ?>">
                                                            </div>
                                                            <div class="form-group col-md-4">
                                                                <label for="slcModalidad" class="small">MODALIDAD CONTRATACIÓN</label>
                                                                <select id="slcModalidad" name="slcModalidad" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                                    <?php
                                                                    foreach ($modalidad as $mo) {
                                                                        if ($mo['id_modalidad'] !== $adq_compra['id_modalidad']) {
                                                                            echo '<option value="' . $mo['id_modalidad'] . '">' . $mo['modalidad'] . '</option>';
                                                                        } else {
                                                                            echo '<option selected value="' . $mo['id_modalidad'] . '">' . $mo['modalidad'] . '</option>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group col-md-4">
                                                                <label for="numTotalContrato" class="small">Valor total contrato</label>
                                                                <input type="number" name="numTotalContrato" id="numTotalContrato" class="form-control form-control-sm" value="<?php echo $adq_compra['val_contrato'] ?>">
                                                            </div>
                                                        </div>
                                                        <div class="form-row">
                                                            <div class="form-group col-md-12">
                                                                <input type="hidden" name="tpBnSv" value="<?php echo $adq_compra['id_tipo_bn_sv'] ?>">
                                                                <label for="slcTipoBnSv" class="small">TIPO DE BIEN O SERVICIO</label>
                                                                <select id="slcTipoBnSv" name="slcTipoBnSv" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                                    <?php
                                                                    foreach ($tbnsv as $tbs) {
                                                                        if ($tbs['id_tipo_b_s'] !== $adq_compra['id_tipo_bn_sv']) {
                                                                            echo '<option value="' . $tbs['id_tipo_b_s'] . '">' . $tbs['tipo_compra'] . ' || ' . $tbs['tipo_contrato'] . ' || ' . $tbs['tipo_bn_sv'] . '</option>';
                                                                        } else {
                                                                            echo '<option selected value="' . $tbs['id_tipo_b_s'] . '">' . $tbs['tipo_compra'] . ' || ' . $tbs['tipo_contrato'] . ' || ' . $tbs['tipo_bn_sv'] . '</option>';
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="form-row pt-2">
                                                            <div class="form-group col-md-12">
                                                                <label for="txtObjeto" class="small">OBJETO</label>
                                                                <textarea id="txtObjeto" type="text" name="txtObjeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" placeholder="Objeto del contrato"><?php echo $adq_compra['objeto'] ?></textarea>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    <div class="text-center pt-2">
                                                        <?php if ((PermisosUsuario($permisos, 5302, 3) || $id_rol == 1)) { ?>
                                                            <button class="btn btn-primary btn-sm" id="btnUpDataAdqCompra">Actualizar</button>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-header card-header-detalles py-0 headings" id="headingBnSv">
                                                <h5 class="mb-0">
                                                    <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseBnSv" aria-expanded="true" aria-controls="collapseBnSv">
                                                        <div class="form-row">
                                                            <div class="div-icono">
                                                                <span class="fas fa-shopping-bag fa-lg" style="color: #EC7063;"></span>
                                                            </div>
                                                            <div>
                                                                2. BIENES O SERVICIOS
                                                            </div>
                                                        </div>
                                                    </a>
                                                </h5>
                                            </div>
                                            <div id="collapseBnSv" class="collapse" aria-labelledby="headingBnSv">
                                                <div class="card-body">
                                                    <div id="divEstadoBnSv">
                                                        <?php
                                                        if (!empty($listBnSv)) {
                                                        ?>
                                                            <form id="formDetallesAdq">
                                                                <input type="hidden" name="idAdq" value="<?php echo $id_adq ?>">
                                                                <table id="tableUpAdqBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th id="orderCheck">Seleccionar</th>
                                                                            <th>Bien o Servicio</th>
                                                                            <th>Cantidad</th>
                                                                            <th>Valor Unitario</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        foreach ($bnsv as $bs) {
                                                                            $id_bs = $bs['id_b_s'];
                                                                            $key = array_search($id_bs, array_column($listBnSv, 'id_bn_sv'));
                                                                            if (false !== $key) {
                                                                                $check = 'checked';
                                                                                $cant = $listBnSv[$key]['cantidad'];
                                                                                $val_c = $listBnSv[$key]['val_estimado_unid'];
                                                                            } else {
                                                                                $check = $cant = $val_c = null;
                                                                            }
                                                                        ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <div class="text-center casilla">
                                                                                        <input type="checkbox" name="check[]" value="<?php echo $bs['id_b_s'] ?>" <?php echo $check ?>>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="text-left"><i><?php echo $bs['bien_servicio'] ?></i></td>
                                                                                <td><input type="number" name="bnsv_<?php echo $bs['id_b_s'] ?>" id="bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura" value="<?php echo $cant ?>"></td>
                                                                                <td><input type="number" name="val_bnsv_<?php echo $bs['id_b_s'] ?>" id="val_bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura" value="<?php echo $val_c ?>"></td>
                                                                            </tr>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <th>Seleccionar</th>
                                                                            <th>Bien o Servicio</th>
                                                                            <th>Cantidad</th>
                                                                            <th>Valor Unitario</th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </form>
                                                            <div class="text-center pt-2">
                                                                <button class="btn btn-primary btn-sm" id="btnUpDetalAdqCompra">Actualizar</button>
                                                            </div>
                                                        <?php
                                                        } else {
                                                            echo '<div class="p-3 mb-2 bg-warning text-white">AUN NO SE HA AGREGADO NINGÚN BIEN O SERVICIO</div>';
                                                        ?>
                                                            <form id="formDetallesAdq">
                                                                <input type="hidden" name="idAdq" value="<?php echo $id_adq ?>">
                                                                <table id="tableUpAdqBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Seleccionar</th>
                                                                            <th>Bien o Servicio</th>
                                                                            <th>Cantidad</th>
                                                                            <th>Valor estimado Und.</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php
                                                                        foreach ($bnsv as $bs) {
                                                                        ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <div class="text-center">
                                                                                        <input type="checkbox" name="check[]" value="<?php echo $bs['id_b_s'] ?>">
                                                                                    </div>
                                                                                </td>
                                                                                <td class="text-left"><i><?php echo $bs['bien_servicio'] ?></i></td>
                                                                                <td><input type="number" name="bnsv_<?php echo $bs['id_b_s'] ?>" id="bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura"></td>
                                                                                <td><input type="number" name="val_bnsv_<?php echo $bs['id_b_s'] ?>" id="val_bnsv_<?php echo $bs['id_b_s'] ?>" class="form-control altura"></td>
                                                                            </tr>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <th>Seleccionar</th>
                                                                            <th>Bien o Servicio</th>
                                                                            <th>Cantidad</th>
                                                                            <th>Valor estimado Und.</th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </form>
                                                            <div class="text-center pt-2">
                                                                <?php if ((PermisosUsuario($permisos, 5302, 3) || $id_rol == 1)) { ?>
                                                                    <button class="btn btn-primary btn-sm" id="btnUpDetalAdqCompra">Actualizar</button>
                                                                <?php } ?>
                                                            </div>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center pt-3">
                                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal" href="../lista_adquisiciones.php">Regresar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                    <?php include '../../../footer.php' ?>
                </div>
                <?php include '../../../modales.php' ?>
            </div>
            <?php include '../../../scripts.php' ?>
        </body>

        </html>
<?php
    } else {
        echo 'No se puede editar esta compra';
    }
} else {
    echo 'Error al intentar obtener datos';
} ?>