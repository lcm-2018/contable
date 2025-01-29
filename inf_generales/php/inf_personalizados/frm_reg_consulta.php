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
$sql = "SELECT * FROM tb_consultas_sql WHERE id_consulta=" . $id . " LIMIT 1";
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
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CONSULTA</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_consulta">
                <input type="hidden" id="txt_id_con" name="txt_id_con" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="txt_nom_con" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_con" name="txt_nom_con" required value="<?php echo $obj['nom_consulta'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="sl_opcion" class="small">Módulo</label>
                        <select class="form-control form-control-sm" id="sl_opcion" name="sl_opcion" required>
                            <?php cargar_opcion_csql($cmd,'',$obj['id_opcion']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_des_con" class="small">Descripción</label>                   
                        <textarea class="form-control" id="txt_des_con" name="txt_des_con" rows="3"><?php echo htmlspecialchars($obj['des_consulta']) ?></textarea>                        
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_con_sql" class="small">Consulta</label>                   
                        <textarea class="form-control" id="txt_con_sql" name="txt_con_sql" rows="10"><?php echo htmlspecialchars($obj['consulta']) ?></textarea>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_par_con" class="small">Parámetros</label>
                        <input type="text" class="form-control form-control-sm" id="txt_par_con" name="txt_par_con" value="<?php echo htmlspecialchars($obj['parametros']) ?>">
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