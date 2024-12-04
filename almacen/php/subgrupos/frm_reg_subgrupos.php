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
$sql = "SELECT * FROM far_subgrupos WHERE id_subgrupo=" . $id . " LIMIT 1";
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
    $obj['lote_xdef'] = 1;
    $obj['estado'] = 1;
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR SUBGRUPO</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_subgrupos">
                <input type="hidden" id="id_subgrupo" name="id_subgrupo" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-1">
                        <label for="txt_cod_subgrupo" class="small">Código</label>
                        <input type="text" class="form-control form-control-sm number" id="txt_cod_subgrupo" name="txt_cod_subgrupo" required value="<?php echo $obj['cod_subgrupo'] ?>">
                    </div>
                    <div class="form-group col-md-5">
                        <label for="txt_nom_subgrupo" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_subgrupo" name="txt_nom_subgrupo" required value="<?php echo $obj['nom_subgrupo'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_grp_subgrupo" class="small">Grupo</label>
                        <select class="form-control form-control-sm" id="sl_grp_subgrupo" name="sl_grp_subgrupo" required>
                            <?php grupo_articulo($cmd,'',$obj['id_grupo']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_lotexdef" class="small">Lote x Defecto</label>
                        <select class="form-control form-control-sm" id="sl_lotexdef" name="sl_lotexdef">
                            <?php estados_sino('',$obj['lote_xdef']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_estado" class="small">Estado</label>
                        <select class="form-control form-control-sm" id="sl_estado" name="sl_estado">
                            <?php estados_registros('',$obj['estado']) ?>
                        </select>
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

<script type="text/javascript" src="../../js/subgrupos/subgrupos_reg.js?v=<?php echo date('YmdHis') ?>"></script>