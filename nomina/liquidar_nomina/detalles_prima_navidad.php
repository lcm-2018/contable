<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$anio = $_SESSION['vigencia'];
if (isset($_GET['per'])) {
    $periodo = $_GET['per'];
    $mes = '12';
} else {
    header('Location: listempliquidar.php');
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
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT nom_empleado.id_empleado, no_documento, CONCAT(nombre1, ' ',nombre2, ' ',apellido1, ' ', apellido2) AS nombre, cant_dias, val_liq_pv
            FROM
                nom_liq_prima_nav
            INNER JOIN nom_empleado 
                ON (nom_liq_prima_nav.id_empleado = nom_empleado.id_empleado)
            WHERE anio = '$anio' AND periodo = '$periodo'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
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
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTA DE EMPLEADOS LIQUIDACIÓN PRIMA DE NAVIDAD.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="table-responsive w-100">
                                <table id="dataTable" class="table table-striped table-bordered table-sm table-hover nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No. Doc.</th>
                                            <th class="text-center">Nombre Completo</th>
                                            <th class="text-center">Dias Liq.</th>
                                            <th class="text-center">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($obj as $o) { ?>
                                            <tr>
                                                <td><?php echo $o['no_documento'] ?></td>
                                                <td><?php echo mb_strtoupper($o['nombre']) ?></td>
                                                <td><?php echo $o['cant_dias'] ?></td>
                                                <td><?php echo pesos($o['val_liq_pv']) ?></td>
                                            </tr>
                                        <?php }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="center-block py-2">
                                <div class="form-group">
                                    <a type="button" class="btn btn-secondary" href="listempliquidar.php?mes=<?php echo $mes ?>"> Regresar</a>
                                    <a type="button" class="btn btn-secondary " href="../../inicio.php"> Cancelar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>