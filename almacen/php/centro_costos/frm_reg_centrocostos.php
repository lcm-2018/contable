<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT tb_centrocostos.*,
            CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_respon
        FROM tb_centrocostos 
        INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=tb_centrocostos.id_responsable) 
        WHERE tb_centrocostos.id_centro=" . $id . " LIMIT 1";
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
    $obj['es_clinico'] = 0;
    $obj['id_responsable'] = 0;
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR CENTRO DE COSTO</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_centrocostos">
                <input type="hidden" id="id_centrocosto" name="id_centrocosto" value="<?php echo $id ?>">
                <div class=" form-row">                   
                    <div class="form-group col-md-5">
                        <label for="txt_nom_centrocosto" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_centrocosto" name="txt_nom_centrocosto" value="<?php echo $obj['nom_centro'] ?>">
                    </div>                                    
                    <div class="form-group col-md-3">
                        <label class="small">Para Uso Asistencial</label>
                        <div class="form-control form-control-sm" id="rdo_escli_cec">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rdo_escli_cec" id="rdo_escli_cec_si" value="1" <?php echo $obj['es_clinico'] == 1 ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="rdo_escli_cec_si">SI</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rdo_escli_cec" id="rdo_escli_cec_no" value="0" <?php echo $obj['es_clinico'] == 0 ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="rdo_escli_cec_no">NO</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txt_responsable" class="small">Responsable</label>
                        <input type="text" class="form-control form-control-sm" id="txt_responsable" value="<?php echo $obj['usr_respon'] ?>">
                        <input type="hidden" id="id_txt_responsable" name="id_txt_responsable" value="<?php echo $obj['id_responsable'] ?>">
                    </div>
                </div>
                <table id="tb_cuentas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                    <thead>
                        <tr class="text-center centro-vertical">
                            <th>Id</th>
                            <th>Cuenta Contable</th>
                            <th>Fecha Inicio de Vigencia</th>
                            <th>Cuenta Vigente</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/centro_costos/centro_costos_reg.js?v=<?php echo date('YmdHis') ?>"></script>