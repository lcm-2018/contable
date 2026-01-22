<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT COUNT(*) AS `cantiad` FROM `ctb_libaux`";
    $rs = $cmd->query($sql);
    $registros = $rs->fetch();
    $registros = $registros['cantiad'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta la lista de chequeras creadas en el sistema
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
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
                                    LISTA DE CUENTAS CONTABLES
                                </div>
                                <?php
                                if (PermisosUsuario($permisos, 5504, 2) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div>
                                    <div class="text-right mb-2">
                                        <?php
                                        if ($registros == 0) {
                                            if (PermisosUsuario($permisos, 5504, 2) || $id_rol == 1) {
                                        ?>
                                                <button class="btn btn-outline-success btn-sm" id="cargaExcelPuc" title="Cargar plande cuentas con archico Excel"><i class="far fa-file-excel fa-lg"></i></button>
                                                <button class="btn btn-outline-primary btn-sm" id="formatoExcelPuc" title="Descargar formato cargue de plan de cuentas"><i class="fas fa-download fa-lg"></i></button>
                                        <?php
                                            }
                                        } ?>
                                    </div>
                                </div>
                                <table id="tablePlanCuentas" class="table table-striped table-bordered table-sm table-hover shadow" style="table-layout: fixed;width: 98%;">
                                    <thead class="text-center">
                                        <tr>
                                            <th style="width: 12%;">Fecha</th>
                                            <th style="width: 12%;">Cuenta</th>
                                            <th style="width: 40%;">Nombre</th>
                                            <th style="width: 5%;">Tipo</th>
                                            <th style="width: 5%;">Nivel</th>
                                            <th style="width: 5%;" title="Desagregación de terceros">Des.</th>
                                            <th style="width: 7%;">Estado</th>
                                            <th style="width: 14%;">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificartablePlanCuentas">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cuenta</th>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <th>Nivel</th>
                                            <th title="Desagregación de terceros">Des.</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="text-center pt-4">
                            </div>
                        </div>

                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <!-- Modal formulario-->
        <?php include '../modales.php' ?>
    </div>
    <?php include '../scripts.php' ?>

</body>

</html>