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
$sql = "SELECT far_medicamento_lote.id_lote,far_medicamento_lote.lote,
            far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento
        FROM far_medicamento_lote 
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
        WHERE far_medicamento_lote.id_lote=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {    
    //Inicializa variable por defecto
    $obj['id_lote'] = 0;
}

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRO DE MOVIMIENTOS</h5>
        </div>
        <div class="px-2">

            <!--Formulario de registro de lotes-->
            <form id="frm_reg_lotes">
                <input type="hidden" id="id_lote" name="id_lote" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-1">
                        <input type="text" class="form-control form-control-sm" id="txt_cod_art" name="txt_cod_art" value="<?php echo $obj['cod_medicamento'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-5">
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" name="txt_nom_art" value="<?php echo $obj['nom_medicamento'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <input type="text" class="form-control form-control-sm" id="txt_lote" name="txt_lote" value="<?php echo $obj['lote'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <div class=" form-row">
                            <div class="form-group col-md-6">
                                <input type="date" class="form-control form-control-sm" id="txt_fecini_fil" name="txt_fecini_fil" placeholder="Fecha Inicial">
                            </div>
                            <div class="form-group col-md-6">
                                <input type="date" class=" form-control form-control-sm" id="txt_fecfin_fil" name="txt_fecini_fil" placeholder="Fecha Final">
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-1">
                        <a type="button" id="btn_buscar_fil_kar" class="btn btn-outline-success btn-sm" title="Buscar">
                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>    
                </div>
            </form>

            <!--Lista de CUMS-->
            <div class="tab-pane fade show active" id="nav_lista_cums" role="tabpanel" aria-labelledby="nav_lista_cums-tab">
                <table id="tb_kardex" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                    <thead>
                        <tr class="text-center centro-vertical">
                            <th>Id</th>
                            <th>Fecha</th>
                            <th>Comprobante</th>
                            <th>Sede</th>
                            <th>Bodega</th>
                            <th>Lote</th>
                            <th>Detalle</th>
                            <th>Vr. Unitario</th>
                            <th>Vr. Promedio</th>
                            <th>Can. Ingreso</th>
                            <th>Can. Egreso</th>
                            <th>Existencia</th>
                        </tr>
                    </thead>
                    <tbody class="text-left centro-vertical"></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-center pt-3">    
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir">Imprimir</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Salir</a>
    </div>
</div>

<script>
    (function($) {
        $(document).ready(function() {
            $('#tb_kardex').DataTable({
                language: setIdioma,
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: 'listar_kardex_lote.php',
                    type: 'POST',
                    dataType: 'json',
                    data: function(data) {
                        data.id_lote = $('#id_lote').val();
                        data.fec_ini = $('#txt_fecini_fil').val();
                        data.fec_fin = $('#txt_fecfin_fil').val();
                    }
                },
                columns: [
                    { 'data': 'id_kardex' }, //Index=0
                    { 'data': 'fec_movimiento' },
                    { 'data': 'comprobante' },
                    { 'data': 'nom_sede' },
                    { 'data': 'nom_bodega' },
                    { 'data': 'lote' },
                    { 'data': 'detalle' },
                    { 'data': 'val_ingreso' },
                    { 'data': 'val_promedio' },
                    { 'data': 'can_ingreso' },
                    { 'data': 'can_egreso' },
                    { 'data': 'existencia_lote' }
                ],
                columnDefs: [
                    {  orderable: false, targets: [0,1,2,3,4,5,6,7,8,9,10,11] }
                ],
                order: [
                    [0, "ASC"]
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'TODO'],
                ]
            });
            $('#tb_kardex').wrap('<div class="overflow"/>');
        });
    })(jQuery);
    
</script>