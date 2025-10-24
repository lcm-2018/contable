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
$sql = "SELECT * FROM far_orden_egreso_tipo WHERE id_tipo_egreso=" . $id . " LIMIT 1";
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
    $obj['con_pedido'] = 0;
    $obj['dev_fianza'] = 0;
    $obj['consumo'] = -1;    
    $obj['farmacia'] = 1;    
    $obj['almacen'] = 1;    
    $obj['activofijo'] = 1;    
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR TIPO DE ORDEN DE EGRESO</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_tipos_orden_egreso">
                <input type="hidden" id="id_tipo_egreso" name="id_tipo_egreso" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="txt_nom_tipoegreso" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_tipoegreso" name="txt_nom_tipoegreso" required value="<?php echo $obj['nom_tipo_egreso'] ?>">
                    </div>    
                    <div class="form-group col-md-2">
                        <label for="sl_esintext" class="small">Interno/Externo</label>
                        <select class="form-control form-control-sm" id="sl_esintext" name="sl_esintext">
                            <?php interno_externo('',$obj['es_int_ext']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_conpedido" class="small">Con Pedido</label>
                        <select class="form-control form-control-sm" id="sl_conpedido" name="sl_conpedido">
                            <?php estados_sino('',$obj['con_pedido']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_devfianza" class="small">Es Dev. Fianza</label>
                        <select class="form-control form-control-sm" id="sl_devfianza" name="sl_devfianza">
                            <?php estados_sino('',$obj['dev_fianza']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_consumo" class="small">Es Consumo</label>
                        <select class="form-control form-control-sm" id="sl_consumo" name="sl_consumo">
                            <?php estados_sino('',$obj['consumo']) ?>
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

             <table id="tb_cuentas_c" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">    
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Cuenta Contable Gasto</th>
                        <th>Fecha Inicio de Vigencia</th>
                        <th>Cuenta Vigente</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>
 
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/tipos_orden_egreso/tipos_orden_egreso_reg.js?v=<?php echo date('YmdHis') ?>"></script>