<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_serie = isset($_POST['id_ser']) ? $_POST['id_ser'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tipo_nota`,`descripcion` FROM `seg_tipo_notas_acfijo`";
    $rs = $cmd->query($sql);
    $notas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR NOTAS DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="formRegNotasAcFijo">
                <input name="id_serie_acfijo" hidden value="<?php echo $id_serie ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="slcNota" class="small">tipo de nota</label>
                        <select name="slcNota" id="slcNota" class="form-control form-control-sm">
                            <option value="0">--Seleccione--</option>
                            <?php
                            foreach ($notas as $nt) {
                                echo "<option value='$nt[id_tipo_nota]'>$nt[descripcion]</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fecNota" class="small">fecha de nota</label>
                        <input type="date" name="fecNota" id="fecNota" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="numValNota" class="small">valor de nota</label>
                        <input type="number" name="numValNota" id="numValNota" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaNota" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaNota" name="txtObservaNota" rows="3"></textarea>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnRegNotaActFijo" type="button" class="btn btn-primary btn-sm">Registrar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>