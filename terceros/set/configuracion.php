<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('52', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];

?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-10">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    CONFIGURACIONES PARA TERCEROS
                                </div>
                                <?php if (PermisosUsuario($permisos, 5201, 2) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                $contador = 1;
                                ?>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configOne" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-briefcase fa-lg" style="color: #F9E79F;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. RESPONSABILIDADES ECONÓMICAS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configOne" class="collapse" aria-labelledby="headingOne">
                                        <div class="card-body">
                                            <table id="tableResponsabilidades" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>CÓDIGO</th>
                                                        <th>DESCRIPCIÓN</th>
                                                        <th>ACCIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaRespEcon">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingTwo">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configTwo" aria-expanded="true" aria-controls="collapseTwo">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-id-badge fa-lg" style="color: #1fc0e9ff;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. PERFILES DE TERCEROS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configTwo" class="collapse" aria-labelledby="headingTwo">
                                        <div class="card-body">
                                            <table id="tablePerfilTercero" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>DESCRIPCIÓN</th>
                                                        <th>ACCIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaPerfilTercero">
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
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>