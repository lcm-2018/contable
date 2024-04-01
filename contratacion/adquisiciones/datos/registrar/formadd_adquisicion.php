<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$key = array_search('53', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$error = "Debe diligenciar este campo";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM ctt_modalidad ORDER BY modalidad ASC";
    $rs = $cmd->query($sql);
    $modalidad = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_area`, `area` FROM `tb_area_c` ORDER BY `area` ASC";
    $rs = $cmd->query($sql);
    $areas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
            id_tipo_b_s, tipo_compra, tipo_contrato, tipo_bn_sv
        FROM
            tb_tipo_bien_servicio
        INNER JOIN tb_tipo_contratacion 
            ON (tb_tipo_bien_servicio.id_tipo_cotrato = tb_tipo_contratacion.id_tipo)
        INNER JOIN tb_tipo_compra 
            ON (tb_tipo_contratacion.id_tipo_compra = tb_tipo_compra.id_tipo)
        ORDER BY tipo_compra, tipo_contrato, tipo_bn_sv";
    $rs = $cmd->query($sql);
    $tbnsv = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR ADQUISICIÓN</h5>
        </div>
        <form id="formAddAdquisicion">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-3">
                    <label for="datFecAdq" class="small">FECHA ADQUISICIÓN</label>
                    <input type="date" name="datFecAdq" id="datFecAdq" class="form-control form-control-sm">
                </div>
                <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-3">
                    <label for="slcModalidad" class="small">MODALIDAD CONTRATACIÓN</label>
                    <select id="slcModalidad" name="slcModalidad" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($modalidad as $mo) {
                            echo '<option value="' . $mo['id_modalidad'] . '">' . $mo['modalidad'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="numTotalContrato" class="small">VALOR ESTIMADO</label>
                    <input type="number" name="numTotalContrato" id="numTotalContrato" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-3">
                    <label for="slcAreaSolicita" class="small">ÁREA SOLICITANTE</label>
                    <select id="slcAreaSolicita" name="slcAreaSolicita" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($areas as $ar) {
                            echo '<option value="' . $ar['id_area'] . '">' . $ar['area'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="slcTipoBnSv" class="small">TIPO DE BIEN O SERVICIO</label>
                    <select id="slcTipoBnSv" name="slcTipoBnSv" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($tbnsv as $tbs) {
                            echo '<option value="' . $tbs['id_tipo_b_s'] . '">' . $tbs['tipo_compra'] . ' || ' . $tbs['tipo_contrato'] . ' || ' . $tbs['tipo_bn_sv'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtObjeto" class="small">OBJETO</label>
                    <textarea id="txtObjeto" type="text" name="txtObjeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"></textarea>
                </div>
            </div>
            <!--
            <div id="obligacionesContratista" class="form-row px-4" style="display: none;">
                <div class="form-group col-md-12">
                    <label for="txtObligContratista" class="small">OBLIGACIONES ESPECIFICAS DEL CONTRATISTA</label>
                    <textarea id="txtObligContratista" type="text" name="txtObligContratista" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"></textarea>
                </div>
            </div>-->
            <div class="text-center">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddAdquisicion">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>