(function($) {
    $(document).on('show.bs.modal', '.modal', function() {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });

    $(document).ready(function() {
        //Tabla de Registros
        $('#tb_prescomercial').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_prescomerciales.php", function(he) {
                        //$('#divTamModalForms').removeClass('modal-xl');
                        //$('#divTamModalForms').removeClass('modal-sm');
                        //$('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: 'listar_prescomerciales.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.nombre = $('#txt_nombre_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_prescom' }, //Index=0
                { 'data': 'nom_presentacion' },
                { 'data': 'cantidad' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 1 },
                { orderable: false, targets: 3 }
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
        $('#tb_prescomercial').wrap('<div class="overflow"/>');
    });

    //Buascar registros
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_prescomercial');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_prescomercial');
        }
    });

    //Editar un registro    
    $('#tb_prescomercial').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_prescomerciales.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro 
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_nom_prescomercial'));
        error += verifica_vacio($('#txt_cantidad'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_prescomerciales').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_prescomerciales.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_prescomercial').val() == -1) ? 0 : $('#tb_prescomercial').DataTable().page.info().page;
                    reloadtable('tb_prescomercial', pag);
                    $('#id_prescomercial').val(r.id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Proceso realizado con éxito");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r.mensaje);
                }
            }).always(function() {}).fail(function() {
                alert('Ocurrió un error');
            });
        }
    });

    //Borrarr un registro 
    $('#tb_prescomercial').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('prescomerciales', id);
    });

    $('#divModalConfDel').on("click", "#prescomerciales", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_prescomerciales.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_prescomercial').DataTable().page.info().page;
                reloadtable('tb_prescomercial', pag);
                $('#divModalDone').modal('show');
                $('#divMsgDone').html("Proceso realizado con éxito");
            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html(r.mensaje);
            }
        }).always(function() {}).fail(function() {
            alert('Ocurrió un error');
        });
    });

    //Imprimir registros
    $('#btn_imprime_filtro').on('click', function() {
        reloadtable('tb_prescomercial');
        $.post("imp_prescomerciales.php", {
            nombre: $('#txt_nombre_filtro').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);