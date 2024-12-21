(function($) {
    $(document).ready(function() {
        $('#tb_componentes_hojavida').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("../common/buscar_articulos_act_frm.php", function(he) {
                        $('#divTamModalBus').removeClass('modal-xl');
                        $('#divTamModalBus').removeClass('modal-sm');
                        $('#divTamModalBus').addClass('modal-lg');
                        $('#divModalBus').modal('show');
                        $("#divFormsBus").html(he);
                    });
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'listar_componentes_hojavida.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_hv = $('#id_hv').val();
                }
            },
            columns: [
                { 'data': 'id' }, //Index=0
                { 'data': 'nom_articulo' },
                { 'data': 'num_serial' },
                { 'data': 'modelo' },
                { 'data': 'nom_marca' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { orderable: false, targets: 5 }
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
        $('#tb_componentes_hojavida').wrap('<div class="overflow"/>');
    });
})(jQuery);