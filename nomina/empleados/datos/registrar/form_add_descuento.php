<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include('../../../../conexion.php');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_tipo`,`descripcion`
            FROM `nom_tipo_descuentos`
            ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $tdcto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR DESCUENTO(OTRO)</h5>
        </div>
        <form id="formAddOtroDcto">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecDcto">Fecha Inicia</label>
                    <input type="date" class="form-control form-control-sm" id="datFecDcto" name="datFecDcto" value="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datFecFinDcto">Fecha Termina</label>
                    <input type="date" class="form-control form-control-sm" id="datFecFinDcto" name="datFecFinDcto">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" for="sclTipoDcto">Tipo Dcto</label>
                    <select type="date" class="form-control form-control-sm" id="sclTipoDcto" name="sclTipoDcto">
                        <option value="0">--Seleccionar--</option>
                        <?php
                        if (!empty($tdcto)) {
                            foreach ($tdcto as $td) {
                                echo '<option value="' . $td['id_tipo'] . '">' . $td['descripcion'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label class="small text-right" for="numValDcto">Valor</label>
                    <input type="number" class="form-control form-control-sm" id="numValDcto" name="numValDcto">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label class="small" for="datFecInicioIncap">Descripción del descuento</label>
                    <textarea class="form-control form-control-sm" id="txtConDcto" name="txtConDcto" rows="3"></textarea>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddOtroDcto" text="1">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>