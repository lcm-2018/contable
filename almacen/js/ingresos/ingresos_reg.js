(function($) {
    $(document).ready(function() {
        $('#tb_ingresos_detalles').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    if ($('#sl_tip_ing').find('option:selected').attr('data-ordcom') == 1) {
                        $.post("buscar_articulos_pedido_frm.php", { id_sede: $('#id_txt_sede').val(), id_bodega: $('#id_txt_nom_bod').val(), id_pedido: $('#txt_id_pedido').val() }, function(he) {
                            $('#divTamModalBus').removeClass('modal-lg');
                            $('#divTamModalBus').removeClass('modal-sm');
                            $('#divTamModalBus').addClass('modal-xl');
                            $('#divModalBus').modal('show');
                            $("#divFormsBus").html(he);
                        });
                    } else {
                        $.post("../common/buscar_lotes_frm.php", { id_sede: $('#id_txt_sede').val(), id_bodega: $('#id_txt_nom_bod').val() }, function(he) {
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
            ajax: {
                url: 'listar_ingresos_detalles.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_ingreso = $('#id_ingreso').val();
                }
            },
            columns: [
                { 'data': 'id_ing_detalle' }, //Index=0
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'lote' },
                { 'data': 'fec_vencimiento' },
                { 'data': 'nom_presentacion' },
                { 'data': 'cantidad' },
                { 'data': 'valor_sin_iva' },
                { 'data': 'iva' },
                { 'data': 'valor' },
                { 'data': 'val_total' },
                { 'data': 'observacion' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2, 5, 11] },
                { orderable: false, targets: 12 }
            ],
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        }).on('draw', function() {
            let table = $('#tb_ingresos_detalles').DataTable();
            let rows = table.rows({ filter: 'applied' }).count();
            if (rows > 0) {
                $('#sl_tip_ing').prop('disabled', true);
                $('#txt_id_pedido').prop('disabled', true);
            } else {
                $('#sl_tip_ing').prop('disabled', false);
                $('#txt_id_pedido').prop('disabled', false);
            }
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_ingresos_detalles').wrap('<div class="overflow"/>');
    });
})(jQuery);