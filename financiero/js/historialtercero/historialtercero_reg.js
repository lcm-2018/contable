(function($) {
    $(document).ready(function() {
        $('#tb_terceros').DataTable({
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
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
                url: '', //aki va asi url: 'listar_cencos_areas.php'
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
        $('#tb_terceros').wrap('<div class="overflow"/>');

        $('#tb_cuentas').DataTable({
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
                url: '', //aki la url va asi url: 'listar_cencos_areas.php'
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
        $('#tb_cuentas').wrap('<div class="overflow"/>');
    });
})(jQuery);