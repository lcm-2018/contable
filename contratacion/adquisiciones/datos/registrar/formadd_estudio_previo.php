<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../terceros.php';
$id_compra = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida ');
$error = "Debe diligenciar este campo";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_adquisicion`, `val_contrato`
            FROM
                `ctt_adquisiciones` WHERE `id_adquisicion` = '$id_compra'";
    $rs = $cmd->query($sql);
    $adquisicion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_form_pago`, `descripcion`
            FROM
                `tb_forma_pago_compras` ORDER BY `descripcion` ASC ";
    $rs = $cmd->query($sql);
    $forma_pago = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_terceros`.`id_tercero_api`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`nom_tercero`
            FROM
                `tb_terceros`
                INNER JOIN `tb_rel_tercero` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `tb_terceros`.`id_tercero_api`)
            WHERE `tb_terceros`.`estado` = 1 AND `tb_rel_tercero`.`id_tipo_tercero` = 3";
    $rs = $cmd->query($sql);
    $supervisor = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
            `id_poliza`
            , `descripcion`
            , `porcentaje`
        FROM
            `tb_polizas` ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $polizas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR ESTUDIOS PREVIOS</h5>
        </div>
        <form id="formAddEstudioPrevio">
            <input type="hidden" name="id_compra" value="<?php echo $id_compra ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="datFecIniEjec" class="small">FECHA INICIAL</label>
                    <input type="date" name="datFecIniEjec" id="datFecIniEjec" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-4">
                    <label for="datFecFinEjec" class="small">FECHA FINAL</label>
                    <input type="date" name="datFecFinEjec" id="datFecFinEjec" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-4">
                    <label for="numValContrata" class="small">Valor total contrata</label>
                    <input type="number" name="numValContrata" id="numValContrata" class="form-control form-control-sm" value="<?php echo $adquisicion['val_contrato'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label for="slcFormPago" class="small">FORMA DE PAGO</label>
                    <select id="slcFormPago" name="slcFormPago" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($forma_pago as $fp) {
                            echo '<option value="' . $fp['id_form_pago'] . '">' . $fp['descripcion'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="slcSupervisor" class="small">SUPERVISOR</label>
                    <select id="slcSupervisor" name="slcSupervisor" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <option value="A">PENDIENTE</option>
                        <?php
                        foreach ($supervisor as $s) {
                            echo '<option value="' . $s['id_tercero_api'] . '">' . $s['nom_tercero'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="numDS" class="small">Número DC</label>
                    <input type="number" name="numDS" id="numDS" class="form-control form-control-sm">
                </div>
            </div>
            <label for="slcSupervisor" class="small">PÓLIZAS</label>
            <div class="form-row px-4">
                <?php
                $cant = 1;
                foreach ($polizas as $pz) { ?>
                    <div class="form-group col-md-4 mb-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" aria-label="Checkbox for following text input" id="check_<?php echo $cant;
                                                                                                                    $cant++ ?>" name="check[]" value="<?php echo $pz['id_poliza'] ?>">
                                </div>
                            </div>
                            <div class="form-control form-control-sm text-left" aria-label="Text input with checkbox" style="font-size: 55%;"><?php echo $pz['descripcion'] . ' ' . $pz['porcentaje'] ?> </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </form>
        <div class="form-row text-center px-4">
            <div class="form-group col-md-6">
                <label for="txtDescNec" class="small">Descripción de la necesidad</label>
                <textarea name="txtDescNec" id="txtDescNec" cols="30" rows="2" class="form-control form-control-sm"></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="txtActEspecificas" class="small">Actividades específicas</label>
                <textarea name="txtActEspecificas" id="txtActEspecificas" cols="30" rows="2" class="form-control form-control-sm"></textarea>
            </div>
        </div>
        <div class="form-row text-center px-4">
            <div class="form-group col-md-6">
                <label for="txtProdEntrega" class="small">PRODUCTOS A ENTREGAR</label>
                <textarea name="txtProdEntrega" id="txtProdEntrega" cols="30" rows="2" class="form-control form-control-sm"></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="txtObligContratista" class="small">Obligaciones del Contratista</label>
                <textarea name="txtObligContratista" id="txtObligContratista" cols="30" rows="2" class="form-control form-control-sm"></textarea>
            </div>
        </div>
        <div class="form-row text-center px-4">
            <div class="form-group col-md-6">
                <label for="txtDescValor" class="small">Descripción de valor</label>
                <textarea name="txtDescValor" id="txtDescValor" cols="30" rows="2" class="form-control form-control-sm"></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="txtFormPago" class="small">Forma de Pago</label>
                <textarea name="txtFormPago" id="txtFormPago" cols="30" rows="2" class="form-control form-control-sm"></textarea>
            </div>
        </div>
        <div class="form-row text-center px-4">
            <div class="form-group col-md-6">
                <label for="txtReqMinHab" class="small">Req. mínimos Habilitantes</label>
                <textarea name="txtReqMinHab" id="txtReqMinHab" cols="30" rows="2" class="form-control form-control-sm"></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="txtGarantias" class="small">Garantías Contratación</label>
                <textarea name="txtGarantias" id="txtGarantias" cols="30" rows="2" class="form-control form-control-sm"></textarea>
            </div>
        </div>
        <div class="text-center pb-3">
            <button class="btn btn-primary btn-sm" id="btnAddNewEstudioPrevio">Registrar</button>
            <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
        </div>
    </div>
</div>