(function($) {
    $(document).ready(function() {
        $('#tb_cuentas_sg_det').DataTable({
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            autoWidth: false,
            ajax: {
                url: 'listar_centrocostos_sg_cta.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_cec_sg = $('#id_cec_sg').val();
                }
            },
            columns: [
                { 'data': 'id_cecsubgrp_det' }, //Index=0
                { 'data': 'id_subgrupo' },
                { 'data': 'nom_subgrupo' },
                { 'data': 'cuenta' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2, 3] },
                { visible: false, targets: 1 },
                { orderable: false, targets: 4 },
            ],
            order: [
                [1, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_cuentas_sg_det').wrap('<div class="overflow"/>');
    });

})(jQuery);