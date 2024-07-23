(function($) {
    $(document).ready(function() {
        $('#tb_lista_activos_fijos').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_activofijo_detalle.php", {
                        id_ing_detalle: $('#id_ing_detalle').val(),
                        val_unitario: $('#txt_vrunit_med').val()
                    }, function(he) {
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
                url: 'listar_activosfijos_detalles.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_ing_detalle = $('#id_ing_detalle').val();
                }
            },
            columns: [
                { 'data': 'id_act_fij' }, //Index=0
                { 'data': 'placa' },
                { 'data': 'serial' },
                { 'data': 'nom_marca' },
                { 'data': 'valor' },
                { 'data': 'tipo_activo' },
                { 'data': 'botones' }
            ],
            columnDefs: [
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
        $('#tb_lista_activos_fijos').wrap('<div class="overflow"/>');
    });
})(jQuery);