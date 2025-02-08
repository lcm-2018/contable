<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : exit('Acceso no disponible');
$id_pto = $_POST['id_pto'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_crp`.`id_manu`
                , `pto_crp`.`num_contrato`
                , `pto_crp`.`id_tercero_api`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `pto_crp`.`objeto`
                , DATE_FORMAT(`pto_crp`.`fecha`, '%Y-%m-%d') AS `fecha`
            FROM
                `pto_crp`
                INNER JOIN `tb_terceros` 
                    ON (`pto_crp`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE (`pto_crp`.`id_pto_crp` = $id_crp)";
    $rs = $cmd->query($sql);
    $crp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR CRP</h5>
        </div>
        <form id="formUpCRP">
            <input type="hidden" name="id_crp" value="<?php echo $id_crp ?>">
            <input type="hidden" name="id_pto" value="<?php echo $id_pto ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="id_manu" class="small">CONSECUTIVO CRP</label>
                    <input type="number" name="id_manu" id="id_manu" class="form-control form-control-sm" value="<?php echo $crp['id_manu'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="dateFecha" class="small">FECHA</label>
                    <input type="date" name="dateFecha" id="dateFecha" class="form-control form-control-sm" value="<?php echo $crp['fecha'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="txtContrato" class="small">NÚMERO CONTRATO</label>
                    <input type="text" name="txtContrato" id="txtContrato" class="form-control form-control-sm" value="<?php echo $crp['num_contrato'] ?>">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="tercerocrp" class="small">TERCERO</label>
                    <input type="text" id="tercerocrp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" value="<?php echo $crp['nom_tercero'] . ' -> ' . $crp['nit_tercero'] ?>">
                    <input type="hidden" id="id_tercero" name="id_tercero" value="<?php echo $crp['id_tercero_api'] ?>">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtObjeto" class="small">OBJETO</label>
                    <textarea id="txtObjeto" type="text" name="txtObjeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"><?php echo $crp['objeto'] ?></textarea>
                </div>
            </div>
            <div class="text-right pb-3 px-4">
                <button class="btn btn-success btn-sm" id="btnGestionCRP" text="2">Guardar</button>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>