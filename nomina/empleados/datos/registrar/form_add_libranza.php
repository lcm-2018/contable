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
    $sql = "SELECT * 
            FROM tb_bancos
            ORDER BY nom_banco ASC";
    $rs = $cmd->query($sql);
    $banco = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR LIBRANZA</h5>
        </div>
        <form id="formAddLibranza">
            <input type="number" id="idEmpLibranza" name="idEmpLibranza" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" for="slcEntidad">Entidad financiera</label>
                    <select id="slcEntidad" name="slcEntidad" class="form-control form-control-sm py-0" aria-label="Default select example">
                        <option selected value="0">--Selecionar Entidad--</option>
                        <?php
                        foreach ($banco as $b) {
                            echo '<option value="' . $b['id_banco'] . '">' . $b['nom_banco'] . '</option>';
                        }
                        ?>
                    </select>
                    <div id="eslcEntidad" class="invalid-tooltip">
                        <?php echo 'Diligenciar este campo' ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="numValTotal">Valor Total</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="numValTotal" name="numValTotal" min="1" placeholder="Total libranza">
                    </div>
                    <div id="enumValTotal" class="invalid-tooltip">
                        <?php echo 'Diligenciar este campo' ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="numTotCuotasLib">Cuotas Totales</label>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm" id="numTotCuotasLib" name="numTotCuotasLib" min="1" placeholder="Cant. de cuotas">
                    </div>
                    <div id="enumTotCuotasLib" class="invalid-tooltip">
                        <?php echo 'Debe ser mayor a 0' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label class="small" for="txtDescripLib">Descripción</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="txtDescripLib" name="txtDescripLib" placeholder="Descripción de la libranza">
                        <div id="etxtDescripLib" class="invalid-tooltip">
                            <?php echo 'Campo Obligatorio' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-3">
                    <label class="small" for="txtValLibMes">Valor mes</label>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-sm" id="txtValLibMes" name="txtValLibMes" placeholder="Cuota mensual">
                    </div>
                    <div id="etxtValLibMes" class="invalid-tooltip">
                        <?php echo 'Campo Obligatorio' ?>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="txtPorcLibMes">Porcentaje %</label>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm" id="txtPorcLibMes" name="txtPorcLibMes" placeholder="Ej: 10.5">
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="datFecInicioLib">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioLib" name="datFecInicioLib" value="<?php echo date('Y-m-d') ?>">
                        <div id="edatFecInicioLib" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" for="datFecFinLib">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinLib" name="datFecFinLib" value="<?php echo date('Y') ?>-12-31">
                        <div id="edatFecFinLib" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddLibranza">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>