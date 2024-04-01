<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
include '../../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../../head.php';

?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">

    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-11">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTADO DE INFORMES CONTABILIDAD
                                </div>
                            </div>
                        </div>
                        <ul class="nav nav-tabs" id="myTab">
                            <li class="nav-item">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">Internos </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReporteContable(11);">Libros auxiliares</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReporteContable(12);">Balance de prueba</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReportePresupuesto(3);">Mayor y balance</a>
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(2);">Estado financieros</a>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">Impuestos y descuentos </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReporteContable(21);">Municipales</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReporteContable(22);">DIAN</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReporteContable(24);">Estampillas</a>
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReporteContable(23);">Otros descuentos</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">Entidades de control </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(1);">Contraloría SIA</a>
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(2);">Cuipo ingresos</a>
                                    <a class="dropdown-item sombra" id="settings-tab2" href="#" onclick="abrirLink(3);">Cuipo gastos</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(13);">Sia Observa</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item sombra" href="#" onclick="abrirLink(1);">Ministerio Salud SIHO</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">Certificados </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="cargarReporteContable(25);">Certificado de ingresos y retenciones</a>
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

            <?php include '../../footer.php' ?>
        </div>
        <!-- Modal formulario-->
        <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divForms">

                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php include '../../scripts.php' ?>

    <!-- Script -->
    <script>
        $('#myTab a').on('click', function(e) {
            e.preventDefault()
            $(this).tab('show')
        })
    </script>
</body>

</html>