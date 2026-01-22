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
    $sql = "SELECT * FROM nom_riesgos_laboral";
    $rs = $cmd->query($sql);
    $rlaboral = $rs->fetchAll();
    $sql = "SELECT * FROM nom_arl ORDER BY nombre_arl ASC";
    $rs = $cmd->query($sql);
    $arlnov = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR NOVEDAD ARL</h5>
        </div>
        <form id="formAddArlNovedad">
            <input type="number" id="idEmpNovArl" name="idEmpNovArl" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-5">
                    <label for="slcArlNovedad" class="small">ARL</label>
                    <select id="slcArlNovedad" name="slcArlNovedad" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar ARL--</option>
                        <?php
                        foreach ($arlnov as $a) {
                            echo '<option value="' . $a['id_arl'] . '">' . $a['nombre_arl'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="slcRiesLabNov" class="small">Riesgo laboral</label>
                    <select id="slcRiesLabNov" name="slcRiesLabNov" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar clase--</option>
                        <?php
                        foreach ($rlaboral as $r) {
                            echo '<option value="' . $r['id_rlab'] . '">' . $r['clase'] . ' - ' . $r['riesgo'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="datFecAfilArlNovedad" class="small">Afilición</label>
                    <input type="date" class="form-control form-control-sm" id="datFecAfilArlNovedad" name="datFecAfilArlNovedad" value="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="form-group col-md-2">
                    <label for="datFecRetArlNovedad" class="small">Retiro</label>
                    <input type="date" class="form-control form-control-sm" id="datFecRetArlNovedad" name="datFecRetArlNovedad" value="<?php echo date('Y') ?>-12-31">

                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddNovedadArl">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>