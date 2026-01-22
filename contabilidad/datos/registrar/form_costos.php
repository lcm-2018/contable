<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id = $_POST['id'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_pgcp`.`id_pgcp`
                ,  CONCAT_WS(' - ', `ctb_pgcp`.`cuenta`, `ctb_pgcp`.`nombre`) AS `cuenta`
                , IFNULL(`ccc`.`id_cta_debito`, 0) AS `id_debito`
                , CONCAT_WS(' - ',`tc_d`.`cuenta`, `tc_d`.`nombre`) AS `cta_debito`
                , IFNULL(`tc_d`.`tipo_dato`, 'M') AS `tp_debito`
                , IFNULL(`ccc`.`id_cta_credito`, 0) AS `id_credito`
                , CONCAT_WS(' - ',`tc_c`.`cuenta`, `tc_c`.`nombre`) AS `cta_credito`
                , IFNULL(`tc_c`.`tipo_dato`, 'M') AS `tp_credito`
                , `ccc`.`id_cta`
            FROM
                `ctb_pgcp` 
                LEFT JOIN `ctb_cuenta_costo` `ccc` ON (`ccc`.`id_cta_costo` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `ctb_pgcp` AS `tc_d` ON (`ccc`.`id_cta_debito` = `tc_d`.`id_pgcp`)
                LEFT JOIN `ctb_pgcp` AS `tc_c` ON (`ccc`.`id_cta_credito` = `tc_c`.`id_pgcp`)
            WHERE (`ctb_pgcp`.`id_pgcp` = $id)";
    $rs = $cmd->query($sql);
    $cuenta = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$opcion = $cuenta['id_cta'] > 0 ? $cuenta['id_cta'] : 0;
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">TRASLADO DE COSTOS</h5>
        </div>
        <form id="formTrasladoCostos" class="px-3">
            <input type="hidden" name="opcion" id="opcion" value="<?= $opcion; ?>">
            <input type="hidden" name="id_pgcp" id="id_pgcp" value="<?= $cuenta['id_pgcp']; ?>">
            <div class="form-row pt-2">
                <div class="form-group col-md-12">
                    <label for="txtCuenta" class="small">CUENTA SELECCIONADA</label>
                    <input type="text" name="txtCuenta" id="txtCuenta" class="form-control form-control-sm" value="<?= $cuenta['cuenta'] ?>" readonly disabled>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="codigoCta1" class="small">CUENTA DE DÉBITO</label>
                    <input type="text" name="codigoCta1" id="codigoCta1" class="form-control form-control-sm" value="<?= $cuenta['cta_debito']; ?>">
                    <input type="hidden" name="id_codigoCta1" id="id_codigoCta1" value="<?= $cuenta['id_debito']; ?>">
                    <input type="hidden" name="tipoDato1" id="tipoDato1" value="<?= $cuenta['tp_debito']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="codigoCta2" class="small">CUENTA DE CRÉDITO</label>
                    <input type="text" name="codigoCta2" id="codigoCta2" class="form-control form-control-sm" value="<?= $cuenta['cta_credito']; ?>">
                    <input type="hidden" name="id_codigoCta2" id="id_codigoCta2" value="<?= $cuenta['id_credito']; ?>">
                    <input type="hidden" name="tipoDato2" id="tipoDato2" value="<?= $cuenta['tp_credito']; ?>">
                </div>
            </div>
        </form>
    </div>
    <div class="text-center py-3">
        <button class="btn btn-primary btn-sm" onclick="GuardarCtasTrasladoCostos()">Guardar</button>
        <button type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</button>
    </div>
</div>