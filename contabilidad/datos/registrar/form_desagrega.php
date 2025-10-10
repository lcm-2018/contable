<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id = $_POST['id'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_pgcp`,`cuenta`, `nombre`, `desagrega`
            FROM `ctb_pgcp`
            WHERE `id_pgcp` = $id";
    $rs = $cmd->query($sql);
    $cuenta = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">DESAGREGACIÓN DE TERCEROS</h5>
        </div>
        <form id="formDesagregacion" class="px-3">
            <input type="hidden" name="id_pgcp" id="id_pgcp" value="<?php echo $cuenta['id_pgcp']; ?>">
            <input type="hidden" name="cuenta" id="cuenta" value="<?php echo $cuenta['cuenta']; ?>">
            <div class="form-row px-12 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtCuenta" class="small">CÓDIGO - CUENTA</label>
                    <input type="text" name="txtCuenta" id="txtCuenta" class="form-control form-control-sm" value="<?php echo $cuenta['cuenta'] . ' - ' . $cuenta['nombre']; ?>" readonly disabled>
                </div>
            </div>
            <div class="form-row px-12">
                <div class="form-group col-md-8 text-center mt-1">
                    <label class="small d-block" for="aplica">Aplica A</label>
                    <div class="form-control-sm border rounded px-2 py-1">
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="aplicacion" id="aplica_uno" value="1" checked>
                            <label class="form-check-label small" for="aplica_uno">Cuenta (Única)</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="aplicacion" id="aplica_todo" value="2">
                            <label class="form-check-label small" for="aplica_todo">Grupo (Inicia por)</label>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-4 text-center mt-1">
                    <label class="small d-block" for="marca_si">Marca</label>
                    <div class="form-control-sm border rounded px-2 py-1">
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="marca" id="marca_si" value="1" checked>
                            <label class="form-check-label small" for="marca_si">Si</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="marca" id="marca_no" value="0">
                            <label class="form-check-label small" for="marca_no">No</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="text-center py-3">
        <button class="btn btn-primary btn-sm" onclick="GuardarDesagregacion()">Guardar</button>
        <button type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</button>
    </div>
</div>