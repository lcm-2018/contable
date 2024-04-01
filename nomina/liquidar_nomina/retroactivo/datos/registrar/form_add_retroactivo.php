<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `porcentaje`
                , `id_inc`
                , `porcentaje`
                , `vigencia`
                , `fec_reg`
            FROM
                `nom_incremento_salario`
            WHERE (`vigencia` = '$vigencia')";
    $rs = $cmd->query($sql);
    $incrementos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR RETROACTIVOS</h5>
        </div>
        <div class="px-2">
            <form id="formAddRetroactivo">
                <div class="form-row text-center">
                    <div class="form-group col-md-6">
                        <label for="fecIniciaRetroactivo" class="small">Fecha Incial</label>
                        <input id="fecIniciaRetroactivo" name="fecIniciaRetroactivo" type="date" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecTerminaRetroactivo" class="small">Fecha Final</label>
                        <input id="fecTerminaRetroactivo" name="fecTerminaRetroactivo" type="date" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-6">
                        <label for="numMesesRetroactivo" class="small"># meses</label>
                        <input type="number" id="numMesesRetroactivo" name="numMesesRetroactivo" class="form-control form-control-sm" min="1" max="12">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="numPorcentajeRetro" class="small">% incremento</label>
                        <select id="numPorcentajeRetro" name="numPorcentajeRetro" class="form-control form-control-sm">
                            <option>--Seleccionar--</option>
                            <?php
                            foreach ($incrementos as $inc) {
                                echo '<option value=' . $inc['id_inc'] . '>' .$inc['fec_reg'].' => '. $inc['porcentaje'] . '%</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtaObservaRetroActivo" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtaObservaRetroActivo" name="txtaObservaRetroActivo" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnAddRetroactivo" type="button" class="btn btn-primary btn-sm">Registrar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>