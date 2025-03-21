(function($) {
    $(document).ready(function() {
        $('#tb_cuentas').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_centrocostos_cta.php", { id_cencos: $('#id_centrocosto').val() }, function(he) {
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
                url: 'listar_centrocostos_cta.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_cencos = $('#id_centrocosto').val();
                }
            },
            columns: [
                { 'data': 'id_cec_cta' }, //Index=0
                { 'data': 'cuenta' },
                { 'data': 'fecha_vigencia' },
                { 'data': 'vigente' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 1 },
                { orderable: false, targets: 5 },
                { width: '5%', targets: [0, 2, 3, 4, 5] }
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
        $('#tb_cuentas').wrap('<div class="overflow"/>');

        $('#tb_cuentas_sg').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_centrocostos_sg.php", { id_cencos: $('#id_centrocosto').val() }, function(he) {
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
            searching: false,
            autoWidth: false,
            ajax: {
                url: 'listar_centrocostos_sg.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_cencos = $('#id_centrocosto').val();
                }
            },
            columns: [
                { 'data': 'id_cecsubgrp' }, //Index=0
                { 'data': 'fecha_vigencia' },
                { 'data': 'vigente' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { orderable: false, targets: 4 }
            ],
            rowCallback: function(row, data) {
                if (data.vigente == 'X') {
                    $($(row).find("td")[2]).css("background-color", "#ffc107");
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
        $('#tb_cuentas_sg').wrap('<div class="overflow"/>');

    });

})(jQuery);