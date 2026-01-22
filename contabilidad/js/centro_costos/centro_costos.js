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
                { class: 'text-wrap', targets: [1, 2] },
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
            $('#divTamModalForms').addClass('modal-xl');
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

    /* ---------------------------------------------------
    CUENTAS CONTABLES CENTRO DE COSTO
    -----------------------------------------------------*/

    //Editar un registro 
    $('#divForms').on('click', '#tb_cuentas .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_centrocostos_cta.php", { id: id }, function(he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    // Autocompletar cuenta contable     
    $('#divFormsReg,#divFormsBus').on("input", ".cuenta", function() {
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
            var data = $('#frm_reg_centrocostos_cta').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_centrocostos_cta.php',
                dataType: 'json',
                data: data + "&id_cencos=" + $('#id_centrocosto').val() + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_ceccta').val() == -1) ? 0 : $('#tb_cuentas').DataTable().page.info().page;
                    reloadtable('tb_cuentas', pag);
                    pag = $('#tb_centro_costos').DataTable().page.info().page;
                    reloadtable('tb_centro_costos', pag);
                    $('#id_ceccta').val(r.id);
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
    $('#divForms').on('click', '#tb_cuentas .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('cuenta', id);
    });
    $('#divModalConfDel').on("click", "#cuenta", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_centrocostos_cta.php',
            dataType: 'json',
            data: { id: id, id_cencos: $('#id_centrocosto').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_cuentas').DataTable().page.info().page;
                reloadtable('tb_cuentas', pag);
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
    CUENTAS CONTABLES POR SUBGRUPO ENCABEZADO
    -----------------------------------------------------*/

    //Editar un registro 
    $('#divForms').on('click', '#tb_cuentas_sg .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_centrocostos_sg.php", { id: id }, function(he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //Guardar registro Subgrupo
    $('#divFormsReg').on("click", "#btn_guardar_sg", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_fec_vig'));
        error += verifica_vacio($('#sl_estado_cta'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_centrocostos_sg').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_centrocostos_sg.php',
                dataType: 'json',
                data: data + "&id_cencos=" + $('#id_centrocosto').val() + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_cec_sg').val() == -1) ? 0 : $('#tb_cuentas_sg').DataTable().page.info().page;
                    reloadtable('tb_cuentas_sg', pag);
                    $('#id_cec_sg').val(r.id);
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

    //Borrar un registro Subgrupo
    $('#divForms').on('click', '#tb_cuentas_sg .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('cuenta_sg', id);
    });
    $('#divModalConfDel').on("click", "#cuenta_sg", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_centrocostos_sg.php',
            dataType: 'json',
            data: { id: id, id_cencos: $('#id_centrocosto').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_cuentas_sg').DataTable().page.info().page;
                reloadtable('tb_cuentas_sg', pag);
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
    CUENTAS CONTABLES POR SUBGRUPO - DETALLE
    -----------------------------------------------------*/

    //Editar un registro 
    $('#divFormsReg').on('click', '#tb_cuentas_sg_det .btn_editar', function() {
        let id = $(this).attr('value');
        let fila = $(this).closest('tr');
        let data = $('#tb_cuentas_sg_det').DataTable().row(fila).data();
        $.post("frm_reg_centrocostos_sg_cta.php", { id: id, id_subgrupo: data.id_subgrupo }, function(he) {
            $('#divTamModalBus').removeClass('modal-xl');
            $('#divTamModalBus').removeClass('modal-sm');
            $('#divTamModalBus').addClass('modal-lg');
            $('#divModalBus').modal('show');
            $("#divFormsBus").html(he);
        });
    });

    //Guardar registro Cuenta
    $('#divTamModalBus').on("click", "#btn_guardar_sg_cta", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio_2($('#id_txt_cta_con'), $('#txt_cta_con'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_centrocostos_sg_cta').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_centrocostos_sg_cta.php',
                dataType: 'json',
                data: data + "&id_cec_sg=" + $('#id_cec_sg').val() + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_cec_sgcta').val() == -1) ? 0 : $('#tb_cuentas_sg_det').DataTable().page.info().page;
                    reloadtable('tb_cuentas_sg_det', pag);
                    $('#id_cec_sgcta').val(r.id);
                    $('#divModalBus').modal('hide');
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

    //Borrarr un registro Cuenta
    $('#divFormsReg').on('click', '#tb_cuentas_sg_det .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('cuenta_sg_det', id);
    });
    $('#divModalConfDel').on("click", "#cuenta_sg_det", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_centrocostos_sg_cta.php',
            dataType: 'json',
            data: { id: id, id_cec_sg: $('#id_cec_sg').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_cuentas_sg_det').DataTable().page.info().page;
                reloadtable('tb_cuentas_sg_det', pag);
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