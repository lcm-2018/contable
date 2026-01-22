<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php';
// Tabla que genera el reporte datos_detalle_rad.php
// Consulta tipo de presupuesto en la base de datos
$id_rad = isset($_POST['id_rad']) ? $_POST['id_rad'] : exit('Acceso no permitido');
$id_ppto = $_POST['id_ejec'];
$automatico = '';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
               `pto_rad`. `id_pto_rad`
               , `pto_rad`.`id_manu`
               , `pto_rad`.`fecha`
               , `pto_rad`.`objeto`
               , `pto_rad`.`num_factura`
               , CONCAT(`tb_terceros`.`nom_tercero`, ' -> ', `tb_terceros`.`nit_tercero`) AS `tercero`
               , `pto_rad`.`id_tercero_api`
            FROM
                `pto_rad`
            LEFT JOIN `tb_terceros` 
                ON (`pto_rad`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE `id_pto_rad` = $id_rad";
    $rs = $cmd->query($sql);
    $datosRad = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$automatico = 'readonly';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT (SUM(`valor`) - SUM(`valor_liberado`)) as `valorCdp` FROM `pto_rad_detalle` WHERE `id_pto_rad` = $id_rad";
    $rs = $cmd->query($sql);
    $totalCdp = $rs->fetch();
    // total con puntos de mailes number_format()
    $valor = !empty($totalCdp['valorCdp']) ? $totalCdp['valorCdp'] : 0;
    $total = number_format($valor, 2, '.', ',');
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Buscar si el usuario tiene registrado fecha de sesion
try {
    $sql = "SELECT `fecha` FROM `tb_fin_fecha` WHERE `id_usuario` = {$_SESSION['id_user']} AND `vigencia` = '{$_SESSION['vigencia']}'";
    $res = $cmd->query($sql);
    $fechases = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$fecha = date('Y-m-d', strtotime($datosRad['fecha']));

// Consulta funcion fechaCierre del modulo 4
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 54, $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$cmd = null;
?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : '' ?>">

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
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    DETALLE RECONOCIMIENTO PRESUPUESTAL
                                </div>


                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="divFormDoc">
                                <form id="formAddEjecutaPresupuesto">
                                    <input type="hidden" id="id_pto_presupuestos" name="id_pto_presupuestos" value="<?php echo $id_ppto; ?>">
                                    <input type="hidden" id="id_rads" name="id_rads" value="<?php echo $id_rad; ?>">
                                    <input type="hidden" id="id_pto_docini" value="<?php echo $datosRad['id_manu']; ?>">
                                    <div class="right-block">
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">NUMERO CDP:</label></div>
                                            </div>
                                            <div class="col-10">
                                                <input type="number" name="numCdp" id="numCdp" class="form-control form-control-sm" value="<?php echo $datosRad['id_manu']; ?>" onchange="buscarCdp(value,'CDP')" <?php echo $automatico; ?> readonly>
                                            </div>
                                        </div>
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                            </div>
                                            <div class="col-10">
                                                <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha; ?>" onchange="buscarConsecutivo('CDP');" readonly>
                                            </div>
                                        </div>
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="tercero" class="small">TERCERO:</label></div>
                                            </div>
                                            <div class="col-10">
                                                <input type="text" name="tercero" id="tercero" class="form-control form-control-sm" value="<?php echo $datosRad['tercero']; ?>" readonly>
                                                <input type="hidden" id="id_tercero" name="id_tercero" value="<?php echo $datosRad['id_tercero_api']; ?>">
                                            </div>
                                        </div>
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                            </div>
                                            <div class="col-10"> <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required" readonly><?php echo $datosRad['objeto']; ?></textarea></div>
                                        </div>
                                        <div class="row pb-1">
                                            <div class="col-2">
                                                <div class="col"><label for="sol" class="small">No Factura:</label></div>
                                            </div>
                                            <div class="col-10">
                                                <input type="text" name="solicitud" id="solicitud" class="form-control form-control-sm" value="<?php echo $datosRad['num_factura']; ?>" readonly>
                                            </div>
                                        </div>

                                    </div>
                                </form>
                            </div>
                            <form id="formAddModDetalleRad">
                                <input type="hidden" id="id_rad" name="id_rad" value="<?php echo $id_rad; ?>">
                                <input type="hidden" id="id_pto_rad" name="id_pto_rad" value="<?php echo $id_ppto; ?>">
                                <input type="hidden" id="id_pto_movto" name="id_pto_movto" value="<?php echo $id_ppto; ?>">
                                <table id="tableEjecRad" class="table table-striped table-bordered table-sm table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">ID</th>
                                            <th style="width: 60%;">Codigo</th>
                                            <th style="width: 20%;" class="text-center">Valor</th>
                                            <th style="width: 12%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarEjeRad">
                                    </tbody>
                                </table>
                            </form>
                            <div class="text-center pt-4">
                                <?php if (PermisosUsuario($permisos, 5401, 6) || $id_rol == 1) { ?>
                                    <a type="button" class="btn btn-primary btn-sm" onclick="imprimirFormatoRad(<?php echo $id_rad; ?>);" style="width: 5rem;"> <span class="fas fa-print "></span></a>
                                <?php } ?>
                                <a type="button" id="volverListaRads" class="btn btn-danger btn-sm" style="width: 5rem;" href="#"> VOLVER</a>

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