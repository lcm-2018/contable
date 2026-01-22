<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
                FROM
                    nom_embargos
                WHERE id_embargo = '$id'";
    $rs = $cmd->query($sql);
    $embargo = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM nom_juzgados ORDER BY nom_juzgado ASC";
    $rs = $cmd->query($sql);
    $juzgados = $rs->fetchAll();
    $sql = "SELECT * 
            FROM nom_tipo_embargo
            ORDER BY id_tipo_emb ASC";
    $rs = $cmd->query($sql);
    $tipoembargo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR EMBARGO</h5>
        </div>
        <form id="formUpEmbargo">
            <input type="number" name="numidEmbargo" value="<?php echo $embargo['id_embargo'] ?>" hidden="true">
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label class="small" for="slcUpJuzgado">Juzgado</label>
                    <select id="slcUpJuzgado" name="slcUpJuzgado" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <?php
                        $valtotal = $embargo['valor_total'];
                        $valmes = $embargo['valor_mes'];
                        $porcent = $embargo['porcentaje'];
                        $fecinemb = $embargo['fec_inicio'];
                        $fecfinemb = $embargo['fec_fin'];
                        $idembargo = $embargo['id_embargo'];
                        $jzactual = $embargo['id_juzgado'];
                        $tipembactual = $embargo['tipo_embargo'];
                        $dctomax = $embargo['dcto_max'];
                        foreach ($juzgados as $jz) {
                            if ($jz['id_juzgado'] !== $jzactual) {
                                echo '<option value="' . $jz['id_juzgado'] . '">' . $jz['nom_juzgado'] . '</option>';
                            } else {
                                echo '<option selected value="' . $jz['id_juzgado'] . '">' . $jz['nom_juzgado'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="slcUpTipoEmbargo">Tipo</label>
                    <select id="slcUpTipoEmbargo" name="slcUpTipoEmbargo" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <?php
                        foreach ($tipoembargo as $tpem) {
                            if ($tpem['id_tipo_emb'] === $tipembactual) {
                                echo '<option selected value="te=' . $tpem['id_tipo_emb'] . '&ie=' . $embargo["id_empleado"] . '">' . mb_strtoupper($tpem['tipo']) . '</option>';
                            } else {
                                echo '<option value="te=' . $tpem['id_tipo_emb'] . '&ie=' . $embargo["id_empleado"] . '">' . mb_strtoupper($tpem['tipo']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="numUpDctoAprox">Dcto. Máximo</label>
                    <div class="form-group">
                        <div class="form-control form-control-sm" id="divUpDctoAprox">
                            <?php echo $dctomax ?>
                            <input type="number" id="numUpDctoAprox" name="numUpDctoAprox" value="<?php echo $dctomax ?>" hidden>
                            <input type="number" name="numUpTipoEmbargo" value="<?php echo $tipembactual ?>" hidden>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <label class="small" for="numUpTotEmbargo">Total Embargo</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="numUpTotEmbargo" name="numUpTotEmbargo" min="1" value="<?php echo $valtotal ?>" placeholder="Valor total">
                    </div>
                    <div id="enumUpTotEmbargo" class="invalid-tooltip">
                        Campo obligatorio
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-3">
                    <label class="small" for="txtUpValEmbargoMes"> Valor Embargo Mensual</label>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm" id="txtUpValEmbargoMes" name="txtUpValEmbargoMes" min="1" value="<?php echo $valmes ?>" placeholder="Valor mes">
                    </div>
                    <div id="etxtUpValEmbargoMes" class="invalid-tooltip">
                        Campo obligatorio
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="txtUpPorcEmbMes"> % Embargo Mes</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="txtUpPorcEmbMes" name="txtUpPorcEmbMes" value="<?php echo $porcent * 100 ?>" placeholder="Ej: 5.2">
                    </div>
                    <div id="etxtUpPorcEmbMes" class="invalid-tooltip">
                        Campo obligatorio
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="datUpFecInicioEmb">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecInicioEmb" name="datUpFecInicioEmb" value="<?php echo $fecinemb ?>">
                        <div id="edatUpFecInicioEmb" class="invalid-tooltip">
                            Inicio debe ser menor
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="datUpFecFinEmb">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecFinEmb" name="datUpFecFinEmb" value="<?php echo $fecfinemb ?>">
                        <div id="edatUpFecFinEmb" class="invalid-tooltip">
                            Fin debe ser mayor
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarEmb">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>