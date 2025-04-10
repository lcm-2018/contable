<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
include '../../../conexion.php';
include '../../../permisos.php';


if ($id_rol != 1) {
    exit('Usuario no autorizado');
}
$id_perfirl = isset($_POST['id']) ? $_POST['id'] : exit('Acceso denegado');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_rol`, `nom_rol`
            FROM
                `seg_rol`
            WHERE `id_rol` = $id_perfirl";
    $rs = $cmd->query($sql);
    $rol = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">MODIFICAR PERFÍL DE USUARIO</h5>
        </div>
        <div class="px-4">
            <form id="formAddPerfil">
                <input type="hidden" id="id_perfil" value="<?php echo $id_perfirl ?>">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label class="small" for="txtPerfil">nombre de perfíl</label>
                        <input type="text" class="form-control form-control-sm" id="txtPerfil" name="txtPerfil" value="<?php echo $rol['nom_rol'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnUpPerfil" type="button" class="btn btn-primary btn-sm">Actualizar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>