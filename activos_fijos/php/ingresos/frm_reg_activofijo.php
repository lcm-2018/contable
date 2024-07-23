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

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT acf_orden_ingreso_detalle.id_articulo,
            far_medicamentos.cod_medicamento,
            far_medicamentos.nom_medicamento,
            acf_orden_ingreso_detalle.cantidad,
            acf_orden_ingreso_detalle.valor,
            acf_orden_ingreso_detalle.observacion
        FROM acf_orden_ingreso_detalle 
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med = acf_orden_ingreso_detalle.id_articulo)
        WHERE acf_orden_ingreso_detalle.id_ing_detalle=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR DATOS BÁSICOS DE ACTIVOS FIJOS</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de Ordenes de Ingreso-->
            <form id="acf_reg_orden_ingreso">
                <input type="hidden" id="id_ing_detalle" name="id_ing_detalle" value="<?php echo $id ?>">
                <input type="hidden" id="id_articulo" name="id_articulo" value="<?php echo $obj['id_articulo'] ?>">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label for="txt_cod_med" class="small">Codigo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_cod_med" class="small" value="<?php echo $obj['cod_medicamento'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txt_desc_med" class="small">Descripción</label>
                        <input type="text" class="form-control form-control-sm" id="txt_desc_med" class="small" value="<?php echo $obj['nom_medicamento'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-1">
                        <label for="txt_cdd_med" class="small">Cantidad</label>
                        <input type="text" class="form-control form-control-sm" id="txt_cdd_med" class="small" value="<?php echo $obj['cantidad'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_vrunit_med" class="small">Vr. Unitario</label>
                        <input type="text" class="form-control form-control-sm" id="txt_vrunit_med" class="small" value="<?php echo $obj['valor'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_observacion" class="small">Observaciones</label>
                        <input type="text" class="form-control form-control-sm" id="txt_observacion" class="small" value="<?php echo $obj['observacion'] ?>" readonly="readonly">
                    </div>
                </div>
            </form>    
            <table id="tb_lista_activos_fijos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Placa</th>
                        <th>Serial</th>
                        <th>Marca</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3 rigth">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Salir</a>
    </div>    
</div>

<script type="text/javascript" src="../../js/ingresos/activofijo_reg.js?v=<?php echo date('YmdHis') ?>"></script>