<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
include 'conexion.php';
include 'permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include 'head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include 'navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include 'navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <span class="fas fa-house-user fa-lg" style="color: #1D80F7"></span> INICIO
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="container">
                                <!-- Button trigger modal -->
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#staticBackdrop">
                                    Launch static backdrop modal
                                </button>

                                <div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="staticBackdropLabel">Modal title</h5>
                                                <div style="position:absolute" class="w-100">
                                                    <div style="position:relative; top: -80px; left: -10px;" class="text-center">
                                                        <img src="./images/logos/cronhis.png" class="img-fluid" alt="Imagen" style="max-width: 70px;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-body">
                                                ...
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include 'footer.php' ?>
        </div>
        <?php include 'modales.php' ?>
    </div>
    <?php include 'scripts.php' ?>
</body>

</html>