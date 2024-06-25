<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../conexion.php';
$id_ctb_doc = isset($_POST['id_tipo']) ? $_POST['id_tipo'] : exit('Acceso no permitido');
$id_documento = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : 0;
$id_vigencia = $_SESSION['id_vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_acto`
                , `nombre`
            FROM
                `pto_actos_admin`
            ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $actos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_caja_const`
                , `id_tipo_acto` AS `acto`
                , `num_acto`
                , `nombre_caja`
                , `fecha_ini`
                , `fecha_acto`
                , `valor_total`
                , `valor_minimo`
                , `num_poliza`
                , `porcentaje`
                , `estado`
            FROM
                `tes_caja_const`
            WHERE (`id_caja_const` = $id_documento)";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date("Y-m-d");
// Estabelcer fecha minima con vigencia
$fecha_min = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
if (empty($datos)) {
    $datos['id_caja_const'] = 0;
    $datos['acto'] = 0;
    $datos['num_acto'] = '';
    $datos['nombre_caja'] = '';
    $datos['fecha_ini'] = $fecha;
    $datos['fecha_acto'] = $fecha;
    $datos['valor_total'] = '';
    $datos['valor_minimo'] = '';
    $datos['num_poliza'] = '';
    $datos['porcentaje'] = '';
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;"><b>CONSTITUCIÓN DE CAJA MENOR</b></h5>
        </div>
        <form id="formGetMvtoCaja">
            <input type="hidden" name="id_ctb_doc" value="<?php echo $id_ctb_doc; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="slcTipActo" class="small">tipo acto</label>
                    <select name="slcTipActo" id="slcTipActo" class="form-control form-control-sm" required>
                        <option value="0" <?php echo $datos['acto'] == 0 ? 'selected' : '' ?>>--Seleccione--</option>
                        <?php foreach ($actos as $acto) {
                            $slc = $datos['acto'] == $acto['id_acto'] ? 'selected' : '';
                            echo '<option value="' . $acto['id_acto'] . '" ' . $slc . '>' . $acto['nombre'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="numActo" class="small">Num. Acto</label>
                    <input type="text" name="numActo" id="numActo" class="form-control form-control-sm" value="<?php echo $datos['num_acto']; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="txtNomCaja" class="small">Nombre Caja</label>
                    <input type="text" name="txtNomCaja" id="txtNomCaja" class="form-control form-control-sm" value="<?php echo $datos['nombre_caja']; ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label for="fecIniciaCaja" class="small">Inicia</label>
                    <input type="date" name="fecIniciaCaja" id="fecIniciaCaja" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datos['fecha_ini'])); ?>" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="fecActoDc" class="small">Acto</label>
                    <input type="date" name="fecActoDc" id="fecActoDc" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datos['fecha_acto'])); ?>" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="txtPoliza" class="small">Num. Póliza</label>
                    <input type="text" name="txtPoliza" id="txtPoliza" class="form-control form-control-sm" value="<?php echo $datos['num_poliza']; ?>">
                </div>
            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-4">
                    <label for="valTotal" class="small">Valor Total</label>
                    <input type="text" name="valTotal" id="valTotal" class="form-control form-control-sm" value="<?php echo $datos['valor_total']; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="valMinimo" class="small">Valor Mínimo</label>
                    <input type="text" name="valMinimo" id="valMinimo" class="form-control form-control-sm" value="<?php echo $datos['valor_minimo']; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="porcentajeCs" class="small">Porcentaje (%)</label>
                    <input type="text" name="porcentajeCs" id="porcentajeCs" class="form-control form-control-sm" value="<?php echo $datos['porcentaje']; ?>">
                </div>
            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button class="btn btn-primary btn-sm" style="width: 5rem;" id="gestionarMvtoCtbCaja" text="<?php echo $id_documento ?>"><?php echo $id_documento == 0 ? 'Registrar' : 'Actualizar'; ?></button>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>