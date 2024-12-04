<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
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
    $sql = "SELECT 
            id_tipo_b_s, tipo_compra, tipo_contrato, tipo_bn_sv
        FROM
            tb_tipo_bien_servicio
        INNER JOIN tb_tipo_contratacion 
            ON (tb_tipo_bien_servicio.id_tipo_cotrato = tb_tipo_contratacion.id_tipo)
        INNER JOIN tb_tipo_compra 
            ON (tb_tipo_contratacion.id_tipo_compra = tb_tipo_compra.id_tipo)
        ORDER BY tipo_compra, tipo_contrato, tipo_bn_sv";
    $rs = $cmd->query($sql);
    $tbnsv = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR BIEN O SERVICIO</h5>
        </div>
        <form id="formAddBnSv">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="slcTipoBnSv" class="small">TIPO DE BIEN O SERVICIO</label>
                    <select id="slcTipoBnSv" name="slcTipoBnSv" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($tbnsv as $tbs) {
                            echo '<option value="' . $tbs['id_tipo_b_s'] . '">' . $tbs['tipo_compra'] . ' || ' . $tbs['tipo_contrato'] . ' || ' . $tbs['tipo_bn_sv'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row px-4">
                <div class="col-md-1 offset-11" id="celdaPR">
                    <button class="btn btn-success btn-circle shadow-gb btn_addBnSv"><span class="fas fa-plus fa-lg"></span></button>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="txtBnSv" class="small">NOMBRE DE BIEN O SERVICIO</label>
                    <input id="txtBnSv" type="text" name="txtBnSv[]" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                </div>
            </div>
            <div id="content_inputs" class="px-4">

            </div>
            <div>
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddBnSv">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>