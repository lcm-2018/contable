<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT acf_baja.*,                           
            CASE acf_baja.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS nom_estado 
        FROM acf_baja             
        WHERE id_baja=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
    //Inicializa variable por defecto    
    $obj['estado'] = 1;
    $obj['nom_estado'] = 'PENDIENTE';

    $fecha = fecha_hora_servidor();
    $obj['fec_orden'] = $fecha['fecha'];
    $obj['hor_orden'] = $fecha['hora'];
}
$guardar = in_array($obj['estado'],[1]) ? '' : 'disabled="disabled"';
$cerrar = in_array($obj['estado'],[1]) && $id != -1 ? '' : 'disabled="disabled"';
$anular = in_array($obj['estado'],[2]) ? '' : 'disabled="disabled"';
$imprimir = $id != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR BAJAS DE ACTIVOS FIJOS</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de baja-->
            <form id="frm_reg_bajas">
                <input type="hidden" id="id_baja" name="id_baja" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-1">
                        <label for="txt_ide" class="small">Id.</label>
                        <input type="text" class="form-control form-control-sm" id="txt_ide" name="txt_ide" class="small" value="<?php echo ($id==-1?'':$id) ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_fec_orden" class="small">Fecha baja</label>
                        <input type="text" class="form-control form-control-sm" id="txt_fec_orden" name="txt_fec_orden" class="small" value="<?php echo $obj['fec_orden'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_hor_orden" class="small">Hora baja</label>
                        <input type="text" class="form-control form-control-sm" id="txt_hor_orden" name="txt_hor_orden" class="small" value="<?php echo $obj['hor_orden'] ?>" readonly="readonly">
                    </div>                                      
                    <div class="form-group col-md-2">
                        <label for="txt_est_baja" class="small">Estado baja</label>
                        <input type="text" class="form-control form-control-sm" id="txt_est_baja" name="txt_est_baja" class="small" value="<?php echo $obj['nom_estado'] ?>" readonly="readonly">
                    </div>
                </div>    
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="txt_obs_baja" class="small">Observaciones</label>
                        <textarea class="form-control" id="txt_obs_baja" name="txt_obs_baja" rows="2"><?php echo $obj['observaciones'] ?></textarea>
                    </div>                      
                </div>
            </form>    
            <table id="tb_bajas_detalles" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Placa</th>
                        <th>Articulo</th>
                        <th>Estado General</th>
                        <th>Observación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>                 
                <tbody class="text-left centro-vertical"></tbody>
            </table>            
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar" <?php echo $guardar ?>>Guardar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_cerrar" <?php echo $cerrar ?>>Cerrar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_anular" <?php echo $anular ?>>Anular</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir" <?php echo $imprimir ?>>Imprimir</button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/bajas/bajas_reg.js?v=<?php echo date('YmdHis') ?>"></script>