<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../conexion.php';
include '../permisos.php';
$key = array_search('8', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$data = isset($_POST['datos']) ? explode('|', $_POST['datos']) : array('0', '', '0', '');
$tip_bus =  $data[0];
$id_sepl =  $data[1];
$tipo_e = $data[2];
$acfijo = $data[3];
$indice = 0;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-pencil-ruler fa-lg" style="color:#F1C40F"></i>
                                    GESTIÓN DE ACTIVOS FIJOS.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="modComponentes">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra" data-toggle="collapse" data-target="#collapsemodComponentes" aria-expanded="true" aria-controls="collapsemodComponentes">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-cogs fa-lg" style="color: #1ABC9C;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $indice++ ?>. COMPONENTES
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapsemodComponentes" class="collapse show" aria-labelledby="modComponentes">
                                        <div class="card-body">
                                            <form id="formAjustarCantidad">
                                                <div class="form-row text-center">
                                                    <div class="form-group col-md-2">
                                                        <label for="tipoBusqueda" class="small">Tipo de búsqueda</label>
                                                        <select id="tipoBusqueda" name="tipoBusqueda" class="form-control form-control-sm">
                                                            <option value="0" <?php echo $tip_bus == 0 ? 'selected' : '' ?>>--Seleccionar--</option>
                                                            <option value="1" <?php echo $tip_bus == 1 ? 'selected' : '' ?>>PLACA</option>
                                                            <option value="2" <?php echo $tip_bus == 2 ? 'selected' : '' ?>>No. SERIAL</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-md-10">
                                                        <label for="buscarActFijo" class="small">Buscar activo fijo</label>
                                                        <input id="buscarActFijo" class="form-control form-control-sm" value="<?php echo $acfijo ?>">
                                                        <input type="hidden" id="id_ser_pla" value="<?php echo $id_sepl ?>">
                                                        <input type="hidden" id="tipo_entra" value="<?php echo $tipo_e ?>">
                                                    </div>
                                                </div>
                                            </form>
                                            <?php if ($id_sepl != 0) { ?>
                                                <?php
                                                $sql = "SELECT
                                                    `seg_num_serial`.`num_serial`
                                                    , `seg_num_serial`.`placa`
                                                    , `seg_num_serial`.`id_serial`
                                                    , `seg_num_serial`.`id_activo_fijo`
                                                    , `seg_num_serial`.`tipo_entra`
                                                    , `ctt_bien_servicio`.`bien_servicio`
                                                    , `seg_entra_detalle_activos_fijos`.`mantenimiento`
                                                    , `seg_entra_detalle_activos_fijos`.`depreciable`
                                                    , `seg_entra_detalle_activos_fijos`.`marca`
                                                    , `seg_entra_detalle_activos_fijos`.`modelo`
                                                    , `seg_entra_detalle_activos_fijos`.`val_unit`
                                                    , `seg_entra_detalle_activos_fijos`.`descripcion`
                                                    , `seg_tipo_activo`.`descripcion` AS `tipo_activo`
                                                FROM
                                                    `seg_num_serial` 
                                                    INNER JOIN `seg_entra_detalle_activos_fijos`
                                                        ON (`seg_num_serial`.`id_activo_fijo` = `seg_entra_detalle_activos_fijos`.`id_acfijo`)
                                                    INNER JOIN `ctt_bien_servicio` 
                                                        ON (`seg_entra_detalle_activos_fijos`.`id_prod` = `ctt_bien_servicio`.`id_b_s`)
                                                    INNER JOIN `seg_tipo_activo` 
                                                        ON (`seg_entra_detalle_activos_fijos`.`id_tipo_activo` = `seg_tipo_activo`.`id_tipo_act`)
                                                WHERE `seg_num_serial`.`tipo_entra` = '$tipo_e' AND `seg_num_serial`.`id_serial` = '$id_sepl'";
                                                try {
                                                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                                                    $rs = $cmd->query($sql);
                                                    $data_acfj = $rs->fetch();
                                                    $cmd = null;
                                                } catch (PDOException $e) {
                                                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                                                }
                                                ?>
                                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                                                <div class="shadow detalles-empleado mb-4">
                                                    <div class="row">
                                                        <div class="div-mostrar bor-top-left col-md-2">
                                                            <label class="lbl-mostrar">No. SERIE</label>
                                                            <div class="div-cont"><?php echo $data_acfj['num_serial'] ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-2">
                                                            <label class="lbl-mostrar">No. PLACA</label>
                                                            <div class="div-cont"><?php echo $data_acfj['placa'] ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-2">
                                                            <label class="lbl-mostrar">ID</label>
                                                            <div class="div-cont"><?php echo $data_acfj['id_serial'] ?></div>
                                                            <input type="hidden" id="id_ser_comp" value="<?php echo $data_acfj['id_serial'] ?>">
                                                        </div>
                                                        <div class="div-mostrar bor-top-right col-md-6">
                                                            <label class="lbl-mostrar">NOMBRE ACTIVO FIJO</label>
                                                            <div class="div-cont"><?php echo mb_strtoupper($data_acfj['bien_servicio']) ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="div-mostrar col-md-1">
                                                            <label class="lbl-mostrar">MANTENIMIENTO</label>
                                                            <div class="div-cont"><?php echo $data_acfj['mantenimiento'] == 1 ? 'SI' : 'NO' ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-1">
                                                            <label class="lbl-mostrar">DEPRECIABLE</label>
                                                            <div class="div-cont"><?php echo $data_acfj['depreciable'] == 1 ? 'SI' : 'NO' ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-3">
                                                            <label class="lbl-mostrar">MARCA</label>
                                                            <div class="div-cont"><?php echo mb_strtoupper($data_acfj['marca']) ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-2">
                                                            <label class="lbl-mostrar">MODELO</label>
                                                            <div class="div-cont"><?php echo $data_acfj['modelo'] ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-2">
                                                            <label class="lbl-mostrar">VALOR</label>
                                                            <div class="div-cont"><?php echo pesos($data_acfj['val_unit']) ?></div>
                                                        </div>
                                                        <div class="div-mostrar col-md-3">
                                                            <label class="lbl-mostrar">TIPO DE ACTIVO</label>
                                                            <div class="div-cont"><?php echo $data_acfj['tipo_activo'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="div-mostrar bor-bottom-left bor-bottom-right col-md-12">
                                                            <label class="lbl-mostrar">DESCRIPCIÓN</label>
                                                            <div class="div-cont"><?php echo $data_acfj['descripcion'] == '' ? '<div class="inactivo">SIN DESCRIPCIÓN</div>' : mb_strtoupper($data_acfj['descripcion']) ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <table id="tableComponentesActFijo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Bien o servicio</th>
                                                            <th>Mantenimiento</th>
                                                            <th>Depreciable</th>
                                                            <th>Marca</th>
                                                            <th>Modelo</th>
                                                            <th>Valor</th>
                                                            <th>Descripción</th>
                                                            <th>Cantidad</th>
                                                            <th>Serial</th>
                                                            <th>Tipo Activo</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarComponentesActFijo">
                                                    </tbody>
                                                </table>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="metDeprecia">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemetDeprecia" aria-expanded="true" aria-controls="collapsemetDeprecia">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-long-arrow-alt-down fa-lg" style="color: #E74C3C;"></span>
                                                        <span class="fas fa-long-arrow-alt-up fa-lg" style="color: #E74C3C;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $indice++ ?>. MÉTODO DE DEPRECIACIÓN
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapsemetDeprecia" class="collapse" aria-labelledby="metDeprecia">
                                        <div class="card-body">
                                            <?php if ($id_sepl != 0) { ?>
                                                <table id="tableDepreciacionesAcfijo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th>ID</th>
                                                            <th>Método depreciación</th>
                                                            <th>Fecha de Inicio</th>
                                                            <th>Vida Útil <br>(Meses)</th>
                                                            <th>Valor Residual</th>
                                                            <th>Capacidad <br>de producción</th>
                                                            <th>Observaciones</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarDepreciacionAcfijo">
                                                    </tbody>
                                                </table>
                                            <?php } else { ?>
                                                <div class="alert alert-warning" role="alert">
                                                    PRIMERO DEBE ELEGIR UN ACTIVO FIJO EN LA SECCIÓN <b>1. COMPONENTES</b>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="ubicTraslado">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseubicTraslado" aria-expanded="true" aria-controls="collapseubicTraslado">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-map-marker-alt fa-lg" style="color: #5DADE2;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $indice++ ?>. UBICACIÓN Y TRASLADO
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapseubicTraslado" class="collapse" aria-labelledby="ubicTraslado">
                                        <div class="card-body">
                                            <?php if ($id_sepl != 0) { ?>
                                                <table id="tableUbicacionTrasladoAcfijo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th>ID</th>
                                                            <th>Sede</th>
                                                            <th>Centro de costo</th>
                                                            <th>Fecha</th>
                                                            <th>Estado</th>
                                                            <th>Responsable</th>
                                                            <th>Observaciones</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarUbicacionTrasladoAcfijo">
                                                    </tbody>
                                                </table>
                                            <?php } else { ?>
                                                <div class="alert alert-warning" role="alert">
                                                    PRIMERO DEBE ELEGIR UN ACTIVO FIJO EN LA SECCIÓN <b>1. COMPONENTES</b>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <!--parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="notas">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapesenotas" aria-expanded="true" aria-controls="collapesenotas">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-sticky-note fa-lg" style="color: #8E44AD;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $indice++ ?>. NOTAS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapesenotas" class="collapse" aria-labelledby="notas">
                                        <div class="card-body">
                                            <?php if ($id_sepl != 0) { ?>
                                                <table id="tableNotasAcfijo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                    <thead>
                                                        <tr class="text-center">
                                                            <th>ID</th>
                                                            <th>Tipo de nota</th>
                                                            <th>Fecha</th>
                                                            <th>Valor</th>
                                                            <th>Observaciones</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificarNotasAcfijo">
                                                    </tbody>
                                                </table>
                                            <?php } else { ?>
                                                <div class="alert alert-warning" role="alert">
                                                    PRIMERO DEBE ELEGIR UN ACTIVO FIJO EN LA SECCIÓN <b>1. COMPONENTES</b>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="codQR">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsecodQR" aria-expanded="true" aria-controls="collapsecodQR">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-qrcode fa-lg" style="color: #ABEBC6;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $indice++ ?>. CODIFICACIÓN QR
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapsecodQR" class="collapse" aria-labelledby="codQR">
                                        <div class="card-body">
                                            <table id="tableQRsActivoFijo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th>ID</th>
                                                        <th>Nombre</th>
                                                        <th>Serial</th>
                                                        <th>Placa</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarQRActivoFijo">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="Depre">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseDepre" aria-expanded="true" aria-controls="collapseDepre">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-house-damage fa-lg" style="color: #5D6D7E;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $indice++ ?>. DEPRECIACIONES
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapseDepre" class="collapse" aria-labelledby="Depre">
                                        <div class="card-body">
                                            <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                                            <table id="tableDepreciaciones" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th>Mes</th>
                                                        <th>Fin Mes</th>
                                                        <th>Registro</th>
                                                        <th>Total</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarDepreciacion">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <?php include '../modales.php' ?>
    </div>
    <?php include '../scripts.php' ?>
</body>

</html>