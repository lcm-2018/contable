<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT * FROM far_orden_ingreso_tipo WHERE id_tipo_ingreso=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if(empty($obj)){
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++):
        $col = $rs->getColumnMeta($i);
        $name=$col['name'];
        $obj[$name]=NULL;
    endfor;    
    //Inicializa variable por defecto
    $obj['es_int_ext'] = 0;
    $obj['orden_compra'] = 0;
    $obj['fianza'] = 0;
    $obj['farmacia'] = 1;    
    $obj['almacen'] = 1;    
    $obj['activofijo'] = 1;    
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR TIPO DE ORDEN DE INGRESO</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_tipos_orden_ingreso">
                <input type="hidden" id="id_tipo_ingreso" name="id_tipo_ingreso" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="txt_nom_tipoingreso" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_tipoingreso" name="txt_nom_tipoingreso" required value="<?php echo $obj['nom_tipo_ingreso'] ?>">
                    </div>    
                    <div class="form-group col-md-2">
                        <label for="sl_esintext" class="small">Interno/Externo</label>
                        <select class="form-control form-control-sm" id="sl_esintext" name="sl_esintext">
                            <?php interno_externo('',$obj['es_int_ext']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_ordencompra" class="small">Con Orden Compra</label>
                        <select class="form-control form-control-sm" id="sl_ordencompra" name="sl_ordencompra">
                            <?php estados_sino('',$obj['orden_compra']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_fianza" class="small">Es Fianza</label>
                        <select class="form-control form-control-sm" id="sl_fianza" name="sl_fianza">
                            <?php estados_sino('',$obj['fianza']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_almacen" class="small">Mod. Almacen</label>
                        <select class="form-control form-control-sm" id="sl_almacen" name="sl_almacen">
                            <?php estados_sino('',$obj['almacen']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_farmacia" class="small">Mod. Farmacia</label>
                        <select class="form-control form-control-sm" id="sl_farmacia" name="sl_farmacia">
                            <?php estados_sino('',$obj['farmacia']) ?>
                        </select>
                    </div>                    
                    <div class="form-group col-md-2">
                        <label for="sl_activofijo" class="small">Mod. Activos Fijos</label>
                        <select class="form-control form-control-sm" id="sl_activofijo" name="sl_activofijo">
                            <?php estados_sino('',$obj['activofijo']) ?>
                        </select>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/tipos_orden_ingreso/tipos_orden_ingreso_reg.js?v=<?php echo date('YmdHis') ?>"></script>