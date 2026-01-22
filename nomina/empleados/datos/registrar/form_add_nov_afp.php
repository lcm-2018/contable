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
    $sql = "SELECT * FROM nom_afp ORDER BY nombre_afp ASC";
    $rs = $cmd->query($sql);
    $afp = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR NOVEDAD AFP</h5>
        </div>
        <form id="formAddAfpNovedad">
            <input type="number" id="idEmpNovAfp" name="idEmpNovAfp" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="slcAfpNovedad" class="small">AFP</label>
                    <select id="slcAfpNovedad" name="slcAfpNovedad" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar AFP--</option>
                        <?php
                        foreach ($afp as $a) {
                            echo '<option value="' . $a['id_afp'] . '">' . $a['nombre_afp'] . '</option>';
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
                    <button class="btn btn-primary btn-sm" id="btnAddNovedadAfp">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>