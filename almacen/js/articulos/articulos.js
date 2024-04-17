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
        $('#tb_articulos').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_articulos.php", function(he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-lg');
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
                url: 'listar_articulos.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.codigo = $('#txt_codigo_filtro').val();
                    data.nombre = $('#txt_nombre_filtro').val();
                    data.subgrupo = $('#sl_subgrupo_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_med' }, //Index=0
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'nom_subgrupo' },
                { 'data': 'top_min' },
                { 'data': 'top_max' },
                { 'data': 'existencia' },
                { 'data': 'val_promedio' },
                { 'data': 'es_clinico' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2, 3] },
                { orderable: false, targets: 10 }
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
        $('#tb_articulos').wrap('<div class="overflow"/>');
    });

    //Buascar registros de Articulos
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_articulos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_articulos');
        }
    });

    // Autocompletar Unidad de Medida
    $('#divForms').on("input", "#txt_unimed_art", function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "../common/cargar_unidadmedida_ls.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term }
                }).done(function(data) {
                    response(data);
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#id_txt_unimed_art').val(ui.item.id);
            }
        });
    });

    //Editar un registro Articulo
    $('#tb_articulos').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_articulos.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro Articulo
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_cod_art'));
        error += verifica_vacio($('#txt_nom_art'));
        error += verifica_vacio($('#sl_subgrp_art'));
        error += verifica_vacio($('#txt_topmin_art'));
        error += verifica_vacio($('#txt_topmax_art'));
        error += verifica_vacio_2($('#id_txt_unimed_art'), $('#txt_unimed_art'));

        if ($('input[name=rdo_escli_art]:checked').val() == 1) {
            error += verifica_vacio($('#sl_medins_art'));
        }

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_articulos').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_articulos.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_articulo').val() == -1) ? 0 : $('#tb_articulos').DataTable().page.info().page;
                    reloadtable('tb_articulos', pag);
                    $('#id_articulo').val(r.id);

                    $('#btn_imprimir').prop('disabled', false);

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

    //Borrarr un registro Articulo
    $('#tb_articulos').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('articulos', id);
    });
    $('#divModalConfDel').on("click", "#articulos", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_articulos.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_articulos').DataTable().page.info().page;
                reloadtable('tb_articulos', pag);
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

    /* ---------------------------------------------------
    CUMS
    -----------------------------------------------------*/

    //Editar un registro CUM
    $('#divForms').on('click', '#tb_articulos_cums .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_articulos_cums.php", { id: id, id_articulo: $('#id_articulo').val() }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    // Autocompletar laboratorio
    $('#divFormsReg').on("input", "#txt_lab_cum", function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "../common/cargar_laboratorio_ls.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term }
                }).done(function(data) {
                    response(data);
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#id_txt_lab_cum').val(ui.item.id);
            }
        });
    });
    // Autocompletar Presentacion Comercial
    $('#divFormsReg').on("input", "#txt_precom_cum", function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "../common/cargar_prescomercial_ls.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term }
                }).done(function(data) {
                    response(data);
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#id_txt_precom_cum').val(ui.item.id);
            }
        });
    });

    //Guardar registro CUM
    $('#divFormsReg').on("click", "#btn_guardar_cum", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_cod_cum'));
        error += verifica_vacio_2($('#id_txt_lab_cum'), $('#txt_lab_cum'));
        error += verifica_vacio_2($('#id_txt_precom_cum'), $('#txt_precom_cum'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_articulos_cums').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_articulos_cums.php',
                dataType: 'json',
                data: data + "&id_articulo=" + $('#id_articulo').val() + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_cum').val() == -1) ? 0 : $('#tb_articulos_cums').DataTable().page.info().page;
                    reloadtable('tb_articulos_cums', pag);
                    $('#id_cum').val(r.id);
                    $('#divModalReg').modal('hide');
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

    //Borrarr un registro CUM de Articulo
    $('#divForms').on('click', '#tb_articulos_cums .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('cums', id);
    });
    $('#divModalConfDel').on("click", "#cums", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_articulos_cums.php',
            dataType: 'json',
            data: { id: id, id_articulo: $('#id_articulo').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_articulos_cums').DataTable().page.info().page;
                reloadtable('tb_articulos_cums', pag);
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

    /* ---------------------------------------------------
    LOTES
    -----------------------------------------------------*/

    //Editar un registro LOTE
    $('#divForms').on('click', '#tb_articulos_lotes .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_articulos_lotes.php", { id: id, id_articulo: $('#id_articulo').val() }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    // Autocompletar Presentacion de Lote
    $('#divFormsReg').on("input", "#txt_pre_lote", function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "../common/cargar_preslote_ls.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term }
                }).done(function(data) {
                    response(data);
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#id_txt_pre_lote').val(ui.item.id);
                $('#txt_can_lote').val(ui.item.cantidad);
            }
        });
    });

    //Guardar registro LOTE
    $('#divFormsReg').on("click", "#btn_guardar_lote", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio_2($('#id_txt_nom_bod'), $('#txt_nom_bod'));
        error += verifica_vacio($('#txt_num_lot'));
        error += verifica_vacio($('#txt_fec_ven'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {

            if (!verifica_valmin($('#txt_can_gru'), 2, "El valor de la Cantidad en la Unidad debe ser mayor a 1")) {

                var data = $('#frm_reg_articulos_lotes').serialize();
                $.ajax({
                    url: 'editar_articulos_lotes.php',
                    type: 'POST',
                    dataType: 'json',
                    data: data + "&id_articulo=" + $('#id_articulo').val() + "&oper=add"
                }).done(function(r) {
                    if (r.mensaje == 'ok') {
                        let pag = ($('#id_lote').val() == -1) ? 0 : $('#tb_articulos_lotes').DataTable().page.info().page;
                        reloadtable('tb_articulos_lotes', pag);
                        $('#id_lote').val(r.id);
                        $('#divModalReg').modal('hide');
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
        }
    });

    //Borrarr un registro LOTE
    $('#divForms').on('click', '#tb_articulos_lotes .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('lotes', id);
    });
    $('#divModalConfDel').on("click", "#lotes", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_articulos_lotes.php',
            dataType: 'json',
            data: { id: id, id_articulo: $('#id_articulo').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_articulos_lotes').DataTable().page.info().page;
                reloadtable('tb_articulos_lotes', pag);
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

    //Imprimir listado de registros
    $('#btn_imprime_filtro').on('click', function() {
        reloadtable('tb_articulos');
        $.post("imp_articulos.php", {
            codigo: $('#txt_codigo_filtro').val(),
            nombre: $('#txt_nombre_filtro').val(),
            subgrupo: $('#sl_subgrupo_filtro').val(),
            estado: $('#sl_estado_filtro').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    //Imprimit un Articulo
    $('#divForms').on("click", "#btn_imprimir", function() {
        $.post("imp_articulo.php", {
            id: $('#id_articulo').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);