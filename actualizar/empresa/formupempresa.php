<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}

include '../../conexion.php';
include '../../permisos.php';
if ($id_rol != 1) {
    header('Location: ../../index.php');
    exit;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT
                `tb_datos_ips`.`id_ips`
                , `tb_datos_ips`.`razon_social_ips`
                , `tb_datos_ips`.`nit_ips`
                , `tb_datos_ips`.`dv`
                , `tb_datos_ips`.`direccion_ips`
                , `tb_datos_ips`.`telefono_ips`
                , `tb_datos_ips`.`email_ips`
                , `tb_datos_ips`.`idmcpio`
                , `tb_municipios`.`nom_municipio`
                , `tb_municipios`.`id_departamento`
                , `tb_departamentos`.`nom_departamento`
            FROM
                `tb_datos_ips`
                INNER JOIN `tb_municipios` 
                    ON (`tb_datos_ips`.`idmcpio` = `tb_municipios`.`id_municipio`)
                INNER JOIN `tb_departamentos` 
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`) LIMIT 1";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $idpto = $obj['id_departamento'];
    $idmun = $obj['idmcpio'];
    $sql = "SELECT * FROM `tb_departamentos` ORDER BY `nom_departamento`";
    $rs = $cmd->query($sql);
    $dpto = $rs->fetchAll();
    $sql = "SELECT * FROM `tb_municipios` WHERE `id_departamento` = $idpto ORDER BY `nom_municipio`";
    $rs = $cmd->query($sql);
    $municipio = $rs->fetchAll();
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
                            <i class="fas fa-city fa-lg" style="color: #07cf74"></i>
                            FORMULARIO DE ACTUALIZACION EMPRESA.
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formUpEmpresa">
                                <input type="number" name="idUpEmpresa" value="<?php echo $obj['id_ips'] ?>" hidden="true">
                                <div class="form-row">
                                    <div class="form-group col-md-8">
                                        <label for="txtNitEmpresa">NIT</label>
                                        <input type="text" class="form-control" id="txtNitEmpresa" name="txtNitEmpresa" value="<?php echo $obj['nit_ips'] ?>" placeholder="Identificación">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="txtUpNomEmpresa">Nombre</label>
                                        <input type="text" class="form-control" id="txtUpNomEmpresa" name="txtUpNomEmpresa" value="<?php echo $obj['razon_social_ips'] ?>" placeholder="Empresa">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="mailUpEmpresa">Correo eléctronico</label>
                                        <input type="email" class="form-control" id="mailUpEmpresa" name="mailUpEmpresa" value="<?php echo $obj['email_ips'] ?>" placeholder="correo@empresa.com">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="txtUpTel">Teléfono</label>
                                        <input type="text" class="form-control" id="txtUpTel" name="txtUpTel" value="<?php echo $obj['telefono_ips'] ?>">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="slcDptoEmp">Departamento</label>
                                        <select id="slcDptoEmp" name="slcDptoEmp" class="form-control py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($dpto as $d) {
                                                if ($idpto === $d['id_departamento']) {
                                                    echo '<option selected value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                                } else {
                                                    echo '<option value="' . $d['id_departamento'] . '">' . $d['nom_departamento'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="slcMunicipioEmp">Municipio</label>
                                        <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                            <?php
                                            foreach ($municipio as $m) {
                                                if ($idmun === $m['id_municipio']) {
                                                    echo '<option selected value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                                } else {
                                                    echo '<option value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="txtUpDireccion">Dirección</label>
                                        <input type="text" class="form-control" id="txtUpDireccion" name="txtUpDireccion" value="<?php echo $obj['direccion_ips'] ?>" placeholder="Usuario">
                                    </div>
                                </div>
                                <br>
                                <button class="btn btn-primary btn-sm" id="btnUpEmpresa"> Actualizar</button>
                                <a type="button" class="btn btn-secondary  btn-sm" href="<?php echo $_SESSION['urlin'] ?>/inicio.php"> Cancelar</a>
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
</body>

</html>