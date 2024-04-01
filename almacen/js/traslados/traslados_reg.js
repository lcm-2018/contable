(function($) {
    $(document).ready(function() {
        $('#tb_traslados_detalles').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("../common/buscar_lotes_frm.php", { id_sede: $('#sl_sede_origen').val(), id_bodega: $('#sl_bodega_origen').val() }, function(he) {
                        $('#divTamModalBus').removeClass('modal-lg');
                        $('#divTamModalBus').removeClass('modal-sm');
                        $('#divTamModalBus').addClass('modal-xl');
                        $('#divModalBus').modal('show');
                        $("#divFormsBus").html(he);
                    });
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'listar_traslados_detalles.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_traslado = $('#id_traslado').val();
                }
            },
            columns: [
                { 'data': 'id_tra_detalle' }, //Index=0
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'lote' },
                { 'data': 'cantidad' },
                { 'data': 'valor' },
                { 'data': 'val_total' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 2 },
                { orderable: false, targets: 7 }
            ],
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_traslado_detalles').wrap('<div class="overflow"/>');
    });
})(jQuery);