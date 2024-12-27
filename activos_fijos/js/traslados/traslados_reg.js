(function($) {
    $(document).ready(function() {
        $('#tb_traslados_detalles').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("../common/buscar_activo_fijo_frm.php", { proceso: 'tras', id_area: $('#sl_area_origen').val() }, function(he) {
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
                { 'data': 'id_traslado_detalle' }, //Index=0
                { 'data': 'placa' },
                { 'data': 'nom_articulo' },
                { 'data': 'estado_general' },
                { 'data': 'observacion' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2, 4] },
                { orderable: false, targets: 5 }
            ],
            order: [
                [0, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        }).on('draw', function() {
            let table = $('#tb_traslados_detalles').DataTable();
            let rows = table.rows({ filter: 'applied' }).count();
            if (rows > 0) {
                $('#sl_area_origen').prop('disabled', true);
            } else {
                $('#sl_area_origen').prop('disabled', false);
            }
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_traslados_detalles').wrap('<div class="overflow"/>');
    });

})(jQuery);