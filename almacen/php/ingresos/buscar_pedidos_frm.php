<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_sede = isset($_POST['id_sede']) ? $_POST['id_sede'] : -1;
$id_bodega = isset($_POST['id_bodega']) && $_POST['id_bodega'] ? $_POST['id_bodega'] : -1;

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">PEDIDO DE ORDEN DE COMPRA</h7>
        </div>
        <div class="px-2">

            <!--Formulario de busqueda de articulos-->
            <form id="frm_buscar_pedidos">
                <input type="hidden" id="id_sede_fil" value="<?php echo $id_sede ?>">
                <input type="hidden" id="id_bodega_fil" value="<?php echo $id_bodega ?>">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <input type="text" class="filtro_ped form-control form-control-sm" id="txt_num_ped_fil" placeholder="No. Pedido">
                    </div>
                    <div class="form-group col-md-3">
                        <input type="date" class="filtro_ped form-control form-control-sm" id="txt_fecini_fil" name="txt_fecini_fil" placeholder="Fecha Inicial">
                    </div>
                    <div class="form-group col-md-3">
                        <input type="date" class="filtro_ped form-control form-control-sm" id="txt_fecfin_fil" name="txt_fecfin_fil" placeholder="Fecha Final">
                    </div> 
                    <div class="form-group col-md-2">
                        <a type="button" id="btn_buscar_ped_fil" class="btn btn-outline-success btn-sm" title="Filtrar">
                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </form>
            <div style="height:400px" class="overflow-auto"> 
                <table id="tb_pedidos_ing" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                    <thead>
                        <tr class="text-center centro-vertical">
                            <th>Id</th>
                            <th>Num. Pedido</th>
                            <th>Fec. Pedido</th>                            
                            <th>Detalle</th>
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
            $('#tb_pedidos_ing').DataTable({
                language: setIdioma,
                processing: true,
                serverSide: true,
                searching: false,
                autoWidth: false,
                ajax: {
                    url: 'buscar_pedidos_lista.php',
                    type: 'POST',
                    dataType: 'json',
                    data: function(data) {
                        data.id_sede = $('#id_sede_fil').val();
                        data.id_bodega = $('#id_bodega_fil').val();
                        data.num_pedido = $('#txt_num_ped_fil').val();
                        data.fec_ini = $('#txt_fecini_fil').val();
                        data.fec_fin = $('#txt_fecfin_fil').val()
                    }
                },
                columns: [
                    { 'data': 'id_pedido' }, //Index=0
                    { 'data': 'num_pedido' },
                    { 'data': 'fec_pedido' },                    
                    { 'data': 'detalle'  }
                ],
                columnDefs: [
                    { class: 'text-wrap', targets: [3] },
                    { width: '5%', targets: [0,1,2] }
                ],
                order: [
                    [0, "desc"]
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'TODO'],
                ]
            });
            $('#tb_articulos_pedido').wrap('<div class="overflow"/>');
        });
    })(jQuery);

    //Buascar registros de articulos 
    $('#btn_buscar_ped_fil').on("click", function() {
        reloadtable('tb_pedidos_ing');
    });

    $('.filtro_ped').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_pedidos_ing');
        }
    });

    $('.filtro_ped').mouseup(function(e) {
        reloadtable('tb_pedidos_ing');
    });
    
</script>