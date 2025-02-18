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
        $('#tb_cencos_areas').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_historialtercero.php", function(he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-xl');
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
                /*url: 'listar_cencos_areas.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.nom_area = $('#txt_nombre_filtro').val();
                    data.id_cencosto = $('#sl_centrocosto_filtro').val();
                    data.id_sede = $('#sl_sede_filtro').val();
                }*/
            },
            columns: [
                { 'data': 'id_area' }, //Index=0              
                { 'data': 'nom_area' },
                { 'data': 'nom_tipo_area' },
                { 'data': 'nom_centrocosto' },
                { 'data': 'nom_sede' },
                { 'data': 'usr_responsable' },
                { 'data': 'nom_bodega' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [1, 2] },
                { visible: false, targets: 6 },
                { orderable: false, targets: 7 }
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
        $('#tb_cencos_areas').wrap('<div class="overflow"/>');
    });


    //Buascar registros
    /*$('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_cencos_areas');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_cencos_areas');
        }
    });*/

    // Autocompletar Usuarios reposnables
    $('#divForms').on("input", "#txt_tercero_filtro", function() {
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
    $('#tb_cencos_areas').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_cencos_areas.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro 
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_nom_area'));
        error += verifica_vacio_2($('#id_txt_responsable'), $('#txt_responsable'));
        error += verifica_vacio($('#sl_centrocosto'));
        error += verifica_vacio($('#sl_sede'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_cencos_areas').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_cencos_areas.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_area').val() == -1) ? 0 : $('#tb_cencos_areas').DataTable().page.info().page;
                    reloadtable('tb_cencos_areas', pag);
                    $('#id_area').val(r.id);
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
    $('#tb_cencos_areas').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('cencos_area', id);
    });

    $('#divModalConfDel').on("click", "#cencos_area", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_cencos_areas.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_cencos_areas').DataTable().page.info().page;
                reloadtable('tb_cencos_areas', pag);
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
        reloadtable('tb_cencos_areas');
        $.post("imp_cencos_areas.php", {
            nom_area: $('#txt_nombre_filtro').val(),
            id_cencosto: $('#sl_centrocosto_filtro').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);