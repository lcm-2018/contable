<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


$id_componente = isset($_POST['id_componente']) ? $_POST['id_componente'] : -1;
$id_hv = isset($_POST['id_hv']) ? $_POST['id_hv'] : -1;
$sql = "SELECT * FROM acf_hojavida_componente WHERE id_componente=" . $id_componente . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR COMPONENTE</h7>
        </div>
        <div class="px-2">
            <form id="frm_reg_componente">
                <input type="hidden" id="id_componente" name="id_componente" value="<?php echo $id_componente ?>">
                <input type="hidden" id="id_hv" name="id_hv" value="<?php echo $id_componente ?>">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="id_articulo" class="small">Artículo</label>
                        <select class="form-control form-control-sm" id="id_articulo" name="id_articulo">
                        <?php articulosActivosFijos($cmd, '', $obj['id_articulo']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="serial" class="small">Serial</label>
                        <input type="text" class="form-control form-control-sm" id="serial" name="serial" required value="<?php echo $obj['serial'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="id_marca" class="small">Marca</label>
                        <select class="form-control form-control-sm" id="id_marca" name="id_marca">
                            <?php marcas($cmd, '', $obj['id_marca']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="modelo" class="small">Modelo</label>
                        <input type="text" class="form-control form-control-sm" id="modelo" name="modelo" required value="<?php echo $obj['modelo'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_componente">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>