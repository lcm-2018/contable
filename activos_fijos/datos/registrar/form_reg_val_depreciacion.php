<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `codigo`, `nom_mes` FROM `nom_meses`";
    $rs = $cmd->query($sql);
    $meses = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CALCULAR DEPRECIACIÓN MES</h5>
        </div>
        <div class="px-2">
            <form id="formRegCalcDeprec">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="slcMesDp" class="small">MES</label>
                        <select type="text" id="slcMesDp" name="slcMesDp" class="form-control form-control-sm">
                            <option value="0">--Seleccionar--</option>
                            <?php
                            foreach ($meses as $m) {
                                echo '<option value="' . $m['codigo'] . '">' . $m['nom_mes'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnRegCalcDepreciacion" class="btn btn-primary btn-sm">Calcular y Registrar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>