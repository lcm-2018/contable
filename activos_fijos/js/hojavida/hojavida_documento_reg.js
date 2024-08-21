(function($) {
    $(document).ready(function() {
        $('#tb_documentos_hojavida').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_documento.php", {
                        id_hv: $('#id_hv').val()
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
                url: 'listar_documentos_hojavida.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_hv = $('#id_hv').val();
                }
            },
            columns: [
                { 'data': 'id' }, //Index=0
                { 'data': 'tipo' },
                { 'data': 'descripcion' },
                { 'data': 'archivo' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { orderable: false, targets: 4 }
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
        $('#tb_documentos_hojavida').wrap('<div class="overflow"/>');
    });

})(jQuery);