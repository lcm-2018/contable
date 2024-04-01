<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
include '../../../conexion.php';
$idUser = isset($_POST['id']) ? $_POST['id'] : exit('Acceso denegado');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM seg_usuarios_sistema WHERE id_usuario = '$idUser'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_rol`, `nom_rol` AS `nombre`
            FROM
                `seg_rol`
            ORDER BY `nombre`";
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
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR USUARIO DEL SISTEMA</h5>
        </div>
        <div class="px-4">
            <form id="formAddUser">
                <input type="hidden" name="idUpUser" value="<?php echo $idUser; ?>">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label class="small" for="txtccUpUser">Número de documento</label>
                        <input type="text" class="form-control form-control-sm" id="txtccUpUser" name="txtccUpUser" value="<?php echo $obj['num_documento'] ?>" placeholder="Identificación">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small" for="txtNomb1Upuser">Primer nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txtNomb1Upuser" name="txtNomb1Upuser" value="<?php echo $obj['nombre1'] ?>" placeholder="Nombre">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="txtNomb2Upuser">Segundo nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txtNomb2Upuser" name="txtNomb2Upuser" value="<?php echo $obj['nombre2'] ?>" placeholder="Nombre">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="txtApe1Upuser">Primer apellido</label>
                        <input type="text" class="form-control form-control-sm" id="txtApe1Upuser" name="txtApe1Upuser" value="<?php echo $obj['apellido1'] ?>" placeholder="Apellido">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="txtApe2user">Segundo apellido</label>
                        <input type="text" class="form-control form-control-sm" id="txtApe2user" name="txtApe2Upuser" value="<?php echo $obj['apellido2'] ?>" placeholder="Apellido">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small" for="txtUplogin">Login</label>
                        <input type="text" class="form-control form-control-sm" id="txtUplogin" name="txtUplogin" value="<?php echo $obj['login'] ?>" placeholder="Usuario">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="mailUpuser">Correo eléctronico</label>
                        <input type="email" class="form-control form-control-sm" id="mailUpuser" name="mailUpuser" value="<?php echo $obj['email'] ?>" placeholder="usuario@correo.com">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="small" for="passUpuser">Contraseña</label>
                        <input type="hidden" id="passAnterior" name="passAnterior" value="<?php echo $obj['clave'] ?>">
                        <input type="password" class="form-control form-control-sm" id="passUpuser" name="passUpuser" value="<?php echo $obj['clave'] ?>" placeholder="password">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small" for="slcRolUser">Rol</label>
                        <select class="form-control form-control-sm" id="slcRolUser" name="slcRolUpUser">
                            <?php foreach ($roles as $rol) {
                                $slc = $rol['id_rol'] == $obj['id_rol'] ? 'selected' : '';
                                echo "<option value='$rol[id_rol]' $slc>$rol[nombre]</option>";
                            } ?>
                        </select>
                        <input type="hidden" name="rol_anterior" value="<?php echo $obj['id_rol'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnUpUser" type="button" class="btn btn-primary btn-sm">Actualizar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>