<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_presupuestos`.`id_pto`
                , `pto_presupuestos`.`descripcion`
                , `pto_presupuestos`.`nombre`
                , `pto_presupuestos`.`estado`
                , `pto_tipo`.`nombre` AS `tipo`
            FROM
                `pto_presupuestos`
                INNER JOIN `pto_tipo` 
                    ON (`pto_presupuestos`.`id_tipo` = `pto_tipo`.`id_tipo`)
            WHERE (`pto_presupuestos`.`id_pto` = $id_pto_presupuestos)";
    $rs = $cmd->query($sql);
    $nomPresupuestos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
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
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTADO DE <?php echo strtoupper($nomPresupuestos['nombre']); ?>
                                </div>
                                <input type="hidden" id="id_pto_ppto" value="<?php echo $_POST['id_pto']; ?>">
                                <?php if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div class="text-right mb-2">
                                    <?php
                                    if ($nomPresupuestos['estado'] == 1) {
                                        if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
                                    ?>
                                            <button class="btn btn-outline-success btn-sm" id="cargaExcelPto" title="Cargar presupuesto con archico Excel"><i class="far fa-file-excel fa-lg"></i></button>
                                            <button class="btn btn-outline-primary btn-sm" id="formatoExcelPto" title="Descargar formato cargue de presupuesto"><i class="fas fa-download fa-lg"></i></button>
                                            <button class="btn btn-success btn-sm" id="cerrarPresupuestos">CERRAR <?php echo strtoupper($nomPresupuestos['tipo']); ?></button>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            CERRADO
                                        </button>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <input type="hidden" id="estadoPresupuesto" value="<?php echo $nomPresupuestos['estado']; ?>">
                                <input type="hidden" id="idPtoEstado" value="<?php echo $id_pto_presupuestos ?>">
                                <table id="tableCargaPresupuesto" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Rubro</th>
                                            <th>Detalle</th>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th>Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificarCargaPresupuesto">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Rubro</th>
                                            <th>Detalle</th>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-secondary" style="width: 7rem;" href="lista_presupuestos.php"> VOLVER</a>
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