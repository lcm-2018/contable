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
        $('#tb_tipos_orden_egreso').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_tipos_orden_egreso.php", function(he) {
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
                url: 'listar_tipos_orden_egreso.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.nombre = $('#txt_nombre_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_tipo_egreso' }, //Index=0
                { 'data': 'nom_tipo_egreso' },
                { 'data': 'cuenta_c' },
                { 'data': 'es_int_ext' },
                { 'data': 'con_pedido' },
                { 'data': 'dev_fianza' },
                { 'data': 'consumo' },
                { 'data': 'farmacia' },
                { 'data': 'almacen' },
                { 'data': 'activofijo' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [1] },
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
        $('#tb_tipos_orden_egreso').wrap('<div class="overflow"/>');
    });

    //Buascar registros
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_tipos_orden_egreso');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_tipos_orden_egreso');
        }
    });

    //Editar un registro    
    $('#tb_tipos_orden_egreso').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_tipos_orden_egreso.php", { id: id }, function(he) {
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
        var error = verifica_vacio($('#txt_nom_tipoegreso'));
        error += verifica_vacio($('#sl_esintext'));
        error += verifica_vacio($('#sl_conpedido'));
        error += verifica_vacio($('#sl_devfianza'));
        error += verifica_vacio($('#sl_consumo'));
        error += verifica_vacio($('#sl_farmacia'));
        error += verifica_vacio($('#sl_almacen'));
        error += verifica_vacio($('#sl_activofijo'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_tipos_orden_egreso').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_tipos_orden_egreso.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_tipo_egreso').val() == -1) ? 0 : $('#tb_tipos_orden_egreso').DataTable().page.info().page;
                    reloadtable('tb_tipos_orden_egreso', pag);
                    $('#id_tipo_egreso').val(r.id);
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
    $('#tb_tipos_orden_egreso').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('tipos_orden_egreso', id);
    });

    $('#divModalConfDel').on("click", "#tipos_orden_egreso", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_tipos_orden_egreso.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_tipos_orden_egreso').DataTable().page.info().page;
                reloadtable('tb_tipos_orden_egreso', pag);
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
        reloadtable('tb_tipos_orden_egreso');
        $.post("imp_tipos_orden_egreso.php", {
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
    $('#divForms').on('click', '#tb_cuentas_c .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_tipos_orden_egreso_cta.php", { id: id }, function(he) {
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
            var data = $('#frm_reg_tipos_orden_egreso_cta').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_tipos_orden_egreso_cta.php',
                dataType: 'json',
                data: data + "&id_tipo_egreso=" + $('#id_tipo_egreso').val() + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_tipo_egreso_cta').val() == -1) ? 0 : $('#tb_cuentas_c').DataTable().page.info().page;
                    reloadtable('tb_cuentas_c', pag);
                    pag = $('#tb_tipos_orden_egreso').DataTable().page.info().page;
                    reloadtable('tb_tipos_orden_egreso', pag);
                    $('#id_tipo_egreso_cta').val(r.id);
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
    $('#divForms').on('click', '#tb_cuentas_c .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('cuenta_c', id);
    });
    $('#divModalConfDel').on("click", "#cuenta_c", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_tipos_orden_egreso_cta.php',
            dataType: 'json',
            data: { id: id, id_tipo_egreso: $('#id_tipo_egreso').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_cuentas_c').DataTable().page.info().page;
                reloadtable('tb_cuentas_c', pag);
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