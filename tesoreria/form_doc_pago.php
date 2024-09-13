<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../terceros.php';
$id_ctb_doc = isset($_POST['id_tipo']) ? $_POST['id_tipo'] : exit('Acceso no permitido');
$id_documento = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : 0;
$id_vigencia = $_SESSION['id_vigencia'];
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`ctb_doc`.`id_manu`) AS `id_manu`, `ctb_fuente`.`nombre`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $fuente = !empty($consecutivo) ? $consecutivo['nombre'] : '---';
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_referencia`.`id_ctb_referencia`
                , `ctb_referencia`.`nombre`
            FROM
                `ctb_referencia`
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_referencia`.`id_ctb_fuente` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_fuente`.`id_doc_fuente` = $id_ctb_doc)";
    $rs = $cmd->query($sql);
    $referencia = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`detalle`
                , `ctb_doc`.`id_ref_ctb`
                , `ctb_doc`.`id_ref`
                , `tes_caja_doc`.`id_caja`
            FROM
                `ctb_doc`
                LEFT JOIN `tes_caja_doc` 
                    ON (`tes_caja_doc`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_documento)";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `numero` FROM `tes_referencia`  WHERE `estado` = 1";
    $rs = $cmd->query($sql);
    $pagos_ref = $rs->fetch();
    if (empty($datos)) {
        if ($rs->rowCount() > 0) {
            $ref = $pagos_ref['numero'];
            $chek = 'checked';
        } else {
            $ref = 0;
            $chek = '';
        }
    } else {
        $ref = $datos['id_ref'];
        $chek = $ref > 0 ? 'checked' : '';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_caja_const`
                , `nombre_caja`
                , `fecha_ini`
            FROM
                `tes_caja_const`
            WHERE (`fecha_ini` BETWEEN '$vigencia-01-01' AND '$vigencia-12-31')";
    $rs = $cmd->query($sql);
    $cajas = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date("Y-m-d");
// Estabelcer fecha minima con vigencia
$fecha_min = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
if (empty($datos)) {
    $datos['id_ctb_doc'] = 0;
    $datos['id_manu'] = $id_manu;
    $datos['id_tercero'] = 0;
    $datos['fecha'] = $fecha;
    $datos['detalle'] = '';
    $datos['id_ref_ctb'] = 0;
    $datos['id_ref'] = '';
    $tercero = '';
} else {
    $ids = $datos['id_tercero'];
    $terceros = getTerceros($ids, $cmd);
    $tercero = ltrim($terceros[0]['nom_tercero']);
}
$cero = isset($datos['id_caja']) ? $datos['id_caja'] : 0;
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIÓN DOCUMENTOS: <b><?php echo $fuente; ?></b></h5>
        </div>
        <form id="formGetMvtoTes">
            <input type="hidden" name="id_ctb_doc" value="<?php echo $id_ctb_doc; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="fecha" class="small">FECHA</label>
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datos['fecha'])); ?>" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="numDoc" class="small">NUMERO</label>
                    <input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" readonly value="<?php echo $datos['id_manu'] ?>">
                </div>
            </div>
            <?php if ($id_ctb_doc == '14') { ?>
                <div class="form-row px-4">
                    <div class="form-group col-md-12">
                        <label for="id_caja" class="small">Caja</label>
                        <select name="id_caja" id="id_caja" class="form-control form-control-sm" required>
                            <option value="0" <?php $cero == 0 || $cero = '' ? 'selected' : '' ?>>--Seleccione--</option>
                            <?php foreach ($cajas as $caja) {
                                $slc = $datos['id_caja'] == $caja['id_caja_const'] ? 'selected' : '';
                                echo '<option value="' . $caja['id_caja_const'] . '" ' . $slc . '>' . $caja['nombre_caja'] . ' -> ' . $caja['fecha_ini'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php } else { ?>
                <div class="form-row px-4">
                    <div class="form-group col-md-6">
                        <label for="ref_mov" class="small">CONCEPTO</label>
                        <select name="ref_mov" id="ref_mov" class="form-control form-control-sm" required>
                            <option value="0">--Seleccione--</option>
                            <?php foreach ($referencia as $rf) {
                                if ($datos['id_ref_ctb'] == $rf['id_ctb_referencia']) {
                                    echo '<option value="' . $rf['id_ctb_referencia'] . '" selected>' . $rf['nombre'] . '</option>';
                                } else {
                                    echo '<option value="' . $rf['id_ctb_referencia'] . '">' . $rf['nombre'] . '</option>';
                                }
                            ?>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="numDoc" class="small">REFERENCIA</label>
                        <div class="input-group">
                            <input type="text" name="referencia" id="referencia" value="<?php echo $ref; ?>" class="form-control form-control-sm" style="text-align: right;">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <input type="checkbox" id="checkboxId" onclick="definirReferenciaPago();" <?php echo $chek; ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="form-row px-4  ">
                <div class="form-group col-md-12">
                    <label for="terceromov" class="small">TERCERO</label>
                    <input type="text" name="terceromov" id="terceromov" class="form-control form-control-sm" value="<?php echo $tercero ?>">
                    <input type="hidden" name="id_tercero" id="id_tercero" class="form-control form-control-sm" value="<?php echo $datos['id_tercero'] ?>">
                </div>

            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="objeto" class="small">DETALLES</label>
                    <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="4" required><?php echo $datos['detalle'] ?></textarea>
                </div>

            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button class="btn btn-primary btn-sm" style="width: 5rem;" id="gestionarMvtoCtbPag" text="<?php echo $id_documento ?>"><?php echo $id_documento == 0 ? 'Registrar' : 'Actualizar'; ?></button>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>