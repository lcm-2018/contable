<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$res = '';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
            FROM
                nom_incapacidad
            WHERE id_incapacidad = '$id'";
    $rs = $cmd->query($sql);
    $incapacidad = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_tipo_incapacidad";
    $rs = $cmd->query($sql);
    $tipoincap = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR INCAPACIDAD</h5>
        </div>
        <form id="formUpIncapacidad">
            <input type="number" name="numidIncapacidad" value="<?php echo $incapacidad['id_incapacidad'] ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-5">
                    <label class="small">Categoría</label>
                    <div class="form-control form-control-sm" id="categoria">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="categoria" id="categoria1" value="1" <?php echo $incapacidad['categoria'] == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="categoria1">Inicial</label>
                        </div>
                        <div class="form-check form-check-inline mr-0">
                            <input class="form-check-input" type="radio" name="categoria" id="categoria2" value="2" <?php echo $incapacidad['categoria'] == 2 ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="categoria2">Prórroga</label>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-5">
                    <label class="small">Tipo de Incapacidad</label>
                    <div class="form-control form-control-sm" id="slcTipIncapacidad">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="slcTipIncapacidad" id="slcTipIncapacidad1" value="1" <?php echo $incapacidad['id_tipo'] == 1 ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="slcTipIncapacidad1">COMÚN</label>
                        </div>
                        <div class="form-check form-check-inline mr-0">
                            <input class="form-check-input" type="radio" name="slcTipIncapacidad" id="slcTipIncapacidad3" value="3" <?php echo $incapacidad['id_tipo'] == 3 ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="slcTipIncapacidad3">LABORAL</label>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <label class="small">Días</label>
                    <div class="form-control form-control-sm" id="divUpCantDiasIncap">
                        <?php echo  $incapacidad['can_dias'] ?>
                        <input type="number" id="numUpCantDiasIncap" name="numUpCantDiasIncap" value="<?php echo  $incapacidad['can_dias'] ?>" hidden>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datUpFecInicioIncap">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecInicioIncap" name="datUpFecInicioIncap" value="<?php echo  $incapacidad['fec_inicio'] ?>">
                        <div id="edatUpFecInicioIncap" class="invalid-tooltip">
                            Inicio debe ser menor
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datUpFecFinIncap">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecFinIncap" name="datUpFecFinIncap" value="<?php echo  $incapacidad['fec_fin'] ?>">
                        <div id="edatUpFecFinIncap" class="invalid-tooltip">
                            Fin debe ser mayor
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarIncap">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>