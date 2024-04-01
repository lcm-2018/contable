<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_mes`, `codigo`, `nom_mes`, `fin_mes`
            FROM
                `nom_meses`";
    $res = $cmd->query($sql);
    $meses = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
                                <div class="col-md-11">
                                    <i class="fas fa-list-alt fa-lg" style="color:#1D80F7"></i>
                                    LISTADO DE INFORMES DE NÓMINA.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <table id="tableListInfoNomina" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr class="text-center centro-vertical">
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th class="w-15">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="accionListInfoNomina">
                                    <tr>
                                        <td>1</td>
                                        <td>REPORTE LIBRANZAS</td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" name="numIDNomina" placeholder="ID Nómina">
                                                <div class="input-group-append">
                                                    <button value="1" class="btn btn-outline-warning infoNomina" type="button" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>REPORTE EMBARGOS</td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" name="numIDNomina" placeholder="ID Nómina">
                                                <div class="input-group-append">
                                                    <button value="2" class="btn btn-outline-warning infoNomina" type="button" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>REPORTE SINDICALIZACIÓN</td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" name="numIDNomina" placeholder="ID Nómina">
                                                <div class="input-group-append">
                                                    <button value="3" class="btn btn-outline-warning infoNomina" type="button" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>ENVIAR DESPRENDIBLES DE NÓMINA MASIVO - INDIVIDUAL</td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" name="numIDNomina" placeholder="ID Nómina">
                                                <div class="input-group-append">
                                                    <button value="4" class="btn btn-outline-warning infoNomina" type="button" title="ENVIO MASIVO"><span class="fas fa-eye fa-lg"></span></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>REPORTE POR CONCEPTOS LIQUIDADOS</td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" name="numIDNomina" placeholder="ID Nómina">
                                                <div class="input-group-append">
                                                    <button value="5" class="btn btn-outline-warning infoNomina" type="button" title="REPORTE POR CONCEPTOS"><span class="fas fa-eye fa-lg"></span></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>6</td>
                                        <td>REPORTE PARAFISCALES</td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" name="numIDNomina" placeholder="ID Nómina">
                                                <div class="input-group-append">
                                                    <button value="6" class="btn btn-outline-warning infoNomina" type="button" title="REPORTE POR CONCEPTOS"><span class="fas fa-eye fa-lg"></span></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>7</td>
                                        <td>REPORTE SIHO</td>
                                        <td class="text-center">
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" name="numIDNomina" placeholder="ID Nómina">
                                                <div class="input-group-append">
                                                    <button value="7" class="btn btn-outline-warning infoNomina" type="button" title="REPORTE POR CONCEPTOS"><span class="fas fa-eye fa-lg"></span></button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
    <script type="text/javascript" src="../js/funciones.js"></script>
</body>

</html>