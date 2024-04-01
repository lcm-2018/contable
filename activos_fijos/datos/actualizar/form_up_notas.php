<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_nota = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tipo_nota`,`descripcion` FROM `seg_tipo_notas_acfijo`";
    $rs = $cmd->query($sql);
    $tnotas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_nota`, `id_tipo_n`, `fecha_n`, `valor`, `observacion`
            FROM `seg_notas_acfijo` WHERE `id_nota` = '$id_nota'";
    $rs = $cmd->query($sql);
    $nota = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR NOTA DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="formUpNotasAcFijo">
                <input name="id_nota" hidden value="<?php echo $id_nota ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="slcNota" class="small">tipo de nota</label>
                        <select name="slcNota" id="slcNota" class="form-control form-control-sm">
                            <?php
                            foreach ($tnotas as $nt) {
                                if ($nt['id_tipo_nota'] == $nota['id_tipo_n']) {
                                    echo '<option value="' . $nt['id_tipo_nota'] . '" selected>' . $nt['descripcion'] . '</option>';
                                } else {
                                    echo '<option value="' . $nt['id_tipo_nota'] . '">' . $nt['descripcion'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fecNota" class="small">fecha de nota</label>
                        <input type="date" name="fecNota" id="fecNota" class="form-control form-control-sm" value="<?php echo $nota['fecha_n'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="numValNota" class="small">valor de nota</label>
                        <input type="number" name="numValNota" id="numValNota" class="form-control form-control-sm" value="<?php echo $nota['valor'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaNota" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaNota" name="txtObservaNota" rows="3"><?php echo $nota['observacion'] ?></textarea>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnUpNotaActFijo" type="button" class="btn btn-primary btn-sm">Actualizar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>