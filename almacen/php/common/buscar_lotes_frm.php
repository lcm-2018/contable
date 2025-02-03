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

$id_subgrupo = isset($_POST['id_subgrupo']) ? $_POST['id_subgrupo'] : 0;

$id_sede = isset($_POST['id_sede']) ? $_POST['id_sede'] : -1;
$id_bodega = isset($_POST['id_bodega']) && $_POST['id_bodega'] ? $_POST['id_bodega'] : -1;

$sql = "SELECT nombre FROM far_bodegas WHERE id_bodega=$id_bodega";
$rs = $cmd->query($sql);
$obj = $rs->fetch();
$nom_bodega = isset($obj['nombre']) ? $obj['nombre'] : '';
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">BUSCAR ARTICULOS - LOTES</h7>
        </div>
        <div class="px-2">

            <!--Formulario de busqueda de lotes-->
            <form id="frm_buscar_lotes">
                <input type="hidden" id="id_sede_fil" value="<?php echo $id_sede ?>">
                <input type="hidden" id="id_bodega_fil" value="<?php echo $id_bodega ?>">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <input type="text" class="form-control form-control-sm" class="small" value="<?php echo $nom_bodega ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <select class="form-control form-control-sm" id="sl_subgrupo_art_fil">
                            <?php subgrupo_articulo($cmd,'--Subgrupo--', $id_subgrupo) ?> 
                        </select>
                    </div>
                    <div class="form-group col-md-7">
                        <div class="form-row">
                            <div class="form-group col-md-2">                    
                                <input type="text" class="filtro_lot form-control form-control-sm" id="txt_codigo_art_fil" placeholder="Codigo">
                            </div>
                            <div class="form-group col-md-3">
                                <input type="text" class="filtro_lot form-control form-control-sm" id="txt_nombre_art_fil" placeholder="Nombre">
                            </div>
                            <div class="form-group col-md-3">
                                <div class="form-control form-control-sm">
                                    <input class="filtro_lot form-check-input" type="checkbox" id="chk_novencido_lot_fil" checked>
                                    <label class="filtro_lot form-check-label small" for="chk_novencido_lot_fil">NO Vencidos</label>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="form-control form-control-sm">
                                    <input class="filtro_lot form-check-input" type="checkbox" id="chk_conexistencia_lot_fil" checked>
                                    <label class="filtro_lot form-check-label small" for="chk_conexistencia_lot_fil">Con Existencias</label>
                                </div>
                            </div>
                            <div class="form-group col-md-1">
                                <a type="button" id="btn_buscar_lot_fil" class="btn btn-outline-success btn-sm" title="Filtrar">
                                    <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                                </a>
                            </div>
                        </div>    
                    </div>    
                </div>
            </form>
            <div style="height:400px" class="overflow-auto"> 
                <table id="tb_lotes_articulos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                    <thead>
                        <tr class="text-center centro-vertical">
                            <th>Id</th>
                            <th>Id.Med</th>
                            <th>Código</th>
                            <th>Artículo</th>
                            <th>Lote</th>                            
                            <th>Presentación del Lote</th>
                            <th>Unidades en UMPL</th>
                            <th>Existencia</th>
                            <th>Vr. Promedio</th>
                            <th>Fecha Vencimiento</th>
                        </tr>
                    </thead>
                    <tbody class="text-left centro-vertical"></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="text-right pt-3 rigth">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Salir</a>
    </div>
</div>

<script>
    (function($) {
        $(document).ready(function() {
            $('#tb_lotes_articulos').DataTable({
                language: setIdioma,
                processing: true,
                serverSide: true,
                searching: false,
                autoWidth: false,
                ajax: {
                    url: '../common/buscar_lotes_lista.php',
                    type: 'POST',
                    dataType: 'json',
                    data: function(data) {
                        data.id_sede = $('#id_sede_fil').val();
                        data.id_bodega = $('#id_bodega_fil').val();
                        data.id_subgrupo = $('#sl_subgrupo_art_fil').val();
                        data.codigo = $('#txt_codigo_art_fil').val();
                        data.nombre = $('#txt_nombre_art_fil').val();
                        data.no_vencidos = $('#chk_novencido_lot_fil').is(':checked') ? 1 : 0;
                        data.con_existencia = $('#chk_conexistencia_lot_fil').is(':checked') ? 1 : 0;
                    }
                },
                columns: [
                    { 'data': 'id_lote' }, //Index=0
                    { 'data': 'id_med' },
                    { 'data': 'cod_medicamento' },
                    { 'data': 'nom_medicamento' },
                    { 'data': 'lote' },
                    { 'data': 'nom_presentacion'  },                    
                    { 'data': 'existencia_umpl' },                    
                    { 'data': 'existencia'  },
                    { 'data': 'val_promedio' },
                    { 'data': 'fec_vencimiento' },
                ],
                columnDefs: [
                    { class: 'text-wrap', targets: [3,5] },
                    { width: '5%', targets: [0,1,2,4,6,7,8,9] }
                ],
                order: [
                    [2, "asc"]
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'TODO'],
                ]
            });
            $('#tb_lotes_articulos').wrap('<div class="overflow"/>');
        });
    })(jQuery);

    //Buascar registros de Lotes de Articulos
    $('#btn_buscar_lot_fil').on("click", function() {
        reloadtable('tb_lotes_articulos');
    });

    $('.filtro_lot').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_lotes_articulos');
        }
    });

    $('.filtro_lot').mouseup(function(e) {
        reloadtable('tb_lotes_articulos');
    });

    $('#sl_subgrupo_art_fil').on("change", function() {
        sessionStorage.setItem("id_subgrupo", $(this).val());
    });
    
</script>