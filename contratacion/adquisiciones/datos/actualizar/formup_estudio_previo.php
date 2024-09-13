<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../terceros.php';
$id_ep = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida ');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_est_prev`, `id_compra`, `fec_fin_ejec`, `fec_ini_ejec`, `val_contrata`, `id_forma_pago`
                , `id_supervisor`, `necesidad`, `act_especificas`, `prod_entrega`, `obligaciones`, `forma_pago`
                , `num_ds`, `requisitos`,`garantia`, `describe_valor`
            FROM
                `ctt_estudios_previos`
            WHERE `id_est_prev` = '$id_ep'";
    $rs = $cmd->query($sql);
    $estudio_prev = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$est_prev = isset($estudio_prev) ? $estudio_prev['id_est_prev'] : 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_garantia`, `id_est_prev`, `id_poliza`
            FROM
                `seg_garantias_compra`
            WHERE `id_est_prev`  = '$est_prev'";
    $rs = $cmd->query($sql);
    $garantias = $rs->fetchAll();
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
                `seg_terceros`.`id_tercero`, `seg_terceros`.`no_doc`, `tb_rel_tercero`.`id_tercero_api`
            FROM
                `tb_rel_tercero`
                INNER JOIN `seg_terceros` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
            WHERE `seg_terceros`.`estado` = 1 AND `tb_rel_tercero`.`id_tipo_tercero` = 3";
    $rs = $cmd->query($sql);
    $terceros_sup = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($terceros_sup)) {
    $ced = [];
    foreach ($terceros_sup as $tE) {
        $ced[] = $tE['id_tercero_api'];
    }
    $ids = implode(',', $ced);
    $supervisor = getTerceros($ids, $cmd);
    $cmd = null;
} else {
    echo "No se ha registrado ningun tercero" . '<br><br><a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>';
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
            <h5 style="color: white;">ACTUALIZAR ESTUDIOS PREVIOS</h5>
        </div>
        <form id="formUpEstudioPrevio">
            <input type="hidden" name="id_est_prev" value="<?php echo $id_ep ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="datFecIniEjec" class="small">FECHA INICIAL</label>
                    <input type="date" name="datFecIniEjec" id="datFecIniEjec" class="form-control form-control-sm" value="<?php echo $estudio_prev['fec_ini_ejec'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="datFecFinEjec" class="small">FECHA FINAL</label>
                    <input type="date" name="datFecFinEjec" id="datFecFinEjec" class="form-control form-control-sm" value="<?php echo $estudio_prev['fec_fin_ejec'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="numValContrata" class="small">Valor total contrata</label>
                    <input type="number" name="numValContrata" id="numValContrata" class="form-control form-control-sm" value="<?php echo $estudio_prev['val_contrata'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label for="slcFormPago" class="small">FORMA DE PAGO</label>
                    <select id="slcFormPago" name="slcFormPago" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($forma_pago as $fp) {
                            $selecionada = '';
                            if ($fp['id_form_pago'] == $estudio_prev['id_forma_pago']) {
                                $selecionada = 'selected';
                            }
                            echo '<option ' . $selecionada . ' value="' . $fp['id_form_pago'] . '">' . $fp['descripcion'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="slcSupervisor" class="small">SUPERVISOR</label>
                    <select id="slcSupervisor" name="slcSupervisor" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">--Selecionar--</option>
                        <?php
                        foreach ($supervisor as $s) {
                            $selecionada = '';
                            if ($s['id_tercero_api'] == $estudio_prev['id_supervisor']) {
                                $selecionada = 'selected';
                            }
                            echo '<option ' . $selecionada . ' value="' . $s['id_tercero_api'] . '">' . $s['nom_tercero']  . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="numDS" class="small">Número DC</label>
                    <input type="number" name="numDS" id="numDS" class="form-control form-control-sm" value="<?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['num_ds']))) ?>">
                </div>
            </div>
            <label for="slcSupervisor" class="small">PÓLIZAS</label>
            <div class="form-row px-4">

                <?php
                $cant = 1;
                foreach ($polizas as $pz) {
                    $chequeado = '';
                    $idp = $pz['id_poliza'];
                    $key = array_search($idp, array_column($garantias, 'id_poliza'));
                    if (false !== $key) {
                        $chequeado = 'checked';
                    }
                ?>
                    <div class="form-group col-md-4 mb-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" aria-label="Checkbox for following text input" id="check_<?php echo $cant;
                                                                                                                    $cant++ ?>" name="check[]" value="<?php echo $pz['id_poliza'] ?>" <?php echo $chequeado ?>>
                                </div>
                            </div>
                            <div class="form-control form-control-sm" aria-label="Text input with checkbox" style="font-size: 55%;"><?php echo $pz['descripcion'] . ' ' . $pz['porcentaje'] . '%' ?> </div>
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
                <textarea name="txtDescNec" id="txtDescNec" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['necesidad']))) ?></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="txtActEspecificas" class="small">Actividades específicas</label>
                <textarea name="txtActEspecificas" id="txtActEspecificas" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['act_especificas']))) ?></textarea>
            </div>
        </div>
        <div class="form-row text-center px-4">
            <div class="form-group col-md-6">
                <label for="txtProdEntrega" class="small">PRODUCTOS A ENTREGAR</label>
                <textarea name="txtProdEntrega" id="txtProdEntrega" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['prod_entrega']))) ?></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="txtObligContratista" class="small">Obligaciones del Contratista</label>
                <textarea name="txtObligContratista" id="txtObligContratista" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['obligaciones']))) ?></textarea>
            </div>
        </div>
        <div class="form-row text-center px-4">
            <div class="form-group col-md-6">
                <label for="txtDescValor" class="small">Descripción de valor</label>
                <textarea name="txtDescValor" id="txtDescValor" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['describe_valor']))) ?></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="txtFormPago" class="small">Forma de Pago</label>
                <textarea name="txtFormPago" id="txtFormPago" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['forma_pago']))) ?></textarea>
            </div>

        </div>
        <div class="form-row text-center px-4">
            <div class="form-group col-md-6">
                <label for="txtReqMinHab" class="small">Req. mínimos Habilitantes</label>
                <textarea name="txtReqMinHab" id="txtReqMinHab" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['requisitos']))) ?></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="txtGarantias" class="small">Garantías Contratación</label>
                <textarea name="txtGarantias" id="txtGarantias" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudio_prev['garantia']))) ?></textarea>
            </div>
        </div>
        <div class="text-center pb-3">
            <button class="btn btn-primary btn-sm" id="btnUpEstudioPrevio">Actualizar</button>
            <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
        </div>
    </div>
</div>