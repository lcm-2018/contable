<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
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
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 0;
if ($tipo == 0) {
    exit('Imposible cargar la página, el tipo de liquidación no es válido');
} else if ($tipo == 1) {
    $titulo = 'SERVICIOS';
} else if ($tipo == 2) {
    $titulo = 'NAVIDAD';
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`
                , `no_documento`
                , `apellido1`
                , `apellido2`
                , `nombre2`
                , `nombre1`
                , `estado`
            FROM `nom_empleado`
            WHERE `estado` = 1";
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
                                    LISTA DE EMPLEADOS A LIQUIDAR PRIMA DE <?php echo $titulo . ', AÑO ' . $vigencia ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="">
                                <form id="formLiqNomina">
                                    <input type="hidden" id="tipo" value="<?php echo $tipo ?>">
                                    <input type="hidden" id="caracter_empresa" value="<?php echo $carcater_empresa ?>">
                                    <table id="tableLiqPrimaSv" class="table table-striped table-bordered table-sm nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center centro-vertical"> Todos <br><input id="selectAll" type="checkbox" checked></th>
                                                <th class="text-center centro-vertical">No. Doc.</th>
                                                <th class="text-center centro-vertical">Nombre Completo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <div></div>
                                            <?php
                                            foreach ($obj as $o) {
                                            ?>
                                                <tr id="filaempl">
                                                    <td>
                                                        <div class="center-block listado">
                                                            <input clase="setAll" type="checkbox" name="empleado[]" checked value="<?php echo $o['id_empleado'] ?>">
                                                        </div>
                                                    </td>
                                                    <td><?php echo $o['no_documento'] ?></td>
                                                    <td><?php echo mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2'] . ' ' . $o['nombre1'] . ' ' . $o['nombre2']) ?></td>
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
                                    if (PermisosUsuario($permisos, 5108, 2) || PermisosUsuario($permisos, 5109, 2) || $id_rol == 1) {
                                    ?>
                                        <button class="btn btn-info" id="btnLiqPrima">LIQUIDAR PRIMA DE <?php echo $titulo ?></button>
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