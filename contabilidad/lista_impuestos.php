<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
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
                                    LISTA DE IMPUESTOS
                                </div>
                                <?php
                                if (PermisosUsuario($permisos, 5506, 2) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="modTipoRte">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodTipoRte" aria-expanded="true" aria-controls="collapsemodTipoRte">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-file-contract fa-lg" style="color: #2ECC71;"></span>
                                                    </div>
                                                    <div>
                                                        1. TIPO DE RETENCIÓN
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapsemodTipoRte" class="collapse" aria-labelledby="modTipoRte">
                                        <div class="card-body">
                                            <table id="tableTipoRetencion" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th>ID</th>
                                                        <th>Tipo Retención</th>
                                                        <th>Responsable</th>
                                                        <th>Estado</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarTipoRetencion">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="Retenciones">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseRetenciones" aria-expanded="true" aria-controls="collapseRetenciones">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-file-signature fa-lg" style="color: #E74C3C;"></span>
                                                    </div>
                                                    <div>
                                                        2. RETENCIONES
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapseRetenciones" class="collapse" aria-labelledby="Retenciones">
                                        <div class="card-body">
                                            <table id="tableRetenciones" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Tipo Retención</th>
                                                        <th>Retención</th>
                                                        <th>Cuenta</th>
                                                        <th>Estado</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarRetencioness">
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
    <script src="js/funciones_retencion.js?<?= date('YmdHHmmss') ?>"></script>

</body>

</html>