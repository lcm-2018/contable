<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>
<?php
include 'conexion.php';
$res = array();
$idUsuer = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT
                `id_vigencia`, `anio`
            FROM
                `tb_vigencias`";
    $rs = $cmd->query($sql);
    $vigencias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT
                `nit_ips` AS `nit`
                , `razon_social_ips` AS `nombre`
                , `caracter`
            FROM
                `tb_datos_ips`";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$_SESSION['caracter'] = $empresa['caracter'];
?>
<!DOCTYPE html>
<html lang="es">
<?php include 'head.php';
?>

<body class="sb-nav-fixed">
    <div id="divFondo" class="container-fluid">
        <div class="row justify-content-center align-items-center minh-100">
            <div class="center-block">
                <div class="card shadow-lg border-0 rounded-lg mt-5" style="width: 23rem;">
                    <div class="card div-gris">
                        <img src="<?php echo $_SESSION['urlin'] ?>/images/logoFinanciero.png" class="card-img-top" alt="Logo">
                    </div>
                    <div class="card-body">
                        <form id="formVigencia">
                            <label class="mb-1 lbl-mostrar px-1" for="slcEmpresa">EMPRESA</label>
                            <div class="input-group">
                                <select id="slcEmpresa" name="slcEmpresa" class="form-control py-2" aria-label="Default select example">
                                    <?php
                                    $_SESSION['nit_emp'] = $empresa['nit'];
                                    echo '<option value="1" selected>' . $empresa['nombre'] . '</option>';
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-landmark fa-lg" style="color: #16A085;"></span>
                                    </div>
                                </div>
                            </div>
                            <label class="mb-1 pt-4 lbl-mostrar px-1" for="slcVigencia">VIGENCIA</label>
                            <div class="input-group">
                                <select id="slcVigencia" name="slcVigencia" class="form-control py-2" aria-label="Default select example">
                                    <option selected value="0">--Elegir Vigencia--</option>
                                    <?php
                                    foreach ($vigencias as $v) {
                                        if ($v["anio"] >= '2023') {
                                            echo '<option value="' . $v["id_vigencia"] . '|' .  $v["anio"] . '">' . $v["anio"] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="far fa-calendar-alt fa-lg" style="color: #D35400;"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="justify-content-between mt-4 mb-0">
                                <center><button class="btn btn-primary" id="btnEntrar">Entrar</button></center>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center div-gris">
                        <div class="small">Bienvenid@</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include 'modales.php';
    include 'scripts.php'; ?>
</body>

</html>