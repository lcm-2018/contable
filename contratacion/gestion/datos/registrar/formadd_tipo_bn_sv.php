<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$key = array_search('53', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$error = "Debe diligenciar este campo";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT tb_tipo_contratacion.id_tipo, tipo_compra, tipo_contrato
            FROM
                tb_tipo_contratacion
            INNER JOIN tb_tipo_compra 
                ON (tb_tipo_contratacion.id_tipo_compra = tb_tipo_compra.id_tipo)
            ORDER BY tipo_compra, tipo_contrato";
    $rs = $cmd->query($sql);
    $tcontrato = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR TIPO DE BIEN O SERVICIO</h5>
        </div>
        <form id="formAddTipoBnSv">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="slcTipoContrato" class="small">TIPO DE CONTRATO</label>
                    <select id="slcTipoContrato" name="slcTipoContrato" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($tcontrato as $tc) {
                            echo '<option value="' . $tc['id_tipo'] . '">' . $tc['tipo_compra'] . ' || ' . $tc['tipo_contrato'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-8">
                    <label for="txtTipoBnSv" class="small">NOMBRE TIPO DE BIEN O SERVICIO</label>
                    <input id="txtTipoBnSv" type="text" name="txtTipoBnSv" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtObjPre" class="small">OBJETO PREDEFINIDO</label>
                    <textarea id="txtObjPre" type="text" name="txtObjPre" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" placeholder="Objeto predefinido del contrato"></textarea>
                </div>
            </div>
            <div class="text-center py-3">
                <button class="btn btn-primary btn-sm" id="btnAddTipoBnSv">Agregar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>