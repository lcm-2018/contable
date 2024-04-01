<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$tipo = isset($_POST['tip_eaf']) ? $_POST['tip_eaf'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `descripcion` FROM  `seg_tipo_entrada` WHERE `id_entrada` = $tipo";
    $rs = $cmd->query($sql);
    $tentradas = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipol = $tentradas['descripcion'];

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR ENTRADA DE ACTIVOS FIJOS POR <?php echo $tipol ?></h5>
        </div>
        <div class="px-2">
            <form id="formRegEntraActFijoDO">
                <input name="tipoEntrada" hidden value="<?php echo $tipo ?>">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="ccnit" class="small">TERCECRO</label>
                        <input type="text" id="compleTerecero" class="form-control form-control-sm">
                        <input type="hidden" id="id_tercero_pd" name="id_tercero_pd" value="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="numActaRem" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRem" name="numActaRem" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecActRem" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRem" name="fecActRem" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaActFijo" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaActFijo" name="txtObservaActFijo" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button id="regEntraActFijoDO" type="button" class="btn btn-primary btn-sm">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>