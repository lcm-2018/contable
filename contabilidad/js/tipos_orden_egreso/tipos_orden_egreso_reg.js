(function($) {
    $(document).ready(function() {
        $('#tb_cuentas_c').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_tipos_orden_egreso_cta.php", { id_tipo_egreso: $('#id_tipo_egreso').val() }, function(he) {
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
            autoWidth: false,
            ajax: {
                url: 'listar_tipos_orden_egreso_cta.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_tipo_egreso = $('#id_tipo_egreso').val();
                }
            },
            columns: [
                { 'data': 'id_tipo_egreso_cta' }, //Index=0
                { 'data': 'cuenta' },
                { 'data': 'fecha_vigencia' },
                { 'data': 'vigente' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 1 },
                { orderable: false, targets: 5 }
            ],
            rowCallback: function(row, data) {
                var vigente = $($(row).find("td")[3]).text();
                if (vigente == 'X') {
                    $($(row).find("td")[3]).css("background-color", "#ffc107");
                }
            },
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_cuentas_c').wrap('<div class="overflow"/>');
        
    });

})(jQuery);