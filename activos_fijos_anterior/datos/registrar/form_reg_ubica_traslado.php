<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_serie = isset($_POST['id_ser']) ? $_POST['id_ser'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_metodo`,`descripcion` FROM `seg_metodo_deprecia`";
    $rs = $cmd->query($sql);
    $metodo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `nombre` FROM `tb_sedes`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_estado`, `descripcion` FROM `nom_estado_acfijo`";
    $rs = $cmd->query($sql);
    $estado = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR UBICACIÓN O TRASLADO DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="formRegUbicaTRasladAcFijo">
                <input name="id_serie_acfijo" hidden value="<?php echo $id_serie ?>">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="slcSedeUbTr" class="small">SEDE</label>
                        <select name="slcSedeUbTr" id="slcSedeUbTr" class="form-control form-control-sm">
                            <option value="0">--Seleccione--</option>
                            <?php foreach ($sedes as $s) { ?>
                                <option value="<?php echo $s['id_sede'] ?>"><?php echo $s['nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="slcCentroCosto" class="small">CENTRO DE COSTO</label>
                        <select name="slcCentroCosto" id="slcCentroCosto" class="form-control form-control-sm">
                            <option value="0">--Seleccionar sede--</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fecUbicTrasl" class="small">Fecha</label>
                        <input type="date" id="fecUbicTrasl" name="fecUbicTrasl" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="slcEstadoAcFijo" class="small">estado</label>
                        <select name="slcEstadoAcFijo" id="slcEstadoAcFijo" class="form-control form-control-sm">
                            <option value="0">--Seleccionar--</option>
                            <?php foreach ($estado as $e) { ?>
                                <option value="<?php echo $e['id_estado'] ?>"><?php echo $e['descripcion'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="txtBuscaTerceroResp" class="small">Tercero Responsable</label>
                        <input type="text" id="txtBuscaTerceroResp" class="form-control form-control-sm">
                        <input type="hidden" id="numTercerResp" name="numTercerResp" value="0">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaUbicaTraslado" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaUbicaTraslado" name="txtObservaUbicaTraslado" rows="3"></textarea>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnRegUbicaTrasladoActFijo" type="button" class="btn btn-primary btn-sm">Registrar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>