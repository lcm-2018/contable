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
// Consulta la lista de chequeras creadas en el sistema
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : ''; ?>">

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
                                    LISTA DE CUENTAS BANCARIAS
                                </div>
                                <?php
                                if (PermisosUsuario($permisos, 5607, 0) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <table id="tableCuentasBanco" class="table table-striped table-bordered table-sm table-hover shadow" style="width:100%">
                                <thead>
                                    <tr class="text-center">
                                        <th>Banco</th>
                                        <th>Tipo de cuenta</th>
                                        <th>Nombre</th>
                                        <th>Número</th>
                                        <th>Código</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificartabletableCuentasBanco">
                                </tbody>
                            </table>
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