<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : exit('Acceso no disponible');
$id_pto = $_POST['id_pto'];
$vigencia = $_SESSION['vigencia'];
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
                , `pto_crp`.`tesoreria`
                , `ctt_adquisiciones`.`id_adquisicion`
                , `ctt_contratos`.`fec_ini`
                , DATE_FORMAT(`pto_cdp`.`fecha`, '%Y-%m-%d') AS `fec_cdp`
            FROM
                `pto_crp`
                INNER JOIN `tb_terceros` 
                    ON (`pto_crp`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `pto_cdp` 
                    ON (`pto_crp`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                LEFT JOIN `ctt_adquisiciones` 
                    ON (`pto_crp`.`id_cdp` = `ctt_adquisiciones`.`id_cdp`)
                LEFT JOIN `ctt_contratos` 
                    ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
            WHERE (`pto_crp`.`id_pto_crp` = $id_crp)";
    $rs = $cmd->query($sql);
    $crp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fec_contrato = $crp['fec_ini'] == '' ? '1900-01-01' : date('Y-m-d', strtotime($crp['fec_ini']));
$min = $crp['fec_cdp'] >= $fec_contrato ? $crp['fec_cdp'] : $fec_contrato;
$max = $vigencia . '-12-31';
$id_adq = $crp['id_adquisicion'] != '' ? $crp['id_adquisicion'] : 0;
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR CRP</h5>
        </div>
        <form id="formUpCRP">
            <input type="hidden" name="id_crp" value="<?php echo $id_crp ?>">
            <input type="hidden" name="id_pto" value="<?php echo $id_pto ?>">
            <input type="hidden" name="id_adq" value="<?php echo $id_adq ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-3">
                    <label for="id_manu" class="small">CONSECUTIVO</label>
                    <input type="number" name="id_manu" id="id_manu" class="form-control form-control-sm" value="<?php echo $crp['id_manu'] ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="dateFecha" class="small">FECHA</label>
                    <input type="date" name="dateFecha" id="dateFecha" class="form-control form-control-sm" value="<?php echo $crp['fecha'] ?>" min="<?php echo $min ?>" max="<?php echo $max ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="txtContrato" class="small">NÚM. CONTRATO</label>
                    <input type="text" name="txtContrato" id="txtContrato" class="form-control form-control-sm" value="<?php echo $crp['num_contrato'] ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="txtContrato" class="small">&nbsp;</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <input type="checkbox" id="chkTesoreria" name="chkTesoreria" <?php echo $crp['tesoreria'] == 1 ? 'checked' : '' ?>>
                            </div>
                        </div>
                        <div type="text" class="form-control input-group-text" aria-label="Text input with checkbox">TESORERÍA</div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="tercerocrp" class="small">TERCERO</label>
                    <input type="text" id="tercerocrp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" value="<?php echo $crp['nom_tercero'] . ' -> ' . $crp['nit_tercero'] ?>">
                    <input type="hidden" id="id_tercero" name="id_tercero" value="<?php echo $crp['id_tercero_api'] ?>">
                    <input type="hidden" id="id_teractual" name="id_teractual" value="<?php echo $crp['id_tercero_api'] ?>">
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