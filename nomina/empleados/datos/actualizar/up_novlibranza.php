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
                    nom_libranzas
                WHERE id_libranza = '$id'";
    $rs = $cmd->query($sql);
    $libranza = $rs->fetch();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM tb_bancos ORDER BY nom_banco ASC";
    $rs = $cmd->query($sql);
    $bancos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR LIBRANZA</h5>
        </div>
        <form id="formUpLibranza">
            <input type="number" name="numidLibranza" value="<?php echo $libranza['id_libranza'] ?>" hidden="true">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" for="slcUpEntidad">Entidad Financiera</label>
                    <select id="slcUpEntidad" name="slcUpEntidad" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <?php
                        $valtotal = $libranza['valor_total'];
                        $cuotas = $libranza['cuotas'];
                        $desclib = $libranza['descripcion_lib'];
                        $valmes = $libranza['val_mes'];
                        $porcen = $libranza['porcentaje'] * 100;
                        $fecin = $libranza['fecha_inicio'];
                        $fecfin = $libranza['fecha_fin'];
                        $idlibranza = $libranza['id_libranza'];
                        $bancoactual = $libranza['id_banco'];
                        foreach ($bancos as $b) {
                            if ($b['id_banco'] !== $bancoactual) {
                                echo '<option value="' . $b['id_banco'] . '">' . $b['nom_banco'] . '</option>';
                            } else {
                                echo '<option selected value="' . $b['id_banco'] . '">' . $b['nom_banco'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="numUpValTotal">Valor Total</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="numUpValTotal" name="numUpValTotal" value="<?php echo $valtotal ?>" min="1" placeholder="Total libranza">
                    </div>
                    <div id="enumUpValTotal" class="invalid-tooltip">
                        Diligenciar este campo
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="numUpTotCuotasLib">Cuotas Totales</label>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm" id="numUpTotCuotasLib" name="numUpTotCuotasLib" value="<?php echo $cuotas ?>" min="1" placeholder="Cant. de cuotas">
                    </div>
                    <div id="enumUpTotCuotasLib" class="invalid-tooltip">
                        Debe ser mayor a 0
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label class="small" for="txtUpDescripLib">Descripción</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="txtUpDescripLib" name="txtUpDescripLib" value="<?php echo $desclib ?>" placeholder="Descripción de la libranza">
                        <div id="etxtUpDescripLib" class="invalid-tooltip">
                            Campo Obligatorio
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-3">
                    <label class="small" for="txtUpValLibMes">Valor mes</label>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm" id="txtUpValLibMes" name="txtUpValLibMes" value="<?php echo  $valmes ?>" placeholder="Cuota mensual">
                    </div>
                    <div id="etxtUpValLibMes" class="invalid-tooltip">
                        Campo Obligatorio
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="txtUpPorcLibMes">Porcentaje %</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="txtUpPorcLibMes" name="txtUpPorcLibMes" value="<?php echo $porcen ?>" placeholder="Ej: 10.5">
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="datUpFecInicioLib">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecInicioLib" name="datUpFecInicioLib" value="<?php echo $fecin ?>">
                        <div id="edatUpFecInicioLib" class="invalid-tooltip">
                            Inicio debe ser menor
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="datUpFecFinLib">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecFinLib" name="datUpFecFinLib" value="<?php echo $fecfin ?>">
                        <div id="edatUpFecFinLib" class="invalid-tooltip">
                            Fin debe ser mayor
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarLib">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>