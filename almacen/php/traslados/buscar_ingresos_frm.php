<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$fecha_sis = date('Y-m-d');
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">ORDEN DE INGRESOS DE ALMACEN</h7>
        </div>
        <div class="px-2">

            <!--Formulario de busqueda de articulos-->
            <form id="frm_buscar_ingresos">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <input type="text" class="filtro_ing form-control form-control-sm" id="txt_num_ing_fil" placeholder="No. Ingreso">
                    </div>
                    <div class="form-group col-md-2">
                        <input type="date" class="filtro_ing form-control form-control-sm" id="txt_fecini_fil" name="txt_fecini_fil" placeholder="Fecha Inicial" value="<?php echo $fecha_sis ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <input type="date" class="filtro_ing form-control form-control-sm" id="txt_fecfin_fil" name="txt_fecfin_fil" placeholder="Fecha Final" value="<?php echo $fecha_sis ?>">
                    </div> 
                    <div class="form-group col-md-2">
                        <a type="button" id="btn_buscar_ing_fil" class="btn btn-outline-success btn-sm" title="Filtrar">
                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </form>
            <div style="height:400px" class="overflow-auto"> 
                <table id="tb_ingresos_tra" class="table table-striped table-bordered table-sm table-hover shadow" style="width:100%; font-size:80%">
                    <thead>
                        <tr class="text-center centro-vertical">
                            <th>Id</th>
                            <th>No. Ingreso</th>
                            <th>Fecha Ingreso</th>
                            <th>Detalle</th> 
                            <th>Proveedor</th> 
                            <th>Id.Sede</th>
                            <th>Sede</th>
                            <th>Id.Bodega</th>
                            <th>Bodega</th>
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
            $('#tb_ingresos_tra').DataTable({
                language: setIdioma,
                processing: true,
                serverSide: true,
                searching: false,
                autoWidth: true,
                ajax: {
                    url: 'buscar_ingresos_lista.php',
                    type: 'POST',
                    dataType: 'json',
                    data: function(data) {
                        data.num_ingreso = $('#txt_num_ing_fil').val();
                        data.fec_ini = $('#txt_fecini_fil').val();
                        data.fec_fin = $('#txt_fecfin_fil').val()
                    }
                },
                columns: [
                    { 'data': 'id_ingreso' }, //Index=0
                    { 'data': 'num_ingreso' },
                    { 'data': 'fec_ingreso' },                    
                    { 'data': 'detalle'  },
                    { 'data': 'nom_tercero'  },
                    { 'data': 'id_sede_origen'  },
                    { 'data': 'nom_sede_origen'  },
                    { 'data': 'id_bodega_origen'  },
                    { 'data': 'nom_bodega_origen'  },
                    { 'data': 'botones'  }
                ],
                columnDefs: [
                    { class: 'text-wrap', targets: [3,4] },
                    { width: '5%', targets: [0,1,2,6,8] },
                    { visible: false, targets: [5,7] },
                    { orderable: false, targets: 9 }
                ],
                order: [
                    [0, "desc"]
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'TODO'],
                ]
            });
            $('#tb_pedidos_tra').wrap('<div class="overflow-auto"></div>');
        });
    })(jQuery);

    //Buascar registros de articulos 
    $('#btn_buscar_ing_fil').on("click", function() {
        reloadtable('tb_ingresos_tra');
    });

    $('.filtro_ing').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_ingresos_tra');
        }
    });

    $('.filtro_ing').mouseup(function(e) {
        reloadtable('tb_ingresos_tra');
    });
    
</script>