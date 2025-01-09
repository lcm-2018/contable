<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">PEDIDOS DE DEPENDENCIA</h7>
        </div>
        <div class="px-2">

            <!--Formulario de busqueda de articulos-->
            <form id="frm_buscar_pedidos">
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
                <table id="tb_pedidos_egr" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                    <thead>
                        <tr class="text-center centro-vertical">
                            <th>Id</th>
                            <th>No. Pedido</th>
                            <th>Fecha Pedido</th>
                            <th>Detalle</th> 
                            <th>Id.Dependencia</th>
                            <th>Dependencia Que Solicita</th>
                            <th>Id.Sede</th>
                            <th>Sede Proveedor</th>
                            <th>Id.Bodega</th>
                            <th>Bodega Proveedor</th>
                            <th>Ver</th>
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
            $('#tb_pedidos_egr').DataTable({
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
                        data.num_pedido = $('#txt_num_ped_fil').val();
                        data.fec_ini = $('#txt_fecini_fil').val();
                        data.fec_fin = $('#txt_fecfin_fil').val()
                    }
                },
                columns: [
                    { 'data': 'id_pedido' }, //Index=0
                    { 'data': 'num_pedido' },
                    { 'data': 'fec_pedido' },                    
                    { 'data': 'detalle'  },
                    { 'data': 'id_cencosto'  },
                    { 'data': 'nom_centro'  },
                    { 'data': 'id_sede'  },
                    { 'data': 'nom_sede'  },
                    { 'data': 'id_bodega'  },
                    { 'data': 'nom_bodega'  },
                    { 'data': 'botones'  }
                ],
                columnDefs: [
                    { class: 'text-wrap', targets: 3 },
                    { width: '5%', targets: [0,1,2,5,7,9] },
                    { visible: false, targets: [4,6,8] },
                    { orderable: false, targets: 10 }
                ],
                order: [
                    [0, "desc"]
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'TODO'],
                ]
            });
            $('#tb_pedidos_egr').wrap('<div class="overflow"/>');
        });
    })(jQuery);

    //Buascar registros de articulos 
    $('#btn_buscar_ped_fil').on("click", function() {
        reloadtable('tb_pedidos_egr');
    });

    $('.filtro_ped').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_pedidos_egr');
        }
    });

    $('.filtro_ped').mouseup(function(e) {
        reloadtable('tb_pedidos_egr');
    });
    
</script>