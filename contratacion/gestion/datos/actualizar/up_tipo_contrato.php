<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$idtc = isset($_POST['idtc']) ? $_POST['idtc'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM tb_tipo_contratacion WHERE id_tipo = '$idtc'";
    $rs = $cmd->query($sql);
    $tcontrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

if (!empty($tcontrato)) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT * FROM tb_tipo_compra ORDER BY tipo_compra ASC";
        $rs = $cmd->query($sql);
        $tcompra = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $error = "Debe diligenciar este campo";
?>
    <div class="px-0">
        <div class="shadow">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h5 style="color: white;">ACTUALIZAR DATOS DE TIPO DE CONTRATO</h5>
            </div>
            <form id="formActualizaTipoContrato">
                <input type="number" id="idTipoContrato" name="idTipoContrato" value="<?php echo $tcontrato['id_tipo'] ?>" hidden>
                <div class="form-row px-4 pt-2">
                    <div class="form-group col-md-4">
                        <label for="slcTipoCompra" class="small">TIPO DE COMPRA</label>
                        <select id="slcTipoCompra" name="slcTipoCompra" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                            <option value="0">-- Seleccionar --</option>
                            <?php
                            foreach ($tcompra as $tc) {
                                if ($tc['id_tipo'] !== $tcontrato['id_tipo_compra']) {
                                    echo '<option value="' . $tc['id_tipo'] . '">' . $tc['tipo_compra'] . '</option>';
                                } else {
                                    echo '<option selected value="' . $tc['id_tipo'] . '">' . $tc['tipo_compra'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label for="txtTipoContrato" class="small">NOMBRE TIPO DE CONTRATO</label>
                        <input id="txtTipoContrato" type="text" name="txtTipoContrato" class="form-control form-control-sm py-0 sm" aria-label="Default select example" value="<?php echo $tcontrato['tipo_contrato'] ?>">
                    </div>
                    <div class="text-center pb-3">
                        <button class="btn btn-primary btn-sm" id="btnUpTipoContrato">Actualizar</button>
                        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
} else {
    echo 'Error al intentar obtener datos';
} ?>