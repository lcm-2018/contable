(function($) {
    $(document).ready(function() {
        $('#tb_notas_mantenimiento').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_nota_detalle.php", { id_md: $('#id_mant_detalle').val() }, function(he) {
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
                url: 'listar_mantenimientos_notas.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_mant_detalle = $('#id_mant_detalle').val();
                }
            },
            columns: [
                { 'data': 'id_det_nota' }, //Index=0
                { 'data': 'fec_nota' },
                { 'data': 'hor_nota' },
                { 'data': 'observacion' },
                { 'data': 'archivo' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 3 },
                { orderable: false, targets: 5 }
            ],
            order: [
                [0, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ]
        });
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_notas_mantenimiento').wrap('<div class="overflow"/>');
    });

})(jQuery);