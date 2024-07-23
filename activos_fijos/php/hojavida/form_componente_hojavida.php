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

$id_hv = isset($_POST['id_hv']) ? $_POST['id_hv'] : -1;

$sql = "SELECT 
            HV.id,
            HV.placa,
            HV.serial,
            HV.id_articulo,
            FM.nom_medicamento nom_articulo
        FROM acf_hojavida HV
            INNER JOIN far_medicamentos FM ON FM.id_med = HV.id_articulo
        WHERE HV.id=" . $id_hv . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if ($obj === false) {
    $obj = array(); // Inicializa $obj como un array vacío
}

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;

}
$guardar = '';
$imprimir = '';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR COMPONENTE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de Ordenes de Ingreso-->
            <form id="frm_reg_ingresos">
                <input type="hidden" id="id_hv" name="id_hv" value="<?php echo $id_hv ?>">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="txt_num_fac" class="small">Articulo</label>
                        <input type="text" class="form-control form-control-sm" id="nom_articulo" name="nom_articulo" class="small" value="<?php echo $obj['nom_articulo'] ?> " readonly="readonly">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txt_num_fac" class="small">Placa</label>
                        <input type="text" class="form-control form-control-sm" id="placa" name="placa" class="small" value="<?php echo $obj['placa'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txt_num_fac" class="small">Serial</label>
                        <input type="text" class="form-control form-control-sm" id="serial" name="serial" class="small" value="<?php echo $obj['serial'] ?>" readonly="readonly">
                    </div>
                </div>
            </form>    
            <table id="tb_componentes_activofijo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Articulo</th>
                        <th>Serial</th>
                        <th>Modelo</th>
                        <th>Marca</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>   
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar" <?php echo $guardar ?>>Guardar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir" <?php echo $imprimir ?>>Imprimir</button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/hojavida/hojavida_componente_reg.js?v=<?php echo date('YmdHis') ?>"></script>