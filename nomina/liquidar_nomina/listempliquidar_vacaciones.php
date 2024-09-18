<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
$vigencia = $_SESSION['vigencia'];

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
    $sql = "SELECT
                `nom_vacaciones`.`id_vac`
                , `nom_vacaciones`.`corte`
                , `nom_vacaciones`.`fec_inicial`
                , `nom_vacaciones`.`fec_fin`
                , `nom_vacaciones`.`dias_inactivo`
                , `nom_vacaciones`.`dias_habiles`
                , `nom_vacaciones`.`estado`
                , `nom_vacaciones`.`dias_liquidar`
                , `nom_empleado`.`id_empleado`
                , `nom_empleado`.`no_documento`
                , `nom_empleado`.`nombre1`
                , `nom_empleado`.`nombre2`
                , `nom_empleado`.`apellido1`
                , `nom_empleado`.`apellido2`
            FROM
                `nom_vacaciones`
                INNER JOIN `nom_empleado` 
                    ON (`nom_vacaciones`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE (`nom_vacaciones`.`estado` = 1)";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$carcater_empresa = $_SESSION['caracter'] == 2 ? $_SESSION['caracter'] : 1;
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
                                    <span class="fas fa-users fa-lg" style="color:#1D80F7"></span>
                                    LISTA DE EMPLEADOS A LIQUIDAR VACACIONES.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="">
                                <form id="formLiqVacs">
                                    <input type="hidden" id="caracter_empresa" value="<?php echo $carcater_empresa ?>">
                                    <table id="tableLiqVacs" class="table table-striped table-bordered table-sm nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center centro-vertical" rowspan="2"><br><input id="selectAll" type="checkbox" checked></th>
                                                <th class="text-center centro-vertical" rowspan="2">No. Doc.</th>
                                                <th class="text-center centro-vertical" rowspan="2">Nombre Completo</th>
                                                <th class="text-center centro-vertical" rowspan="2">Inicia</th>
                                                <th class="text-center centro-vertical" rowspan="2">Termina</th>
                                                <th class="text-center centro-vertical" colspan="3">Dias</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center centro-vertical">Háb.</th>
                                                <th class="text-center centro-vertical">Inact.</th>
                                                <th class="text-center centro-vertical">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($obj as $o) {
                                            ?>
                                                <tr id="filaempl">
                                                    <td>
                                                        <div class="center-block listado">
                                                            <input clase="setAll" type="checkbox" name="empleado[<?php echo $o['id_empleado'] ?>]" checked value="<?php echo $o['id_vac'] ?>">
                                                        </div>
                                                    </td>
                                                    <td><?php echo $o['no_documento'] ?></td>
                                                    <td><?php echo trim(mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2'] . ' ' . $o['nombre1'] . ' ' . $o['nombre2'])) ?></td>
                                                    <td><?php echo $o['fec_inicial'] ?></td>
                                                    <td><?php echo $o['fec_fin'] ?></td>
                                                    <td><?php echo $o['dias_habiles'] ?></td>
                                                    <td><?php echo $o['dias_inactivo'] ?></td>
                                                    <td><?php echo $o['dias_liquidar'] ?></td>
                                                </tr>
                                            <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                            <div class="center-block py-2">
                                <div class="form-group">
                                    <?php
                                    if (PermisosUsuario($permisos, 5106, 2) || $id_rol == 1) {
                                    ?>
                                        <button class="btn btn-info" id="btnLiqVacaciones">LIQUIDAR VACACIONES</button>
                                    <?php
                                    }
                                    ?>
                                    <a type="button" class="btn btn-secondary " href="../../inicio.php"> CANCELAR</a>
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