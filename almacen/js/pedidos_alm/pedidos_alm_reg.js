(function($) {
    $(document).ready(function() {
        $('#tb_pedidos_detalles').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("buscar_articulos_act_frm.php", {
                        id_subgrupo: sessionStorage.getItem("id_subgrupo")
                    }, function(he) {
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
            autoWidth: false,
            ajax: {
                url: 'listar_pedidos_detalles.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_pedido = $('#id_pedido').val();
                }
            },
            columns: [
                { 'data': 'id_ped_detalle' }, //Index=0
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'cantidad' },
                { 'data': 'aprobado' },
                { 'data': 'valor' },
                { 'data': 'val_total' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 2 },
                { orderable: false, targets: 7 }
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
        $('#tb_pedidos_detalles').wrap('<div class="overflow"/>');
    });
})(jQuery);