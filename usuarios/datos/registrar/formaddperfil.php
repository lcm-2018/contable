<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
if ($_SESSION['id_user'] != 1) {
    exit('Usuario no autorizado');
}
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_rol`, `nom_rol` AS `nombre`
            FROM
                `seg_rol`";
    $rs = $cmd->query($sql);
    $roles = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR PERFÍL DE USUARIO</h5>
        </div>
        <div class="px-4">
            <form id="formAddPerfil">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label class="small" for="txtPerfil">nombre de perfíl</label>
                        <input type="text" class="form-control form-control-sm" id="txtPerfil" name="txtPerfil">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnAddPerfil" type="button" class="btn btn-primary btn-sm">Registrar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>