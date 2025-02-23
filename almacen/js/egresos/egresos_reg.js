(function($) {
    $(document).ready(function() {
        $('#tb_egresos_detalles').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    let id_egreso = $('#id_egreso').val();
                    let table = $('#tb_egresos_detalles').DataTable();
                    let filas = table.rows().count();
                    let id_pedido = $('#txt_id_pedido').val();

                    if (id_egreso == -1 || id_pedido && filas == 0) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Primero debe guardar la Orden de Egreso');
                    } else {
                        $.post("../common/buscar_lotes_frm.php", {
                            id_sede: $('#sl_sede_egr').val(),
                            id_bodega: $('#sl_bodega_egr').val(),
                            id_subgrupo: sessionStorage.getItem("id_subgrupo")
                        }, function(he) {
                            $('#divTamModalBus').removeClass('modal-lg');
                            $('#divTamModalBus').removeClass('modal-sm');
                            $('#divTamModalBus').addClass('modal-xl');
                            $('#divModalBus').modal('show');
                            $("#divFormsBus").html(he);
                        });
                    }
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: 'listar_egresos_detalles.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_egreso = $('#id_egreso').val();
                }
            },
            columns: [
                { 'data': 'id_egr_detalle' }, //Index=0
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'lote' },
                { 'data': 'existencia' },
                { 'data': 'fec_vencimiento' },
                { 'data': 'cantidad' },
                { 'data': 'valor' },
                { 'data': 'val_total' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 2 },
                { orderable: false, targets: 9 }
            ],
            order: [
                [0, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        }).on('draw', function() {
            let table = $('#tb_egresos_detalles').DataTable();
            let rows = table.rows({ filter: 'applied' }).count();
            if (rows > 0) {
                $('#sl_sede_egr').prop('disabled', true);
                $('#sl_bodega_egr').prop('disabled', true);
                $('#txt_des_pedido').prop('disabled', true);
                $('#btn_cancelar_pedido').prop('disabled', true);
            } else {
                $('#sl_sede_egr').prop('disabled', false);
                $('#sl_bodega_egr').prop('disabled', false);
                $('#txt_des_pedido').prop('disabled', false);
                $('#btn_cancelar_pedido').prop('disabled', false);
            }
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_egreso_detalles').wrap('<div class="overflow"/>');
    });
})(jQuery);