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
        $('#tb_subgrupos').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_subgrupos.php", function(he) {
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
                url: 'listar_subgrupos.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.nombre = $('#txt_nombre_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_subgrupo' }, //Index=0
                { 'data': 'cod_subgrupo' },
                { 'data': 'nom_subgrupo' },
                { 'data': 'cuenta_cs' },
                { 'data': 'cuenta_af' },
                { 'data': 'cuenta_dep' },
                { 'data': 'cuenta_gas' },
                { 'data': 'nom_grupo' },
                { 'data': 'es_clinico' },
                { 'data': 'lote_xdef' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2, 7] },
                { orderable: false, targets: 11 }
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
        $('#tb_subgrupos').wrap('<div class="overflow"/>');
    });

    //Buascar registros
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_subgrupos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_subgrupos');
        }
    });

    //Editar un registro    
    $('#tb_subgrupos').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_subgrupos.php", { id: id }, function(he) {
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro 
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_cod_subgrupo'));
        error += verifica_vacio($('#txt_nom_subgrupo'));
        error += verifica_vacio($('#sl_grp_subgrupo'));
        error += verifica_vacio($('#sl_lotexdef'));
        error += verifica_vacio($('#sl_estado'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_subgrupos').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_subgrupos.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_subgrupo').val() == -1) ? 0 : $('#tb_subgrupos').DataTable().page.info().page;
                    reloadtable('tb_subgrupos', pag);
                    $('#id_subgrupo').val(r.id);
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
    $('#tb_subgrupos').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('subgrupos', id);
    });

    $('#divModalConfDel').on("click", "#subgrupos", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_subgrupos.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_subgrupos').DataTable().page.info().page;
                reloadtable('tb_subgrupos', pag);
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
        reloadtable('tb_subgrupos');
        $.post("imp_subgrupos.php", {
            nombre: $('#txt_nombre_filtro').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    /* ---------------------------------------------------
    CUENTAS CONTABLES ARTICULOS CONSUMIBLES
    -----------------------------------------------------*/

    //Editar un registro 
    $('#divForms').on('click', '#tb_cuentas_cs .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_subgrupos_cta.php", { id: id }, function(he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    // Autocompletar cuenta contable 
    $('#divFormsReg').on("input", ".cuenta", function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "../common/cargar_cta_contable_ls.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term }
                }).done(function(data) {
                    response(data);
                });
            },
            minLength: 2,
            select: function(event, ui) {
                var that = $(this);
                if (ui.item.tipo == 'D' || ui.item.id == '') {
                    $('#' + that.attr('data-campoid')).val(ui.item.id);
                } else {
                    $('#' + that.attr('data-campoid')).val('-1');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe seleccionar una cuenta tipo detalle');
                }
            },
        });
    });

    //Guardar registro Cuenta
    $('#divFormsReg').on("click", "#btn_guardar_cta", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio_2($('#id_txt_cta_con'), $('#txt_cta_con'));
        error += verifica_vacio($('#txt_fec_vig'));
        error += verifica_vacio($('#sl_estado_cta'));

        var error1 = verifica_valmin_2($('#id_txt_cta_con'), $('#txt_cta_con'), 0);

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else if (error1 >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Todas las cuentas deben ser tipo detalle')
        } else {
            var data = $('#frm_reg_subgrupos_cta').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_subgrupos_cta.php',
                dataType: 'json',
                data: data + "&id_subgrupo=" + $('#id_subgrupo').val() + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_subgrupocta').val() == -1) ? 0 : $('#tb_cuentas_cs').DataTable().page.info().page;
                    reloadtable('tb_cuentas_cs', pag);
                    pag = $('#tb_subgrupos').DataTable().page.info().page;
                    reloadtable('tb_subgrupos', pag);
                    $('#id_subgrupocta').val(r.id);
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

    //Borrar un registro Cuenta
    $('#divForms').on('click', '#tb_cuentas_cs .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('cuenta_cs', id);
    });
    $('#divModalConfDel').on("click", "#cuenta_cs", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_subgrupos_cta.php',
            dataType: 'json',
            data: { id: id, id_subgrupo: $('#id_subgrupo').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_cuentas_cs').DataTable().page.info().page;
                reloadtable('tb_cuentas_cs', pag);
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
    CUENTAS CONTABLES ARTICULOS ACTIVOS FIJOS
    -----------------------------------------------------*/

    //Editar un registro 
    $('#divForms').on('click', '#tb_cuentas_af .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_subgrupos_cta_af.php", { id: id }, function(he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //Guardar registro Cuenta
    $('#divFormsReg').on("click", "#btn_guardar_cta_af", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio_2($('#id_txt_cta_con_act'), $('#txt_cta_con_act'));
        error += verifica_vacio_2($('#id_txt_cta_con_dep'), $('#txt_cta_con_dep'));
        error += verifica_vacio_2($('#id_txt_cta_con_gas'), $('#txt_cta_con_gas'));
        error += verifica_vacio($('#txt_fec_vig'));
        error += verifica_vacio($('#sl_estado_cta'));

        var error1 = verifica_valmin_2($('#id_txt_cta_con_act'), $('#txt_cta_con_act'), 0);
        error1 += verifica_valmin_2($('#id_txt_cta_con_dep'), $('#txt_cta_con_dep'), 0);
        error1 += verifica_valmin_2($('#id_txt_cta_con_gas'), $('#txt_cta_con_gas'), 0);

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else if (error1 >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Todas las cuentas deben ser tipo detalle')
        } else {
            var data = $('#frm_reg_subgrupos_cta_af').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_subgrupos_cta_af.php',
                dataType: 'json',
                data: data + "&id_subgrupo=" + $('#id_subgrupo').val() + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_subgrupocta_af').val() == -1) ? 0 : $('#tb_cuentas_af').DataTable().page.info().page;
                    reloadtable('tb_cuentas_af', pag);
                    pag = $('#tb_subgrupos').DataTable().page.info().page;
                    reloadtable('tb_subgrupos', pag);
                    $('#id_subgrupocta_af').val(r.id);
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

    //Borrar un registro Cuenta
    $('#divForms').on('click', '#tb_cuentas_af .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('cuenta_af', id);
    });
    $('#divModalConfDel').on("click", "#cuenta_af", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_subgrupos_cta_af.php',
            dataType: 'json',
            data: { id: id, id_subgrupo: $('#id_subgrupo').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_cuentas_af').DataTable().page.info().page;
                reloadtable('tb_cuentas_af', pag);
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

})(jQuery);