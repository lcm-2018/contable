(function($) {
    $(document).ready(function() {
        $('#tb_componentes_activofijo').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("form_componente_hojavida_detalle.php", {
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
                url: 'listar_componentes_hojavida.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_hv = $('#id_hv').val();
                }
            },
            columns: [
                { 'data': 'id' }, //Index=0
                { 'data': 'articulo' },
                { 'data': 'serial' },
                { 'data': 'modelo' },
                { 'data': 'marca' },
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
        $('#tb_componentes_activofijo').wrap('<div class="overflow"/>');
    });

    //Editar un registro hoja de vida
    $('#tb_componentes_activofijo').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("form_componente_hojavida_detalle.php", { 
            id_componente: id,
            id_hv: $('#id_hv').val()
        }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    }); 

    //Borrar DOCUMENTO HOJA VIDA
    $('#tb_componentes_activofijo').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('componente_del', id);
    });
    $('#divModalConfDel').on("click", "#componente_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_componente.php',
            dataType: 'json',
            data: { id_componente: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_componentes_activofijo').DataTable().page.info().page;
                reloadtable('tb_componentes_activofijo', pag);
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