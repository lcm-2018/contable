<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../conexion.php';
include '../permisos.php';
$key = array_search('8', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '0';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_entrada`, `descripcion` FROM  `seg_tipo_entrada`";
    $rs = $cmd->query($sql);
    $tentradas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
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
                                    <i class="fas fa-list-alt fa-lg" style="color:#1D80F7"></i>
                                    ENTRADAS PENDIENTES A ACTIVOS FIJOS.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="m-0 row justify-content-center">
                                <div class="form-group col-auto text-center">
                                    <label for="slctipoEntradaAF" class="small">tipo de entrada</label>
                                    <select id="slctipoEntradaAF" name="slctipoEntradaAF" class="form-control form-control-sm" aria-label="Default select example">
                                        <option value="0">--Seleccionar--</option>
                                        <?php
                                        foreach ($tentradas as $ts) {
                                            if ($ts['id_entrada'] != 2) {
                                                $slc = $ts['id_entrada'] == $tipo ? 'selected' : '';
                                                echo '<option ' . $slc . ' value="' . $ts['id_entrada'] . '">' . $ts['descripcion'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                            <input type="hidden" id="tipo_entra_acfijo" value="<?php echo $tipo ?>">
                            <?php
                            switch ($tipo) {
                                case '1':
                            ?>
                                    <table id="tableEntradasActFijosProveedor" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Objeto</th>
                                                <th>Fecha</th>
                                                <th>Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modificarEntradasActfijo">
                                        </tbody>
                                    </table>
                                <?php
                                    break;
                                case '3':
                                case '4':
                                case '5':
                                case '6':
                                case '7':
                                ?>
                                    <table id="tableEntradasActFijosDona" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <th>ID</th>
                                                <th>Tercero</th>
                                                <th>Acta/Remisión</th>
                                                <th>Fecha</th>
                                                <th>Observaciones</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="modificarEntradasActFijosDon">
                                        </tbody>
                                    </table>
                                <?php
                                    break;
                                default:
                                ?>
                                    <div class="alert alert-info text-center" role="alert">
                                        SELECCIONAR UN TIPO DE ENTRADA A ACTIVOS FIJOS.
                                    </div>
                            <?php

                                    break;
                            } ?>
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