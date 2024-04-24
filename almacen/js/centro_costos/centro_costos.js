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
        $('#tb_centro_costos').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_centrocostos.php", function(he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
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
                url: 'listar_centrocostos.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.nombre = $('#txt_nombre_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_centro' }, //Index=0              
                { 'data': 'nom_centro' },
                { 'data': 'cuenta' },
                { 'data': 'es_clinico' },
                { 'data': 'usr_respon' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 1 },
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
        $('#tb_centro_costos').wrap('<div class="overflow"/>');
    });

    //Buascar registros
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_centro_costos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_centro_costos');
        }
    });

    // Autocompletar Usuarios reposnables
    $('#divForms').on("input", "#txt_responsable", function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "../common/cargar_usuariosistema_ls.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term }
                }).done(function(data) {
                    response(data);
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#id_txt_responsable').val(ui.item.id);
            }
        });
    });

    //Editar un registro    
    $('#tb_centro_costos').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_centrocostos.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro 
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_nom_centrocosto'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_centrocostos').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_centrocostos.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_centrocosto').val() == -1) ? 0 : $('#tb_centro_costos').DataTable().page.info().page;
                    reloadtable('tb_centro_costos', pag);
                    $('#id_centrocosto').val(r.id);
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

    //Borrar un registro 
    $('#tb_centro_costos').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('centrocostos', id);
    });

    $('#divModalConfDel').on("click", "#centrocostos", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_centrocostos.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_centro_costos').DataTable().page.info().page;
                reloadtable('tb_centro_costos', pag);
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
        reloadtable('tb_centro_costos');
        $.post("imp_centrocostos.php", {
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