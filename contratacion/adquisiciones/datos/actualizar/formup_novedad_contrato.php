<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../terceros.php';
$data = isset($_POST['datos']) ? explode('|', $_POST['datos']) : exit('Acción no permitida ');
$id_novedad = $data[0];
$opcion = $data[1];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_novedad` , `descripcion` FROM `ctt_tipo_novedad`";
    $rs = $cmd->query($sql);
    $tip_novedad = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (empty($tip_novedad)) {
    echo 'Error al intentar obetener tipos de novedad';
    exit();
}
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
switch ($opcion) {
    case 1:
    case 2:
    case 3:
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT
                        `id_nov_con`, `val_adicion`, `fec_adcion`, `fec_ini_prorroga`, `fec_fin_prorroga`, `observacion`, `id_cdp`
                    FROM
                        `ctt_novedad_adicion_prorroga`
                    WHERE `id_nov_con` = $id_novedad";
            $rs = $cmd->query($sql);
            $detalles_novedad = $rs->fetch();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR/MODIFICAR ADICIÓN Y/O PRORROGA DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-12">
                            <label for="slcTipoNovedad" class="small">TIPO DE NOVEDAD</label>
                            <select id="slcTipoNovedad" name="slcTipoNovedad" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                <?php
                                $ver1 = $ver2 = 'none';
                                foreach ($tip_novedad as $tn) {
                                    $slc = $opcion == $tn['id_novedad'] ? 'selected' : '';
                                    echo '<option ' . $slc . ' value="' . $tn['id_novedad'] . '">' . $tn['descripcion'] . '</option>';
                                }
                                if ($opcion == 3) {
                                    $ver1 = $ver2 = true;
                                } else if ($opcion == 2) {
                                    $ver2 = true;
                                } else {
                                    $ver1 = true;
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row px-4" id="divAdicion" style="display: <?php echo $ver1 ?>;">
                        <div class="form-group col-md-6">
                            <label for="numValAdicion" class="small">VALOR</label>
                            <input type="number" name="numValAdicion" id="numValAdicion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['val_adicion'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="datFecAdicion" class="small">FECHA DE ADICIÓN</label>
                            <input type="date" name="datFecAdicion" id="datFecAdicion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_adcion'] ?>">
                        </div>
                    </div>
                    <?php if (false) { ?>
                        <div class="form-row px-4" id="divCDPadicion" style="display: <?php echo $ver1 ?>;">
                            <div class="form-group col-md-12">
                                <label for="slcCDP" class="small">CDP</label>
                                <select id="slcCDP" name="slcCDP" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                    <option value="1">CDP-001</option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-row px-4" id="divProrroga" style="display: <?php echo $ver2 ?>;">
                        <div class="form-group col-md-6">
                            <label for="datFecIniProrroga" class="small">FECHA INICIAL</label>
                            <input type="date" name="datFecIniProrroga" id="datFecIniProrroga" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_ini_prorroga'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="datFecFinProrroga" class="small">FECHA FINAL</label>
                            <input type="date" name="datFecFinProrroga" id="datFecFinProrroga" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_fin_prorroga'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4" id="divObservaNov">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
        break;
    case 4:
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT
                        `id_cesion`, `id_adq`, `id_tipo_nov`, `id_tercero`, `fec_cesion`, `observacion`
                    FROM
                        `ctt_novedad_cesion`
                    WHERE (`id_cesion` = $id_novedad)";
            $rs = $cmd->query($sql);
            $detalles_novedad = $rs->fetch();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $id_t[] = $detalles_novedad['id_tercero'];
        $ids = implode(',', $id_t);
        $terceros_api = getTerceros($ids, $cmd);
        $cmd = null;
        $tercero = isset($terceros_api[0]) ? ltrim($terceros_api[0]['nom_tercero'] . ' -> ' . $terceros_api[0]['nit_tercero']) : '';
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR CESIÓN DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" name="id_contrato" value="<?php echo $detalles_novedad['id_adq'] ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="4">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-4">
                            <label for="datFecCesion" class="small">FECHA CESIÓN</label>
                            <input type="date" name="datFecCesion" id="datFecCesion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_cesion'] ?>">
                        </div>
                        <div class="form-group col-md-8">
                            <label for="slcTerceroCesion" class="small">TERCERO CESIONARIO</label>
                            <input type="text" id="SeaTercer" class="form-control form-control-sm" value="<?php echo $tercero ?>">
                            <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $detalles_novedad['id_tercero'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
        break;
    case 5:
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT
                        `id_suspension`, `id_adq`, `id_tipo_nov`, `fec_inicia`, `fec_fin`, `observacion`
                    FROM
                        `ctt_novedad_suspension`
                    WHERE (`id_suspension` = $id_novedad)";
            $rs = $cmd->query($sql);
            $detalles_novedad = $rs->fetch();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR SUSPENCIÓN DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" name="id_contrato" value="<?php echo $detalles_novedad['id_adq'] ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="5">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-6">
                            <label for="datFecIniSuspencion" class="small">FECHA INICIAL</label>
                            <input type="date" name="datFecIniSuspencion" id="datFecIniSuspencion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_inicia'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="datFecFinSuspencion" class="small">FECHA FINAL</label>
                            <input type="date" name="datFecFinSuspencion" id="datFecFinSuspencion" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_fin'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
        break;
    case 6:
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT
                        `ctt_novedad_reinicio`.`id_reinicio`
                        , `ctt_novedad_reinicio`.`id_tipo_nov`
                        , `ctt_novedad_reinicio`.`id_suspension`
                        , `ctt_novedad_reinicio`.`fec_reinicia`
                        , `ctt_novedad_reinicio`.`observacion`
                        , `ctt_novedad_suspension`.`id_adq`
                    FROM
                        `ctt_novedad_reinicio`
                        INNER JOIN `ctt_novedad_suspension` 
                            ON (`ctt_novedad_reinicio`.`id_suspension` = `ctt_novedad_suspension`.`id_suspension`)
                    WHERE (`ctt_novedad_reinicio`.`id_reinicio` = $id_novedad)";
            $rs = $cmd->query($sql);
            $detalles_novedad = $rs->fetch();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $id_contrato = $detalles_novedad['id_adq'];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT 
                        `id_suspension`,`id_adq`,`id_tipo_nov`,`fec_inicia`,`fec_fin`,`observacion` 
                    FROM
                        `ctt_novedad_suspension`
                    WHERE `id_suspension` IN (SELECT MAX(`id_suspension`) FROM `ctt_novedad_suspension` WHERE (`id_adq` = $id_contrato))";
            //echo $sql;
            $rs = $cmd->query($sql);
            $suspensiones = $rs->fetch();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR REINICIO DE CONTRATO</h5>
                </div>
                <br>
                <div class="px-4">
                    <?php
                    if ($suspensiones['id_suspension'] == '') {
                    ?>
                        <div class="alert alert-danger" role="alert">
                            PRIMERO DEBE REGISTAR UNA SUSPENCIÓN DE CONTRATO!
                        </div>
                        <div class="text-center pb-3">
                            <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                        </div>
                    <?php
                    } else {
                    ?>
                        <table class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Inicial</th>
                                    <th>Fecha Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo $suspensiones['id_suspension'] ?></td>
                                    <td><?php echo $suspensiones['fec_inicia'] ?></td>
                                    <td><?php echo $suspensiones['fec_fin'] ?></td>
                                </tr>
                            </tbody>
                        </table>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" id="fecIniSus" value="<?php echo $suspensiones['fec_inicia'] ?>">
                    <input type="hidden" id="fecFinSus" value="<?php echo $suspensiones['fec_fin'] ?>">
                    <input type="hidden" id="id_suspension" name="id_suspension" value="<?php echo $suspensiones['id_suspension'] ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="6">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-12">
                            <label for="datFecReinicio" class="small">FECHA APROBADA REINICIO</label>
                            <input type="date" name="datFecReinicio" id="datFecReinicio" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_reinicia'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            <?php } ?>
            </div>
        </div>
    <?php
        break;
    case 7:
        //API URL
        $url = $api . 'terceros/datos/res/listar/tipos_terminacion_contrato';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $tip_terminacion = json_decode($result, true);
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT
                        `id_terminacion`, `id_tipo_nov`, `id_t_terminacion`, `id_adq`, `observacion`
                    FROM
                        `ctt_novedad_terminacion`
                    WHERE (`id_terminacion` = $id_novedad)";
            $rs = $cmd->query($sql);
            $detalles_novedad = $rs->fetch();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR TERMINACIÓN DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="7">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-12">
                            <label for="slcTipTerminacion" class="small">TIPO DE TERMINACIÓN DE CONTRATO</label>
                            <select id="slcTipTerminacion" name="slcTipTerminacion" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                <?php
                                foreach ($tip_terminacion as $tt) {
                                    $slc = $tt['id_tipo_term'] == $detalles_novedad['id_t_terminacion'] ? 'selected' : '';
                                    echo '<option ' . $slc . ' value="' . $tt['id_tipo_term'] . '">' . $tt['descripcion'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    <?php
        break;
    case 8:
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT
                        `id_liquidacion`, `id_tipo_nov`, `id_t_liq`, `id_adq`, `fec_liq`, `val_cte`, `val_cta`, `observacion`
                    FROM
                        `ctt_novedad_liquidacion`
                    WHERE (`id_liquidacion` = $id_novedad)";
            $rs = $cmd->query($sql);
            $detalles_novedad = $rs->fetch();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    ?>
        <div class="px-0">
            <div class="shadow">
                <div class="card-header" style="background-color: #16a085 !important;">
                    <h5 style="color: white;">ACTUALIZAR O MODIFICAR LIQUIDACIÓN DE CONTRATO</h5>
                </div>
                <form id="formUpNovContrato">
                    <input type="hidden" name="id_novendad" value="<?php echo $id_novedad ?>">
                    <input type="hidden" name="slcTipoNovedad" id="slcTipoNovedad" value="8">
                    <div class="form-row px-4 pt-2">
                        <div class="form-group col-md-4">
                            <label for="datFecLiq" class="small">FECHA LIQUIDACIÓN</label>
                            <input type="date" name="datFecLiq" id="datFecLiq" class="form-control form-control-sm" value="<?php echo $detalles_novedad['fec_liq'] ?>">
                        </div>
                        <div class="form-group col-md-8">
                            <label for="slcTipLiquidacion" class="small">TIPO DE LIQUIDACIÓN DE CONTRATO</label>
                            <select id="slcTipLiquidacion" name="slcTipLiquidacion" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                <?php
                                $slc1 = $detalles_novedad['id_t_liq'] == 1 ? 'selected' : '';
                                $slc2 = $detalles_novedad['id_t_liq'] == 2 ? 'selected' : '';
                                ?>
                                <option <?php echo $slc1 ?> value="1">UNILATERAL</option>
                                <option <?php echo $slc2 ?> value="2">MUTUO ACUERDO</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-6">
                            <label for="numValFavorCtrate" class="small">VALOR A FAVOR CONTRATANTE</label>
                            <input type="number" name="numValFavorCtrate" id="numValFavorCtrate" class="form-control form-control-sm" value="<?php echo $detalles_novedad['val_cte'] ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="numValFavorCtrista" class="small">VALOR A FAVOR CONTRATISTA</label>
                            <input type="number" name="numValFavorCtrista" id="numValFavorCtrista" class="form-control form-control-sm" value="<?php echo $detalles_novedad['val_cta'] ?>">
                        </div>
                    </div>
                    <div class="form-row px-4">
                        <div class="form-group col-md-12">
                            <label for="txtAObservaNov" class="small">OBSERVACIONES</label>
                            <textarea class="form-control" id="txtAObservaNov" name="txtAObservaNov" rows="3"><?php echo $detalles_novedad['observacion'] ?></textarea>
                        </div>
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpNovContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
<?php
        break;
}
?>