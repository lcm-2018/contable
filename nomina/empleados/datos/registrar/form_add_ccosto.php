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
    $sql = "SELECT `id_centro`, `nom_centro` FROM `tb_centrocostos` ORDER BY `nom_centro` ASC";
    $rs = $cmd->query($sql);
    $ccosto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTION NOVEDAD CENTROS DE COSTO.</h5>
        </div>
        <form id="formNovCCosto">
            <input type="number" id="idEmp" name="idEmp" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="slcCcostoEmpl" class="small">CENTRO DE COSTO</label>
                    <select id="slcCcostoEmpl" name="slcCcostoEmpl" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar--</option>
                        <?php
                        foreach ($ccosto as $cc) {
                            echo '<option value="' . $cc['id_centro'] . '">' . $cc['nom_centro'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddCCostoEmp" text="1">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>