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
                    <div class="card mb-11">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTADO DE INFORMES PRESUPUESTALES
                                </div>
                            </div>
                        </div>
                        <ul class="nav nav-tabs" id="myTab">
                            <li class="nav-item">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">Internos </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(1);">Ejecución presupuestal de ingresos</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(2);">Ejecución presupuestal de gastos</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(3);">Libros presupuestales</a>
                                    <a class="dropdown-item sombra" id="sl_libros_aux_pto">Libros auxiliares de presupuesto<a>
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(2);">Modificaciones presupuestales ingresos</a>
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(2);">Modificaciones presupuestales gastos</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">Entidades de control </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(1);">Contraloría SIA</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(6);">Contraloría General - Cuipo</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(8);">Contraloría General - Ejecución</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(13);">Sia Observa</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">SIHO </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(1);">Homologación de ingresos</a>
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(2);">Homologación de gastos</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(5);">Reporte 2193 de ingresos</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(4);">Reporte 2193 de gastos</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">Otros </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(7);">Reporte de anulaciones</a>
                                </div>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane active" id="internos" role="tabpanel" aria-labelledby="internos-tab">

                            </div>
                            <div class="tab-pane" id="profile" role="tabpanel" aria-labelledby="profile-tab">CAMPO2</div>
                            <div class="tab-pane" id="messages" role="tabpanel" aria-labelledby="messages-tab">
                            </div>
                            <div class="tab-pane" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Cras justo odio</li>
                                    <li class="list-group-item">Dapibus ac facilisis in</li>
                                    <li class="list-group-item">Morbi leo risus</li>
                                    <li class="list-group-item">Porta ac consectetur ac</li>

                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="contenedor card-body" id="areaReporte">

                    </div>
                </div>
            </main>

            <?php include '../footer.php' ?>
        </div>
        <?php include '../modales.php' ?>
    </div>


    <?php include '../scripts.php' ?>

    <!-- Script -->
    <script>
        $('#myTab a').on('click', function(e) {
            e.preventDefault()
            $(this).tab('show')
        })
    </script>

    <script type="text/javascript" src="js/libros_aux_pto/libros_aux_pto.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>