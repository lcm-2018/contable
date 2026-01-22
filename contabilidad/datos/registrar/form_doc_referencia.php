<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_ctb_ref = isset($_POST['id_ctb_ref']) ? $_POST['id_ctb_ref'] : exit('Acceso no permitido');
$id_doc_ref = $_POST['id_doc_ref'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_referencia`.`id_ctb_referencia`
                , `ctb_referencia`.`id_cuenta`
                , IF(`ctb_referencia`.`id_cta_credito` IS NULL, 0, `ctb_referencia`.`id_cta_credito`) AS `id_cta_credito`
                ,`ctb_referencia`.`nombre`
                , `ctb_pgcp`.`nombre` AS `nombre2`
                , `ctb_referencia`.`accion`
                , `ctb_referencia`.`estado`
                , CONCAT(`ctb_pgcp`.`nombre`, ' -> ',`ctb_pgcp`.`cuenta`) AS `nom_cuenta`
                , CONCAT(`pgcp`.`nombre`, ' -> ',`pgcp`.`cuenta`) AS `nom_cuenta2`
                , 'D' AS `tipo`
                , 'D' AS `tipo2`
                , `ctb_referencia`.`accion_pto`
                , `ctb_referencia`.`id_rubro`
                , '1' AS `tipo_rubro`
                , CONCAT_WS(' - ',`pto_cargue`.`cod_pptal`, `pto_cargue`.`nom_rubro`) AS `nom_rubro`
            FROM `ctb_referencia`
                LEFT JOIN `ctb_pgcp` 
                    ON (`ctb_referencia`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `ctb_pgcp` AS `pgcp`
                    ON (`ctb_referencia`.`id_cta_credito` = `pgcp`.`id_pgcp`)
                LEFT JOIN `pto_cargue`
                    ON (`ctb_referencia`.`id_rubro` = `pto_cargue`.`id_cargue`)
            WHERE `ctb_referencia`.`id_ctb_referencia` = $id_ctb_ref";
    $rs = $cmd->query($sql);
    $referencias = $rs->fetch(PDO::FETCH_ASSOC);
    if (empty($referencias)) {
        $referencias = [
            'id_ctb_referencia' => 0,
            'id_cuenta' => 0,
            'id_cta_credito' => 0,
            'nombre' => '',
            'nombre2' => '',
            'accion' => 2,
            'estado' => 1,
            'nom_cuenta' => '',
            'nom_cuenta2' => '',
            'tipo' => 'M',
            'tipo2' => 'M'
        ];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$ver = $referencias['accion'] == 1 ? 'block' : 'none';
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REFERENCIA</b></h5>
        </div>
        <form id="formRefDr">
            <input type="hidden" name="id_doc_ref" id="id_doc_ref" value="<?php echo $id_doc_ref; ?>">
            <input type="hidden" name="id_ctb_ref" id="id_ctb_ref" value="<?php echo $id_ctb_ref; ?>">
            <input type="hidden" id="id_pto_movto" value="1">
            <div class="form-row px-4 pt-3">
                <div class="form-group col-md-8">
                    <label for="nombre" class="small">Nombre</label>
                    <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" value="<?php echo $referencias['nombre']; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="accion" class="small">acción</label>
                    <select class="form-control form-control-sm" id="accion" name="accion" onchange="cambiarTipoRefDr(value);">
                        <option value="2">--Seleccione--</option>
                        <option value="1" <?php echo $referencias['accion'] == 1 ? 'selected' : ''; ?>>INGRESO</option>
                        <option value="0" <?php echo $referencias['accion'] == 0 ? 'selected' : ''; ?>>GASTO</option>
                    </select>
                </div>
            </div>
            <div class="form-row px-4 pb-3">
                <div class="form-group col-md-6">
                    <label for="codigoCta1" class="small">Cuenta D</label>
                    <input type="text" name="codigoCta1" id="codigoCta1" class="form-control form-control-sm" value="<?php echo $referencias['nom_cuenta']; ?>">
                    <input type="hidden" name="id_codigoCta1" id="id_codigoCta1" value="<?php echo $referencias['id_cuenta']; ?>">
                    <input type="hidden" name="tipoDato1" id="tipoDato1" value="<?php echo $referencias['tipo']; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="codigoCta2" class="small">Cuenta C</label>
                    <input type="text" name="codigoCta2" id="codigoCta2" class="form-control form-control-sm" value="<?php echo $referencias['nom_cuenta2']; ?>">
                    <input type="hidden" name="id_codigoCta2" id="id_codigoCta2" value="<?php echo $referencias['id_cta_credito']; ?>">
                    <input type="hidden" name="tipoDato2" id="tipoDato2" value="<?php echo $referencias['tipo2']; ?>">
                </div>
            </div>
            <div id="divAfectacion" style="display:<?php echo $ver; ?>;">
                <div class="form-row px-4 pb-2">
                    <div class="form-group col-md-12 text-center mt-1">
                        <label class="small d-block" for="marca_si">AFECTA PRESUPUESTO</label>
                        <div class="form-control-sm border rounded px-2 py-1 bg-light">
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="afectacion" id="afectacion1" value="1" checked>
                                <label class="form-check-label small" for="afectacion1">Recaudo</label>
                            </div>
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="afectacion" id="afectacion2" value="2">
                                <label class="form-check-label small" for="afectacion2">Reconocimiento y recaudo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row px-4 pb-2">
                    <div class="form-group col-md-12 text-center">
                        <label for="rubroCod" class="small">CUENTA PRESUPUESTO DE INGRESOS</label>
                        <input type="text" class="form-control form-control-sm" id="rubroCod" name="rubroCod" value="<?php echo isset($referencias['nom_rubro']) ? $referencias['nom_rubro'] : ''; ?>">
                        <input type="hidden" name="id_rubroCod" id="id_rubroCod" value="<?php echo isset($referencias['id_rubro']) ? $referencias['id_rubro'] : '0'; ?>">
                        <input type="hidden" name="tipoRubro" id="tipoRubro" value="<?php echo isset($referencias['tipo_rubro']) ? $referencias['tipo_rubro'] : '0'; ?>">
                    </div>
                </div>
            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button type="button" class="btn btn-primary btn-sm" onclick="GuardarReferenciaDr(this)">Guardar</button>
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
        </div>
    </div>
</div>