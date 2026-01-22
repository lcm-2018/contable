<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../conexion.php';
include '../permisos.php';
$key = array_search('59', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php' ?>
<link href="css/handsontable.min.css?v=<?php echo date('YmdHis') ?>" rel="stylesheet" />
<style>
    .modal-fullscreen {
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        margin: 0;
    }

    .modal-header,
    .modal-body {
        padding: 1rem;
        height: 100%;
        overflow-y: auto;
    }
</style>

<body class="sb-nav-fixed <?= $_SESSION['navarlat'] == '1' ? 'sb-sidenav-toggled' : ''; ?>">
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
                                    <i class="fas fa-copy fa-lg" style="color:#1D80F7"></i>
                                    CONSULTAS DINÁMICAS
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <?php if (PermisosUsuario($permisos, 5901, 2) || $id_rol == 1) { ?>
                                <input type="hidden" id="peReg" value="1">
                            <?php } else { ?>
                                <input type="hidden" id="peReg" value="0">
                            <?php } ?>
                            <input type="hidden" id="id_consulta" value="<?= $_POST['id_consulta'] ?>">
                            <table id="tableConsultas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="accionConsultas">
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
    <script src="js/funcionconsultas.js"></script>
    <script src="js/handsontable.js?v=<?php echo date('YmdHis') ?>"></script>
</body>

</html>