<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`anio`)
            FROM
                `tb_vigencias`";
    $rs = $cmd->query($sql);
    $vigencias = $rs->fetch(PDO::FETCH_ASSOC);
    $vigencia = !empty($vigencias) ? $vigencias['MAX(`anio`)'] + 1 : 2023;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR VIGENCIA</h5>
        </div>
        <div class="px-2">
            <form id="formRegConcepXvig">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="vigencia" class="small">VIGENCIA</label>
                        <input type="number" class="form-control form-control-sm" id="vigencia" name="vigencia" value="<?php echo $vigencia ?>" readonly>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnRegVigencia">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>