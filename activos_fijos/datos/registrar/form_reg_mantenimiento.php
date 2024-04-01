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
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR MANTENIMIENTO DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="formRegMantenimientoAcFijo">
                <input name="id_serie_acfijo" hidden value="<?php echo $id_serie ?>">
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="txtNoOrdenMmto" class="small"># orden</label>
                        <input type="text" name="txtNoOrdenMmto" id="txtNoOrdenMmto" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fecIniciaMmto" class="small">Fecha de Ingreso</label>
                        <input type="date" name="fecIniciaMmto" id="fecIniciaMmto" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fecFinMmto" class="small">Fecha de retorno</label>
                        <input type="date" name="fecFinMmto" id="fecFinMmto" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="slcTipoMmto" class="small">Tipo de mantenimiento</label>
                        <select id="slcTipoMmto" name="slcTipoMmto" class="form-control form-control-sm">
                            <option value="0">--Seleccionar--</option>
                            <option value="1">PREVENTIVO</option>
                            <option value="2">CORRECTIVO</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txtConcptoMmto" class="small">concepto</label>
                        <input type="text" id="txtConcptoMmto" name="txtConcptoMmto" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numValDeterioro" class="small">Valor deterioro</label>
                        <input type="number" id="numValDeterioro" name="numValDeterioro" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaMmto" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaMmto" name="txtObservaMmto" rows="3"></textarea>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnRegMmtoActFijo" type="button" class="btn btn-primary btn-sm">Registrar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>