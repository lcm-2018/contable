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

$id_med = isset($_POST['id_med']) ? $_POST['id_med'] : -1;
$id = isset($_POST['id']) ? $_POST['id'] : -1;

$sql = "SELECT acf_hojavida_componentes.*,far_medicamentos.nom_medicamento AS nom_articulo
        FROM acf_hojavida_componentes 
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med = acf_hojavida_componentes.id_articulo)
        WHERE id_componente=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;    

    $articulo = datos_articulo($cmd, $id_med);
    $obj['id_articulo'] = $articulo['id_med'];
    $obj['nom_articulo'] = $articulo['nom_articulo'];
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR COMPONENTE</h7>
        </div>
        <div class="px-2">
            <form id="frm_reg_componente">
                <input type="hidden" id="id_componente" name="id_componente" value="<?php echo $id ?>">

                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="txt_nom_art" class="small">Articulo Componente</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" class="small" value="<?php echo $obj['nom_articulo'] ?>" readonly="readonly">
                        <input type="hidden" id="id_txt_nom_art" name="id_txt_nom_art" value="<?php echo $obj['id_articulo'] ?>">
                    </div>   
                    <div class="form-group col-md-4">
                        <label for="txt_num_serial" class="small">No. Serial</label>
                        <input type="text" class="form-control form-control-sm" id="txt_num_serial" name="txt_num_serial" value="<?php echo $obj['num_serial'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_marca" class="small">Marca</label>
                        <select class="form-control form-control-sm" id="sl_marca" name="sl_marca">
                            <?php marcas($cmd, '', $obj['id_marca']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txt_modelo" class="small">Modelo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_modelo" name="txt_modelo" value="<?php echo $obj['modelo'] ?>">
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