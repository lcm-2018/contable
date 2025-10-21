<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
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
                                    LISTADO DE INFORMES FINANCIEROS
                                </div>
                            </div>
                        </div>
                        <ul class="nav nav-tabs" id="myTab">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">Entidades de control </a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="FormInfFinanciero(1);">Contraloría SIA</a>
                                    <a class="dropdown-item sombra" href="#" onclick="FormInfFinanciero(2);">Contraloría General - Cuipo</a>
                                    <a class="dropdown-item sombra" href="#" onclick="FormInfFinanciero(3);">Contraloría General - Ejecución</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item sombra" href="#" onclick="FormInfFinanciero(4);">Sia Observa</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">SIHO</a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="FormInfFinanciero(5);">Ejecución Presupuestal</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle sombra" data-toggle="dropdown" href="#" role="button" aria-expanded="false">SUPERSALUD</a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item sombra" href="#" onclick="FormInfFinanciero(6);" title="Cuentas por pagar">FT004</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="contenedor card-body" id="areaReporte">

                    </div>
                </div>
            </main>

            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>

    <!-- Script -->
    <script>
        $('#myTab a').on('click', function(e) {
            e.preventDefault()
            $(this).tab('show')
        })
    </script>

    <script type="text/javascript" src="../js/informes.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>