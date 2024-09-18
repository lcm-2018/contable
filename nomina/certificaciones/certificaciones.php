<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
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
    $sql = "SELECT `id_cert`, `descripcion` FROM `nom_tipo_certificado` ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $tipo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
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
                                    <i class="fab fa-wpforms fa-lg" style="color:#1D80F7"></i>
                                    CERTIFICACIONES
                                </div>
                                <?php
                                if (PermisosUsuario($permisos, 5113, 2) || $id_rol == 1) {
                                    echo '<input type="hidden" id="peReg" value="1">';
                                } else {
                                    echo '<input type="hidden" id="peReg" value="0">';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="card-body text-center" id="divCuerpoPag">
                            <form id="formGenCertificado">
                                <div class="form-row mt-3">
                                    <div class="form-group col-md-2">
                                        <label for="slcTipoCertf" class="small">TIPO</label>
                                        <select class="form-control form-control-sm" id="slcTipoCertf" name="slcTipoCertf">
                                            <option value="0">--Seleccione--</option>
                                            <?php foreach ($tipo as $t) { ?>
                                                <option value="<?php echo $t['id_cert'] ?>"><?php echo $t['descripcion'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="txtBuscTercero" class="small">TERCERO</label>
                                        <input type="text" class="form-control form-control-sm" id="txtBuscTercero">
                                        <input type="hidden" id="noDocTercero" name="noDocTercero" value="0">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="fecInicia" class="small">Inicia</label>
                                        <input type="date" class="form-control form-control-sm" id="fecInicia" name="fecInicia">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="fecFin" class="small">Termina</label>
                                        <input type="date" class="form-control form-control-sm" id="fecFin" name="fecFin">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <?php
                                        if (PermisosUsuario($permisos, 5113, 2) || $id_rol == 1) {
                                        ?>
                                            <label for="btnGenCertificado" class="small">&nbsp;</label>
                                            <div class="form-control form-control-sm border-0 p-0">
                                                <button class="btn btn-outline-info btn-sm w-100" id="btnGenCertificado">
                                                    <i class="fas fa-atom fa-xs"></i> Generar
                                                </button>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6 text-left" id="divListContratos">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
        <?php include '../../modales.php' ?>
    </div>
    <?php include '../../scripts.php' ?>
    <script type="text/javascript" src="<?php echo $_SESSION['urlin'] ?>/nomina/certificaciones/js/funcionescertifica.js"></script>
</body>

</html>