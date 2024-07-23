(function($) {
    $(document).ready(function() {
        $('#tb_lista_documentos_acf').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_documentos.php", {
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
                url: 'listar_activosfijos_documentos.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_hv = $('#id_hv').val();
                }
            },
            columns: [
                { 'data': 'id' }, //Index=0
                { 'data': 'placa' },
                { 'data': 'tipo' },
                { 'data': 'descripcion' },
                { 'data': 'archivo' },
                { 'data': 'usuario' },
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
        $('#tb_lista_documentos_acf').wrap('<div class="overflow"/>');
    });

    //Editar un registro hoja de vida
    $('#tb_lista_documentos_acf').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_documentos.php", { id_hv_doc: id }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    }); 

    //Borrar DOCUMENTO HOJA VIDA
    $('#tb_lista_documentos_acf').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('documentos_del', id);
    });
    $('#divModalConfDel').on("click", "#documentos_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_documentos_hv.php',
            dataType: 'json',
            data: { id_hv_doc: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_lista_documentos_acf').DataTable().page.info().page;
                reloadtable('tb_lista_documentos_acf', pag);
                $('#divModalDone').modal('show');
                $('#divMsgDone').html("Proceso realizado con éxito");
            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html(r.mensaje);
            }
        }).always(function() {

        }).fail(function(xhr, textStatus, errorThrown) {
            console.error(xhr.responseText)
            alert('Ocurrió un error');
        });
    });
})(jQuery);