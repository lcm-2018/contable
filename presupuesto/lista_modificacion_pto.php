<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
// consulto id_pto_tipo de la tabla pto_presupuestos cuando id_pto_presupuestos es igual a $id_pto_presupuestos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tipo`, `nombre` FROM `pto_presupuestos` WHERE `id_pto` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $presupuesto = $rs->fetch();
    $id_tipo = $presupuesto['id_tipo'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$buscar = $id_tipo == 1 ? 1 : 2;
$tipo_dato = isset($_POST['tipo_mod']) ? $_POST['tipo_mod'] : "0";

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // consulta select tipo de recursos
    $sql = "SELECT `id_tmvto`,`codigo`,`nombre` FROM `pto_tipo_mvto` WHERE (`filtro` = $buscar OR `filtro` = 0)ORDER BY `nombre`";
    $rs = $cmd->query($sql);
    $tipoMod = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>

<body class="sb-nav-fixed <?php echo $_SESSION['navarlat'] === '1' ? 'sb-sidenav-toggled' : ''; ?>">
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
                                    MODIFICACIONES A <?php echo strtoupper($presupuesto['nombre'])  ?>
                                </div>
                                <input type="hidden" id="id_pto_ppto" value="<?php echo $id_pto_presupuestos ?>">
                                <input type="hidden" id="id_mov" value="<?php echo $tipo_dato ?>">
                                <?php if (PermisosUsuario($permisos, 5401, 2) || $id_rol == 1) {
                                    echo  '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo  '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>


                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div clas="row">
                                    <div class="center-block">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">

                                                    <select class="custom-select custom-select-sm" id="id_pto_doc" name="id_pto_doc" onchange="cambiaListadoModifica(value)">
                                                        <option value="0">-- Seleccionar --</option>
                                                        <?php
                                                        foreach ($tipoMod as $mov) {
                                                            if ($mov['id_tmvto'] == $tipo_dato) {
                                                                echo '<option value="' . $mov['id_tmvto'] . '" selected>' . $mov['nombre'] . '</option>';
                                                            } else {
                                                                echo '<option value="' . $mov['id_tmvto'] . '" >' . $mov['nombre'] . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <?php
                                if ($tipo_dato != 0) {
                                ?>
                                    <table id="tableModPresupuesto" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Número</th>
                                                <th>Fecha</th>
                                                <th>Documento</th>
                                                <th>Acto admin</th>
                                                <th>Valor</th>
                                                <th>Acciones</th>

                                            </tr>
                                        </thead>
                                        <tbody id="modificarModPresupuesto">
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Número</th>
                                                <th>Fecha</th>
                                                <th>Documento</th>
                                                <th>Acto admin</th>
                                                <th>Valor</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                <?php } ?>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-danger" style="width: 7rem;" href="lista_presupuestos.php"> VOLVER</a>
                            </div>
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