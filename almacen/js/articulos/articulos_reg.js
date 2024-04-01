(function($) {
    $(document).ready(function() {
        $('#tb_articulos_cums').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_articulos_cums.php", { id_articulo: $('#id_articulo').val() }, function(he) {
                        $('#divTamModalReg').removeClass('modal-xl');
                        $('#divTamModalReg').removeClass('modal-sm');
                        $('#divTamModalReg').addClass('modal-lg');
                        $('#divModalReg').modal('show');
                        $("#divFormsReg").html(he);
                    });
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'listar_articulos_cums.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_articulo = $('#id_articulo').val();
                }
            },
            columns: [
                { 'data': 'id_cum' }, //Index=0
                { 'data': 'cum' },
                { 'data': 'ium' },
                { 'data': 'nom_laboratorio' },
                { 'data': 'nom_presentacion' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [3, 4] },
                { orderable: false, targets: 6 }
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
        $('#tb_articulos_cums').wrap('<div class="overflow"/>');

        $('#tb_articulos_lotes').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_articulos_lotes.php", { id_articulo: $('#id_articulo').val() }, function(he) {
                        $('#divTamModalReg').removeClass('modal-xl');
                        $('#divTamModalReg').removeClass('modal-sm');
                        $('#divTamModalReg').addClass('modal-lg');
                        $('#divModalReg').modal('show');
                        $("#divFormsReg").html(he);

                    });
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'listar_articulos_lotes.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_articulo = $('#id_articulo').val();
                }
            },
            columns: [
                { 'data': 'id_lote' }, //Index=0
                { 'data': 'lote' },
                { 'data': 'lote_pri' },
                { 'data': 'fec_vencimiento' },
                { 'data': 'nom_presentacion', },
                { 'data': 'existencia_umpl' },
                { 'data': 'existencia' },
                { 'data': 'cum' },
                { 'data': 'nom_bodega' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [4] },
                { orderable: false, targets: 10 }
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
        $('#tb_articulos_lotes').wrap('<div class="overflow"/>');
    });

})(jQuery);