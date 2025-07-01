(function($) {
    $(document).ready(function() {
        $('#tb_bajas_detalles').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("../common/buscar_activo_fijo_frm.php", { proceso: 'baja' }, function(he) {
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
                url: 'listar_bajas_detalles.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_baja = $('#id_baja').val();
                }
            },
            columns: [
                { 'data': 'id_baja_detalle' }, //Index=0
                { 'data': 'placa' },
                { 'data': 'nom_articulo' },
                { 'data': 'des_activo' },
                { 'data': 'estado_general' },
                { 'data': 'observacion' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2, 3, 5] },
                { orderable: false, targets: 6 }
            ],
            order: [
                [0, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_bajas_detalles').wrap('<div class="overflow"/>');
    });

})(jQuery);