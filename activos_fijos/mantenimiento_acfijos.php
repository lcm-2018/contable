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
$slc1 = $slc2 = $slc3 = '';
switch ($tip_bus) {
    case '0':
        $slc1 = 'selected';
        break;
    case '1':
        $slc2 = 'selected';
        break;
    case '2':
        $slc3 = 'selected';
        break;
}
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
                                    <i class="fas fa-tools fa-lg" style="color:#EB984E"></i>
                                    GESTIÓN MANTENIMIENTO DE ACTIVOS FIJOS.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <form id="formAjustarCantidad">
                                    <div class="form-row text-center">
                                        <div class="form-group col-md-2">
                                            <label for="tipoBusqueda" class="small">Tipo de búsqueda</label>
                                            <select id="tipoBusqueda" name="tipoBusqueda" class="form-control form-control-sm">
                                                <option value="0" <?php echo $slc1 ?>>--Seleccionar--</option>
                                                <option value="1" <?php echo $slc2 ?>>PLACA</option>
                                                <option value="2" <?php echo $slc3 ?>>No. SERIAL</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-10">
                                            <label for="buscarActFijoMnto" class="small">Buscar activo fijo</label>
                                            <input id="buscarActFijoMnto" class="form-control form-control-sm" value="<?php echo $acfijo ?>">
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
                                    $mantenimiento = $data_acfj['mantenimiento'];
                                    if ($id_sepl != 0) {
                                        if ($mantenimiento == 1) {
                                    ?>
                                            <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                                            <input type="hidden" id="id_ser_comp" value="<?php echo $data_acfj['id_serial'] ?>">
                                            <form id="formDataAcFijo">
                                                <input type="hidden" name="num_serial" value="<?php echo $data_acfj['num_serial'] ?>">
                                                <input type="hidden" name="placa" value="<?php echo $data_acfj['placa'] ?>">
                                                <input type="hidden" name="id_serial" value="<?php echo $data_acfj['id_serial'] ?>">
                                                <input type="hidden" name="id_activo_fijo" value="<?php echo $data_acfj['id_activo_fijo'] ?>">
                                                <input type="hidden" name="tipo_entra" value="<?php echo $data_acfj['tipo_entra'] ?>">
                                                <input type="hidden" name="bien_servicio" value="<?php echo $data_acfj['bien_servicio'] ?>">
                                                <input type="hidden" name="mantenimiento" value="<?php echo $data_acfj['mantenimiento'] ?>">
                                                <input type="hidden" name="depreciable" value="<?php echo $data_acfj['depreciable'] ?>">
                                                <input type="hidden" name="marca" value="<?php echo $data_acfj['marca'] ?>">
                                                <input type="hidden" name="modelo" value="<?php echo $data_acfj['modelo'] ?>">
                                                <input type="hidden" name="val_unit" value="<?php echo $data_acfj['val_unit'] ?>">
                                                <input type="hidden" name="descripcion" value="<?php echo $data_acfj['descripcion'] ?>">
                                                <input type="hidden" name="tipo_activo" value="<?php echo $data_acfj['tipo_activo'] ?>">
                                                <input type="hidden" name="tip_in" value="<?php echo $tipo_e ?>">
                                            </form>
                                            <table id="tableMantenimientoAcfijo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th>ID</th>
                                                        <th># Orden</th>
                                                        <th>Fecha Inicio</th>
                                                        <th>Fecha Regreso</th>
                                                        <th>Tipo</th>
                                                        <th>Concepto</th>
                                                        <th>Valor deterioro</th>
                                                        <th>Observaciones</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarMantenimientoAcfijo">
                                                </tbody>
                                            </table>
                                <?php } else {
                                            echo '
                                    <div class="alert alert-warning text-center" role="alert">
                                        ACTIVO FIJO NO SE HA REGISTRADO COMO UN ELEMENTO PARA REALIZAR MANTENIMIENTO.
                                        <div style="font-size:70%"><i>*Para realizar mantenimiento debe  actualizar activo fijo y elegir la opcion de "Mantenimiento" en SI.</i></div>
                                    </div>
                                    ';
                                        }
                                    }
                                } ?>
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