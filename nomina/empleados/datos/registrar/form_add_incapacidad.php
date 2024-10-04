<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}

include '../../../../conexion.php';
$idemp  = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
            FROM
                nom_incapacidad
            INNER JOIN nom_tipo_incapacidad 
                ON (nom_incapacidad.id_tipo = nom_tipo_incapacidad.id_tipo)
            WHERE id_empleado = '$idemp'
            ORDER BY fec_fin ASC";
    $rs = $cmd->query($sql);
    $listincap = $rs->fetchAll();
    $sql = "SELECT * FROM nom_tipo_incapacidad ORDER BY id_tipo";
    $rs = $cmd->query($sql);
    $tipincap = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR INCAPACIDAD</h5>
        </div>
        <form id="formAddIncapacidad">
            <input type="number" id="idEmpIncapacidad" name="idEmpIncapacidad" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-5">
                    <label class="small">Categoría</label>
                    <div class="form-control form-control-sm" id="categoria">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="categoria" id="categoria1" value="1">
                            <label class="form-check-label small" for="categoria1">Inicial</label>
                        </div>
                        <div class="form-check form-check-inline mr-0">
                            <input class="form-check-input" type="radio" name="categoria" id="categoria2" value="2">
                            <label class="form-check-label small" for="categoria2">Prórroga</label>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-5">
                    <label class="small">Tipo de Incapacidad</label>
                    <div class="form-control form-control-sm" id="slcTipIncapacidad">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="slcTipIncapacidad" id="slcTipIncapacidad1" value="1">
                            <label class="form-check-label small" for="slcTipIncapacidad1">COMÚN</label>
                        </div>
                        <div class="form-check form-check-inline mr-0">
                            <input class="form-check-input" type="radio" name="slcTipIncapacidad" id="slcTipIncapacidad3" value="3">
                            <label class="form-check-label small" for="slcTipIncapacidad3">LABORAL</label>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <label class="small">Días</label>
                    <div class="form-control form-control-sm" id="divCantDiasIncap">
                        2
                        <input type="number" id="numCantDiasIncap" name="numCantDiasIncap" value="2" hidden>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecInicioIncap">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioIncap" name="datFecInicioIncap" value="<?php echo date('Y-m-d') ?>">
                        <div id="edatFecInicioIncap" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datFecFinIncap">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinIncap" name="datFecFinIncap" value="<?php $fecha = date('Y-m-d');
                                                                                                                                    $tomo = strtotime("+ 1 day", strtotime($fecha));
                                                                                                                                    $mañana = date('Y-m-d', $tomo);
                                                                                                                                    echo $mañana;
                                                                                                                                    ?>">
                        <div id="edatFecFinIncap" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddIncapacidad">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>