<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../financiero/php/historialtercero/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if ($id_rol != 1) {
    exit('Usuario no autorizado');
}
try {

    $sql = "SELECT
                `id_rol`, `nom_rol` AS `nombre`
            FROM
                `seg_rol`
            ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $roles = $rs->fetchAll(PDO::FETCH_ASSOC);
    //$cmd = null;  /// este select colocarlo en comunes como drilocoquito, es para los roles de los usuarios
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR USUARIO DEL SISTEMA</h5>
        </div>
        <div class="px-4">
            <form id="formAddUser">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="sl_tipoDocumento" class="small">Tipo documento</label>
                        <select class="form-control form-control-sm" id="sl_tipoDocumento" name="sl_tipoDocumento">
                            <?php tipoDocumento($cmd, '', 0) ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label class="small" for="txtCCuser">Número de documento</label>
                        <input type="number" class="form-control form-control-sm" id="txtCCuser" name="txtCCuser" placeholder="Identificación">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small" for="txtNomb1user">Primer nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txtNomb1user" name="txtNomb1user" placeholder="Nombre">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="txtNomb2user">Segundo nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txtNomb2user" name="txtNomb2user" placeholder="Nombre">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="txtApe1user">Primer apellido</label>
                        <input type="text" class="form-control form-control-sm" id="txtApe1user" name="txtApe1user" placeholder="Apellido">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="txtApe2user">Segundo apellido</label>
                        <input type="text" class="form-control form-control-sm" id="txtApe2user" name="txtApe2user" placeholder="Apellido">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small" for="txtlogin">Login</label>
                        <input type="text" class="form-control form-control-sm" id="txtlogin" name="txtlogin" placeholder="Usuario">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="mailuser">Correo eléctronico</label>
                        <input type="email" class="form-control form-control-sm" id="mailuser" name="mailuser" placeholder="usuario@correo.com">
                    </div>
                    <div class="form-group col-md-6 campo">
                        <label class="small" for="passuser">Contraseña</label>
                        <input type="password" class="form-control form-control-sm" id="passuser" name="passuser" placeholder="Contraseña">
                    </div>
                </div>
                <input type="number" name="numEstUser" value="1" hidden="true">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small" for="slcRolUser">Rol</label>
                        <select class="form-control form-control-sm" id="slcRolUser" name="slcRolUser">
                            <option value="0" selected>--Seleccionar--</option>
                            <?php foreach ($roles as $rol) { ?>
                                <option value="<?php echo $rol['id_rol'] ?>"><?php echo $rol['nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnAddUser" type="button" class="btn btn-primary btn-sm">Registrar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>