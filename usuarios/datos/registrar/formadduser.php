<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../terceros/php/historialtercero/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if ($id_rol != 1) {
    exit('Usuario no autorizado');
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
                    <div class="form-group col-md-2">
                        <label for="sl_tipoDocumento" class="small">Tipo documento</label>
                        <select class="form-control form-control-sm" id="sl_tipoDocumento" name="sl_tipoDocumento">
                            <?php tipoDocumento($cmd, '', 4) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="small" for="txtCCuser">Número de documento</label>
                        <input type="number" class="form-control form-control-sm" id="txtCCuser" name="txtCCuser" placeholder="Identificación">
                    </div>
                    <div class="form-group col-md-2">
                        <label class="small" for="txtlogin">Login</label>
                        <input type="text" class="form-control form-control-sm" id="txtlogin" name="txtlogin" placeholder="Usuario">
                    </div>
                    <div class="form-group col-md-3 campo">
                        <label class="small" for="passuser">Contraseña</label>
                        <input type="password" class="form-control form-control-sm" id="passuser" name="passuser" placeholder="Contraseña">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_sexo" class="small">Sexo</label>
                        <select class="form-control form-control-sm" id="sl_sexo" name="sl_sexo">
                            <option value="M" selected>M</option>
                            <option value="F">F</option>
                        </select>
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
                        <label class="small" for="txt_direccion">Dirección</label>
                        <input type="text" class="form-control form-control-sm" id="txt_direccion" name="txt_direccion" placeholder="Direccion">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="txt_telefono">Teléfono</label>
                        <input type="text" class="form-control form-control-sm" id="txt_telefono" name="txt_telefono" placeholder="Teléfono">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="mailuser">Correo eléctronico</label>
                        <input type="email" class="form-control form-control-sm" id="mailuser" name="mailuser" placeholder="usuario@correo.com">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="slcRolUser">Rol</label>
                        <select class="form-control form-control-sm" id="slcRolUser" name="slcRolUser">
                            <?php roles($cmd, '', 0) ?>
                        </select>
                    </div>
                </div>
                <input type="number" name="numEstUser" value="1" hidden="true">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small" for="txt_cargo">Cargo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_cargo" name="txt_cargo" placeholder="Cargo">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small" for="sl_centroCosto">Centro de costo - Dependencia</label>
                        <select class="form-control form-control-sm" id="sl_centroCosto" name="sl_centroCosto">
                            <?php centros_costo($cmd, '', 0) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="small" for="sl_areaCentroCosto">Area</label>
                        <select class="form-control form-control-sm" id="sl_areaCentroCosto" name="sl_areaCentroCosto"></select>
                    </div>
                </div>
                <div class="form-row">
                    <label style="width:50%; font-size:80%">Sedes</label>
                    <label style="width:50%; font-size:80%">Bodegas</label>
                </div>
                <div class="form-row">
                    <!--Lista de sedes-->
                    <div style="width: 48%;">
                        <table id="tb_sedes" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                            <thead>
                                <tr class="text-center centro-vertical">
                                    <th>
                                        <label for="chk_sel_filtro_sedes">Sel.</label>
                                        <input type="checkbox" id="chk_sel_filtro_sedes">
                                    </th>
                                    <th>Id.</th>
                                    <th>Sede</th>
                                    <th>Dirección</th>
                                    <th>Teléfono</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div style="width: 4%;"></div>
                    <div style="width: 48%;">
                        <table id="tb_bodegas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                            <thead>
                                <tr class="text-center centro-vertical">
                                    <th>
                                        <label for="chk_sel_filtro_bodegas">Sel.</label>
                                        <input type="checkbox" id="chk_sel_filtro_bodegas">
                                    </th>
                                    <th>Id.</th>
                                    <th>Bodega</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                        </table>
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

<script type="text/javascript" src="js/funcionesusuario_reg.js?v=<?php echo date('YmdHis') ?>"></script>