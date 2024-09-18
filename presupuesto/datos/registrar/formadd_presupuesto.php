<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$error = "Debe diligenciar este campo";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM `pto_tipo` ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $modalidad = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
            `id_tipo_b_s`, `tipo_compra`, `tipo_contrato`, `tipo_bn_sv`
        FROM
            `tb_tipo_bien_servicio`
        INNER JOIN `tb_tipo_contratacion` 
            ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
        INNER JOIN `tb_tipo_compra` 
            ON (`tb_tipo_contratacion`.`id_tipo_compra` = `tb_tipo_compra`.`id_tipo`)
        ORDER BY `tipo_compra`, `tipo_contrato`, `tipo_bn_sv`";
    $rs = $cmd->query($sql);
    $tbnsv = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR PRESUPUESTO</h5>
        </div>
        <form id="formAddPresupuesto">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-8">
                    <label for="nomPto" class="small">NOMBRE PRESUPUESTO</label>
                    <input type="text" name="nomPto" id="nomPto" class="form-control form-control-sm">
                </div>
                <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-4">
                    <label for="tipoPto" class="small">TIPO DE PRESUPUESTO</label>
                    <select id="tipoPto" name="tipoPto" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($modalidad as $mo) {
                            echo '<option value="' . $mo['id_tipo'] . '">' . $mo['nombre'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtObjeto" class="small">DESCRIPCIÓN</label>
                    <textarea id="txtObjeto" type="text" name="txtObjeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"></textarea>
                </div>
            </div>
            <div class="text-right pb-3 px-4">
                <button class="btn btn-success btn-sm" id="btnAddPresupuesto">Agregar</button>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>