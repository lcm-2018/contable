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
    $sql = "SELECT `id_tipo_act`, `descripcion` FROM `seg_tipo_activo`";
    $rs = $cmd->query($sql);
    $tipo_activo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">COMPONENTE DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active small" id="nav-regAcFijo-tab" data-toggle="tab" href="#nav-regAcFijo" role="tab" aria-controls="nav-regAcFijo" aria-selected="true">Registrar</a>
                    <a class="nav-item nav-link small" id="nav-agregAcFijo-tab" data-toggle="tab" href="#nav-agregAcFijo" role="tab" aria-controls="nav-agregAcFijo" aria-selected="false">Agregar</a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-regAcFijo" role="tabpanel" aria-labelledby="nav-regAcFijo-tab">
                    <form id="formRegComponenteAcFijo">
                        <input name="id_serie_acfijo" hidden value="<?php echo $id_serie ?>">
                        <div class=" form-row">
                            <div class="form-group col-md-12">
                                <label for="nom_prod" class="small">Buscar Activo Fijo</label>
                                <input type="text" id="busc_acfijo" class="form-control form-control-sm">
                                <input type="hidden" id="id_acfijo" name="id_acfijo" value="0">
                            </div>
                        </div>
                        <div class="form-row">
                            <input type="hidden" id="cantidad" name="cantidad" value="1">
                            <div class="form-group col-md-4">
                                <label for="mantenimiento" class="small">Mantenimiento</label>
                                <select type="text" id="mantenimiento" name="mantenimiento" class="form-control form-control-sm">
                                    <option value="0">--Seleccionar--</option>
                                    <option value="1">SI</option>
                                    <option value="2">NO</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="slcDepresiacion" class="small">Depreciable</label>
                                <select type="date" id="slcDepresiacion" name="slcDepresiacion" class="form-control form-control-sm">
                                    <option value="0">--Seleccionar--</option>
                                    <option value="1">SI</option>
                                    <option value="2">NO</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="numValUnita" class="small">Valor Unitario</label>
                                <input type="number" id="numValUnita" name="numValUnita" class="form-control form-control-sm" min="0">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="txtMarca" class="small">Marca</label>
                                <input type="text" id="txtMarca" name="txtMarca" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtModelo" class="small">Modelo</label>
                                <input id="txtModelo" name="txtModelo" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="slcTipoActivo" class="small">Tipo de Activo</label>
                                <select type="text" id="slcTipoActivo" name="slcTipoActivo" class="form-control form-control-sm">
                                    <option value="0">--Seleccionar--</option>
                                    <?php
                                    foreach ($tipo_activo as $tipo) {
                                    ?>
                                        <option value="<?php echo $tipo['id_tipo_act'] ?>"><?php echo $tipo['descripcion'] ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="txtNoSerie" class="small"># Serie</label>
                                <div class="input-group mb-1">
                                    <input type="text" id="txtNoSerie" name="txtNoSerie" class="form-control form-control-sm">
                                    <input type="hidden" id="txtSeriales" name="txtSeriales" value="0">
                                    <div class="input-group-append">
                                        <button id="btnMasSerie" class="btn btn-sm btn-success" title="Agregar un No. de Serie"><span class="fas fa-arrow-alt-circle-right fa-lg"></span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row px-2">
                            <div id="divSeriales" class="rounded border border-light w-100 text-left form-row mb-2 pt-1" style="background-color: #16a08533;">
                            </div>
                        </div>
                        <div class="form-row text-center">
                            <div class="form-group col-md-12">
                                <label for="txtObservaActFijo" class="small">Observaciones</label>
                                <textarea class="form-control" id="txtObservaActFijo" name="txtObservaActFijo" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                    <div class="text-center pt-1 pb-3">
                        <button id="btnRegComponenteActFijo" type="button" class="btn btn-primary btn-sm">Registrar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                    </div>
                </div>
                <div class="tab-pane fade" id="nav-agregAcFijo" role="tabpanel" aria-labelledby="nav-agregAcFijo-tab">
                    <form id="formBuscActFijo">
                        <div class="form-row text-center">
                            <div class="form-group col-md-3">
                                <label for="tipBusq" class="small">Tipo de búsqueda</label>
                                <select id="tipBusq" name="tipBusq" class="form-control form-control-sm">
                                    <option value="0">--Seleccionar--</option>
                                    <option value="1">PLACA</option>
                                    <option value="2">No. SERIAL</option>
                                </select>
                            </div>
                            <div class="form-group col-md-9">
                                <label for="busAcFijoXSerPla" class="small">Buscar activo fijo</label>
                                <input id="busAcFijoXSerPla" class="form-control form-control-sm">
                                <input type="hidden" id="id_serpla" name="id_serpla" value="0">
                            </div>
                        </div>
                    </form>
                    <div class="text-center pt-1 pb-3">
                        <button id="btnAddComponenteAcFijo" type="button" class="btn btn-primary btn-sm">Agregar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>