<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
$idemp  = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM `nom_fondo_censan` ORDER BY nombre_fc ASC";
    $rs = $cmd->query($sql);
    $fc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR NOVEDAD FONDO DE CESANTIAS</h5>
        </div>
        <form id="formAddFCNovedad">
            <input type="number" id="idEmpNovAfp" name="idEmpNovAfp" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="slcAfpNovedad" class="small">Fondo cesantias</label>
                    <select id="slcAfpNovedad" name="slcAfpNovedad" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar Fondo--</option>
                        <?php
                        foreach ($fc as $f) {
                            echo '<option value="' . $f['id_fc'] . '">' . $f['nombre_fc'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="datFecAfilAfpNovedad" class="small">Afilición</label>
                    <input type="date" class="form-control form-control-sm" id="datFecAfilAfpNovedad" name="datFecAfilAfpNovedad" value="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="datFecRetAfpNovedad" class="small">Retiro</label>
                    <input type="date" class="form-control form-control-sm" id="datFecRetAfpNovedad" name="datFecRetAfpNovedad" value="<?php echo date('Y') ?>-12-31">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddNovedadFc">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>