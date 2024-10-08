<?php
session_start();
header("Pragma: no-cache");
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
$id_pto_mod = $_POST['id_mod'];
$id_vigencia = $_SESSION['id_vigencia'];
// Consulto los datos generales del nuevo registro presupuesal
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto_mod`, `fecha`, `id_manu`, `objeto`, `id_tipo_mod`, `id_pto` FROM `pto_mod` WHERE `id_pto_mod` = $id_pto_mod";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


try {
    $sql = "SELECT
                `pto_mod`.`id_pto_mod`
                , `pto_mod_detalle`.`id_pto_mod`
                , SUM(`pto_mod_detalle`.`valor_deb`) AS `debito`
                , SUM(`pto_mod_detalle`.`valor_cred`) AS `credito`
            FROM
                `pto_mod`
                LEFT JOIN `pto_mod_detalle` 
                    ON (`pto_mod`.`id_pto_mod` = `pto_mod_detalle`.`id_pto_mod`)
            WHERE (`pto_mod`.`id_pto_mod` = $id_pto_mod AND `pto_mod`.`estado` >= 1)
            GROUP BY `pto_mod_detalle`.`id_pto_mod`";
    $rs = $cmd->query($sql);
    $valores = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$dif = !empty($valores) ? ($valores['debito'] - $valores['credito']) : 0;
$dif = abs($dif);
$fecha = date('Y-m-d', strtotime($datos['fecha']));
$consulta = $sql;
?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : '' ?>">

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
                                    DETALLE DOCUMENTO DE MODIFICACION PRESUPUESTAL
                                </div>

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div class="right-block">
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">NUMERO:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datos['id_manu']; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">FECHA:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $fecha; ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-2">
                                            <div class="col"><label for="fecha" class="small">OBJETO:</label></div>
                                        </div>
                                        <div class="col-10"><?php echo $datos['objeto']; ?></div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            <?php if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
                                echo  '<input type="hidden" id="peReg" value="1">';
                            } else {
                                echo  '<input type="hidden" id="peReg" value="0">';
                            }
                            ?>
                            <input type="hidden" id="id_pto_movto" name="id_pto_movto" value="<?php echo $_POST['id_pto']; ?>">
                            <input type="hidden" id="id_pto_mod" name="id_pto_mod" value="<?php echo $id_pto_mod; ?>">
                            <form id="formAddModDetalle">
                                <table id="tableModDetalle" class="table table-striped table-bordered table-sm table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">ID</th>
                                            <th style="width: 50%;">Codigo</th>
                                            <th style="width: 15%;" Class="text-center">Débito</th>
                                            <th style="width: 15%;" Class="text-center">Crédito</th>
                                            <th style="width: 12%;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarModDetalle">
                                    </tbody>
                                </table>
                            </form>
                            <div class="text-center pt-4">
                                <a onclick="terminarDetalleMod('<?php echo $datos['id_tipo_mod']; ?>')" class="btn btn-danger" style="width: 7rem;" href="#"> TERMINAR</a>

                            </div>
                        </div>

                    </div>
                </div>
                <div>

                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <?php include '../modales.php' ?>
    </div>


    <?php include '../scripts.php' ?>
</body>

</html>