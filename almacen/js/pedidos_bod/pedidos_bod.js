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
        $('#tb_pedidos').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_pedidos.php", function(he) {
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
                url: 'listar_pedidos.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_sedsol = $('#sl_sedsol_filtro').val();
                    data.id_bodsol = $('#sl_bodsol_filtro').val();
                    data.id_pedido = $('#txt_id_pedido_filtro').val();
                    data.num_pedido = $('#txt_num_pedido_filtro').val();
                    data.fec_ini = $('#txt_fecini_filtro').val();
                    data.fec_fin = $('#txt_fecfin_filtro').val();
                    data.id_sedpro = $('#sl_sedpro_filtro').val();
                    data.id_bodpro = $('#sl_bodpro_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_pedido' }, //Index=0
                { 'data': 'num_pedido' },
                { 'data': 'fec_pedido' },
                { 'data': 'hor_pedido' },
                { 'data': 'detalle' },
                { 'data': 'nom_sede_solicita' },
                { 'data': 'nom_bodega_solicita' },
                { 'data': 'nom_sede_provee' },
                { 'data': 'nom_bodega_provee' },
                { 'data': 'val_total' },
                { 'data': 'estado' },
                { 'data': 'nom_estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [4, 5, 6, 7, 8] },
                { type: "numeric-comma", targets: 9 },
                { visible: false, targets: 10 },
                { orderable: false, targets: 12 }
            ],
            rowCallback: function(row, data) {
                if (data.estado == 1) {
                    $($(row).find("td")[0]).css("background-color", "yellow");
                } else if (data.estado == 0) {
                    $($(row).find("td")[0]).css("background-color", "gray");
                }
            },
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_pedidos').wrap('<div class="overflow"/>');
    });

    //Filtrar las Bodegas acorde a la Sede y Usuario de sistema
    $('#sl_sedsol_filtro').on("change", function() {
        $('#sl_bodsol_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega Solititante--' }, function() {});
    });
    $('#sl_sedsol_filtro').trigger('change');

    $('#sl_sedpro_filtro').on("change", function() {
        $('#sl_bodpro_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega Proveedor--', todas: true }, function() {});
    });
    $('#sl_sedpro_filtro').trigger('change');

    $('#divForms').on("change", "#sl_sede_solicitante", function() {
        $('#sl_bodega_solicitante').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val() }, function() {});
    });
    $('#divForms').on("change", "#sl_sede_proveedor", function() {
        $('#sl_bodega_proveedor').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), todas: true }, function() {});
    });

    //Buscar registros de Pedido
    $('#btn_buscar_filtro').on("click", function() {
        $('.is-invalid').removeClass('is-invalid');
        reloadtable('tb_pedidos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_pedidos');
        }
    });

    //Editar un registro Pedido
    $('#tb_pedidos').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_pedidos.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro Pedido
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#sl_sede_solicitante'));
        error += verifica_vacio($('#sl_bodega_solicitante'));
        error += verifica_vacio($('#sl_sede_proveedor'));
        error += verifica_vacio($('#sl_bodega_proveedor'));
        error += verifica_vacio($('#txt_det_pedido'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            if ($('#sl_bodega_solicitante').val() == $('#sl_bodega_proveedor').val()) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('La Bodega que Solicita y la Bodega Proveedora deben ser diferentes');
            } else {
                var data = $('#frm_reg_pedidos').serialize();
                $.ajax({
                    type: 'POST',
                    url: 'editar_pedidos.php',
                    dataType: 'json',
                    data: data + "&oper=add"
                }).done(function(r) {
                    if (r.mensaje == 'ok') {
                        let pag = ($('#id_pedido').val() == -1) ? 0 : $('#tb_pedidos').DataTable().page.info().page;
                        reloadtable('tb_pedidos', pag);
                        $('#id_pedido').val(r.id);
                        $('#txt_ide').val(r.id);

                        $('#btn_cerrar').prop('disabled', false);
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
        }
    });

    //Borrar un registro Pedido
    $('#tb_pedidos').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('pedidos_del', id);
    });
    $('#divModalConfDel').on("click", "#pedidos_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_pedidos.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_pedidos').DataTable().page.info().page;
                reloadtable('tb_pedidos', pag);
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

    //Cerrar un registro Pedido
    $('#divForms').on("click", "#btn_cerrar", function() {
        let id = $(this).attr('value');
        confirmar_proceso('pedidos_close', id);
    });
    $('#divModalConfDel').on("click", "#pedidos_close", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_pedidos.php',
            dataType: 'json',
            data: { id: $('#id_pedido').val(), oper: 'close' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_pedidos').DataTable().page.info().page;
                reloadtable('tb_pedidos', pag);

                $('#txt_num_pedido').val(r.num_pedido);
                $('#txt_est_pedido').val('CERRADO');

                $('#btn_guardar').prop('disabled', true);
                $('#btn_cerrar').prop('disabled', true);
                $('#btn_anular').prop('disabled', false);

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

    //Anular un registro Pedido
    $('#divForms').on("click", "#btn_anular", function() {
        let id = $(this).attr('value');
        confirmar_proceso('pedidos_annul', id);
    });
    $('#divModalConfDel').on("click", "#pedidos_annul", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_pedidos.php',
            dataType: 'json',
            data: { id: $('#id_pedido').val(), oper: 'annul' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_pedidos').DataTable().page.info().page;
                reloadtable('tb_pedidos', pag);

                $('#txt_est_pedido').val('ANULADO');

                $('#btn_guardar').prop('disabled', true);
                $('#btn_cerrar').prop('disabled', true);
                $('#btn_anular').prop('disabled', true);

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
    DETALLES
    -----------------------------------------------------*/
    $('#divModalBus').on('dblclick', '#tb_articulos_bodega tr', function() {
        let id_med = $(this).find('td:eq(0)').text();
        $.post("frm_reg_pedidos_detalles.php", { id_med: id_med }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);

        });
    });

    $('#divForms').on('click', '#tb_pedidos_detalles .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_pedidos_detalles.php", { id: id }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //Guardar registro Detalle
    $('#divFormsReg').on("click", "#btn_guardar_detalle", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_can_ped'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else if (!verifica_valmin($('#txt_can_ped'), 1, "La cantidad debe ser mayor igual a 1")) {
            var data = $('#frm_reg_pedidos_detalles').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_pedidos_detalles.php',
                dataType: 'json',
                data: data + "&id_pedido=" + $('#id_pedido').val() + '&oper=add'
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_detalle').val() == -1) ? 0 : $('#tb_pedidos_detalles').DataTable().page.info().page;
                    reloadtable('tb_pedidos_detalles', pag);
                    pag = $('#tb_pedidos').DataTable().page.info().page;
                    reloadtable('tb_pedidos', pag);

                    $('#id_detalle').val(r.id);
                    $('#txt_val_tot').val(r.val_total);

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

    //Borrarr un registro Detalle
    $('#divForms').on('click', '#tb_pedidos_detalles .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('detalle', id);
    });
    $('#divModalConfDel').on("click", "#detalle", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_pedidos_detalles.php',
            dataType: 'json',
            data: { id: id, id_pedido: $('#id_pedido').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_pedidos_detalles').DataTable().page.info().page;
                reloadtable('tb_pedidos_detalles', pag);
                pag = $('#tb_pedidos').DataTable().page.info().page;
                reloadtable('tb_pedidos', pag);

                $('#txt_val_tot').val(r.val_total);

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
        reloadtable('tb_pedidos');
        $('.is-invalid').removeClass('is-invalid');
        var verifica = verifica_vacio($('#txt_fecini_filtro'));
        verifica += verifica_vacio($('#txt_fecfin_filtro'));
        if (verifica >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe especificar un rango de fechas');
        } else {
            $.post("imp_pedidos.php", {
                id_sedsol: $('#sl_sedsol_filtro').val(),
                id_bodsol: $('#sl_bodsol_filtro').val(),
                id_pedido: $('#txt_id_pedido_filtro').val(),
                num_pedido: $('#txt_num_pedido_filtro').val(),
                fec_ini: $('#txt_fecini_filtro').val(),
                fec_fin: $('#txt_fecfin_filtro').val(),
                id_sedpro: $('#sl_sedpro_filtro').val(),
                id_bodpro: $('#sl_bodpro_filtro').val(),
                estado: $('#sl_estado_filtro').val()
            }, function(he) {
                $('#divTamModalImp').removeClass('modal-sm');
                $('#divTamModalImp').removeClass('modal-lg');
                $('#divTamModalImp').addClass('modal-xl');
                $('#divModalImp').modal('show');
                $("#divImp").html(he);
            });
        }
    });

    //Imprimit un Pedido
    $('#divForms').on("click", "#btn_imprimir", function() {
        $.post("imp_pedido.php", {
            id: $('#id_pedido').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);