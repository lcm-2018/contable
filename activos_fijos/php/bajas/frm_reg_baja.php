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

$id = isset($_POST['id_baja']) ? $_POST['id_baja'] : -1;
$sql = "SELECT 
            id_baja,
            observaciones,
            fecha_orden,
            hora_orden,
            CASE estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS estado
        FROM acf_baja
        WHERE id_baja=" . $id . " LIMIT 1";
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
    //Inicializa variable por defecto
    $obj['estado'] = 'PENDIENTE';
    $obj['val_total'] = 0;

    $fecha = fecha_hora_servidor();
    $obj['fecha_orden'] = $fecha['fecha'];
    $obj['hora_orden'] = $fecha['hora'];
}
$guardar = in_array($obj['estado'],['PENDIENTE']) ? '' : 'disabled="disabled"';
$aprobado = '';
$ejecucion = '';
$imprimir = $id != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR BAJA ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_baja">
                <input type="hidden" id="id_baja" name="id_baja" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label for="id_baja" class="small">Id.</label>
                        <input type="text" class="form-control form-control-sm" id="txt_id_baja" name="txt_id_baja" class="small" value="<?php echo ($id==-1?'':$id) ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_ing" class="small">Fecha</label>
                        <input type="text" class="form-control form-control-sm" id="fecha_orden" name="fecha_orden" class="small" value="<?php echo $obj['fecha_orden'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_hor_ing" class="small">Hora</label>
                        <input type="text" class="form-control form-control-sm" id="hora_orden" name="hora_orden" class="small" value="<?php echo $obj['hora_orden'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="estado" class="small">Estado</label>
                        <input type="text" class="form-control form-control-sm" id="estado" name="estado" class="small" value="<?php echo $obj['estado'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-12">
                    <label for="txt_det_ing" class="small">Observaciones</label>                   
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2"><?php echo $obj['observaciones'] ?></textarea>
                    </div>
                </div>
            </form>    
            <table id="tb_baja_detalles" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Articulo</th>
                        <th>Placa</th>
                        <th>Observacion Baja</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar" <?php echo $guardar ?>>Guardar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_cerrar" <?php echo $aprobado ?>>Cerrar</button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/bajas/bajas_reg.js?v=<?php echo date('YmdHis') ?>"></script>