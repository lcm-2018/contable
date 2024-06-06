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
                    data.id_pedido = $('#txt_id_pedido_filtro').val();
                    data.num_pedido = $('#txt_num_pedido_filtro').val();
                    data.fec_ini = $('#txt_fecini_filtro').val();
                    data.fec_fin = $('#txt_fecfin_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_pedido' }, //Index=0
                { 'data': 'num_pedido' },
                { 'data': 'fec_pedido' },
                { 'data': 'hor_pedido' },
                { 'data': 'detalle' },
                { 'data': 'val_total' },
                { 'data': 'nom_sede' },
                { 'data': 'nom_bodega' },
                { 'data': 'nom_estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [4] },
                { type: "numeric-comma", targets: 5 },
                { orderable: false, targets: 9 }
            ],
            rowCallback: function(row, data) {
                var estado = $($(row).find("td")[8]).text();
                if (estado == 'PENDIENTE') {
                    $($(row).find("td")[0]).css("background-color", "yellow");
                } else if (estado == 'CONFIRMADO') {
                    $($(row).find("td")[0]).css("background-color", "cyan");
                } else if (estado == 'ACEPTADO') {
                    $($(row).find("td")[0]).css("background-color", "teal");
                } else if (estado == 'ANULADO') {
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

        var error = verifica_vacio($('#txt_det_ped'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
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

                    $('#btn_confirmar').prop('disabled', false);
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

    //Confirmar un registro Pedido
    $('#divForms').on("click", "#btn_confirmar", function() {
        let id = $(this).attr('value');
        confirmar_proceso('pedidos_confirmar', id);
    });
    $('#divModalConfDel').on("click", "#pedidos_confirmar", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_pedidos.php',
            dataType: 'json',
            data: { id: $('#id_pedido').val(), oper: 'conf' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_pedidos').DataTable().page.info().page;
                reloadtable('tb_pedidos', pag);

                $('#txt_num_ped').val(r.num_pedido);
                $('#txt_est_ped').val('CONFIRMADO');

                $('#btn_guardar').prop('disabled', true);
                $('#btn_confirmar').prop('disabled', true);
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

    //Cerrar un registro Pedido
    $('#divForms').on("click", "#btn_cerrar", function() {
        let id = $(this).attr('value');
        confirmar_proceso('pedidos_cerrar', id);
    });
    $('#divModalConfDel').on("click", "#pedidos_cerrar", function() {
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

                $('#txt_num_ped').val(r.num_pedido);
                $('#txt_est_ped').val('CONFIRMADO');

                $('#btn_guardar').prop('disabled', true);
                $('#btn_confirmar').prop('disabled', true);
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

                $('#txt_est_ped').val('ANULADO');

                $('#btn_guardar').prop('disabled', true);
                $('#btn_confirmar').prop('disabled', true);
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
    $('#divModalBus').on('dblclick', '#tb_articulos_activos tr', function() {
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
                id_pedido: $('#txt_id_pedido_filtro').val(),
                num_pedido: $('#txt_num_pedido_filtro').val(),
                fec_ini: $('#txt_fecini_filtro').val(),
                fec_fin: $('#txt_fecfin_filtro').val(),
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