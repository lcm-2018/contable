<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_dcto`,`id_empleado`,`id_tipo_dcto`,`fecha`,`fecha_fin`,`concepto`,`valor`
            FROM `nom_otros_descuentos`
            WHERE `id_dcto` = $id";
    $rs = $cmd->query($sql);
    $descuento = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
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
            <h5 style="color: white;">ACTUALIZAR DESCUENTO(OTRO)</h5>
        </div>
        <form id="formAddOtroDcto">
            <input type="hidden" name="id_dcto" value="<?= $descuento['id_dcto'] ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecDcto">Fecha Inicia</label>
                    <input type="date" class="form-control form-control-sm" id="datFecDcto" name="datFecDcto" value="<?= $descuento['fecha'] ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datFecFinDcto">Fecha Termina</label>
                    <input type="date" class="form-control form-control-sm" id="datFecFinDcto" name="datFecFinDcto" value="<?= $descuento['fecha_fin'] ?>">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" for="sclTipoDcto">Tipo Dcto</label>
                    <select type="date" class="form-control form-control-sm" id="sclTipoDcto" name="sclTipoDcto" value="<?= $descuento['valor'] ?>">
                        <option value="0">--Seleccionar--</option>
                        <?php
                        if (!empty($tdcto)) {
                            foreach ($tdcto as $td) {
                                $slc = $td['id_tipo'] == $descuento['id_tipo_dcto'] ? 'selected' : '';
                                echo '<option value="' . $td['id_tipo'] . '" ' . $slc . '>' . $td['descripcion'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label class="small text-right" for="numValDcto">Valor</label>
                    <input type="number" class="form-control form-control-sm" id="numValDcto" name="numValDcto" value="<?= $descuento['valor'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label class="small" for="datFecInicioIncap">Descripción del descuento</label>
                    <textarea class="form-control form-control-sm" id="txtConDcto" name="txtConDcto" rows="3"><?= $descuento['concepto'] ?></textarea>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddOtroDcto" text="2">Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>