<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../head.php'; ?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ?  'sb-sidenav-toggled' : '' ?>">
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
                                    CONCILIACIÓN BANCARIA
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <table id="tableConcBancaria" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr class="text-center">
                                        <th>Banco</th>
                                        <th>Tipo<br>Cuenta</th>
                                        <th>Descripción</th>
                                        <th>No. Cta.</th>
                                        <th>Saldo</th>
                                        <th>Conciliar</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarTableConcBancaria">
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