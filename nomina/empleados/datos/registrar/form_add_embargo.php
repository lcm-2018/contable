<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}

include '../../../../conexion.php';
$idemp  = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM nom_juzgados ORDER BY municipio";
    $rs = $cmd->query($sql);
    $juzgado = $rs->fetchAll();
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
        <form id="formAddEmbargo">
            <input type="number" id="idEmpEmbargo" name="idEmpEmbargo" value="<?php echo $idemp ?>" hidden>
            <div class="form-row pt-2 px-4">
                <div class="form-group col-md-4">
                    <label class="small" for="slcJuzgado">Juzgado</label>
                    <select id="slcJuzgado" name="slcJuzgado" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar Juzgado--</option>
                        <?php
                        foreach ($juzgado as $j) {
                            echo '<option value="' . $j['id_juzgado'] . '">' . $j['nom_juzgado'] . '</option>';
                        }
                        ?>
                    </select>
                    <div id="eslcJuzgado" class="invalid-tooltip">
                        <?php echo 'Diligenciar este campo' ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="numTipoEmbargo">Tipo</label>
                    <select id="slcTipoEmbargo" name="slcTipoEmbargo" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar tipo--</option>
                        <?php
                        foreach ($tipoembargo as $tpe) {
                            echo '<option value="te=' . $tpe['id_tipo_emb'] . '&ie=' . $idemp . '">' . mb_strtoupper($tpe['tipo']) . '</option>';
                        }
                        ?>
                    </select>
                    <div id="eslcTipoEmbargo" class="invalid-tooltip">
                        <?php echo 'Diligenciar este campo' ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="numDctoAprox">Dcto. Máximo</label>
                    <div class="form-group">
                        <div class="form-control form-control-sm" id="divDctoAprox">
                            0
                            <input type="number" id="numDctoAprox" name="numDctoAprox" value="0" hidden>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-2">
                    <label class="small" for="numTotEmbargo">Total Embargo</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="numTotEmbargo" name="numTotEmbargo" min="1" placeholder="Valor total">
                    </div>
                    <div id="enumTotEmbargo" class="invalid-tooltip">
                        <?php echo 'Campo obligatorio' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-3">
                    <label class="small" for="txtValEmbargoMes"> Valor Embargo Mensual</label>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm" id="txtValEmbargoMes" name="txtValEmbargoMes" min="1" placeholder="Valor mes">
                    </div>
                    <div id="etxtValEmbargoMes" class="invalid-tooltip">
                        <?php echo 'Campo obligatorio' ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="txtPorcEmbMes"> % Embargo Mes</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="txtPorcEmbMes" name="txtPorcEmbMes" placeholder="Ej: 5.2">
                    </div>
                    <div id="enumValEmbargoMes" class="invalid-tooltip">
                        <?php echo 'Campo obligatorio' ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="datFecInicioEmb">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioEmb" name="datFecInicioEmb" value="<?php echo date('Y-m-d') ?>">
                        <div id="edatFecInicioEmb" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="datFecFinEmb">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinEmb" name="datFecFinEmb" value="<?php echo date('Y') ?>-12-31">
                        <div id="edatFecFinEmb" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <input type="text" name="txtEstadoEmbargo" value="1" hidden>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddEmbargo">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>