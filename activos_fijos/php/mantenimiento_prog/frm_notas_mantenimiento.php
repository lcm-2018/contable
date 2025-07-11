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

$id_md = isset($_POST['id_md']) ? $_POST['id_md'] : -1;

$sql = "SELECT HV.placa,HV.num_serial,FM.nom_medicamento AS nom_articulo,HV.des_activo
        FROM acf_mantenimiento_detalle AS MD
        INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo=MD.id_activo_fijo)
        INNER JOIN far_medicamentos FM ON (FM.id_med=HV.id_articulo)
        WHERE MD.id_mant_detalle=" . $id_md . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">NOTAS DE MANTENIMIENTO</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_notas">
                <input type="hidden" id="id_mant_detalle" name="id_mant_detalle" value="<?php echo $id_md ?>">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label class="small">Placa</label>
                        <input type="text" class="form-control form-control-sm" id="txt_placa_nt" value="<?php echo $obj['placa'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small">Articulo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_articulo_nt" value="<?php echo $obj['nom_articulo'] ?> " readonly="readonly">
                    </div>                    
                    <div class="form-group col-md-5">
                        <label class="small">Nombre del Activo Fijo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_activo_nt" value="<?php echo $obj['des_activo'] ?> " readonly="readonly">
                    </div>                    
                    <div class="form-group col-md-2">
                        <label class="small">No. Serial</label>
                        <input type="text" class="form-control form-control-sm" id="txt_serial_nt" value="<?php echo $obj['num_serial'] ?>" readonly="readonly">
                    </div>
                </div>
            </form> 
            <!--Formulario de registro de Ordenes de Ingreso--> 
            <table id="tb_notas_mantenimiento" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Observacion</th>
                        <th>Archivo Documento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir">Imprimir</button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Salir</a>
    </div>   
</div>

<script type="text/javascript" src="../../js/mantenimiento_prog/mantenimiento_notas_reg.js?v=<?php echo date('YmdHis') ?>"></script>