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
        $('#tb_egresos').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_egresos.php", function(he) {
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
                url: 'listar_egresos.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_sede = $('#sl_sede_filtro').val();
                    data.id_bodega = $('#sl_bodega_filtro').val();
                    data.id_egr = $('#txt_idegr_filtro').val();
                    data.num_egr = $('#txt_numegr_filtro').val();
                    data.fec_ini = $('#txt_fecini_filtro').val();
                    data.fec_fin = $('#txt_fecfin_filtro').val();
                    data.id_tercero = $('#sl_tercero_filtro').val();
                    data.id_cencost = $('#sl_centrocosto_filtro').val();
                    data.id_tipegr = $('#sl_tipegr_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_egreso' }, //Index=0
                { 'data': 'num_egreso' },
                { 'data': 'fec_egreso' },
                { 'data': 'hor_egreso' },
                { 'data': 'detalle' },
                { 'data': 'nom_tipo_egreso' },
                { 'data': 'nom_tercero' },
                { 'data': 'nom_centro' },
                { 'data': 'nom_sede' },
                { 'data': 'nom_bodega' },
                { 'data': 'val_total' },
                { 'data': 'estado' },
                { 'data': 'nom_estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [4, 6, 7, 8, 9] },
                { type: "numeric-comma", targets: 10 },
                { visible: false, targets: 11 },
                { orderable: false, targets: 13 }
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
        $('#tb_egresos').wrap('<div class="overflow"/>');
    });

    //Filtrar las Bodegas acorde a la Sede y Usuario de sistema
    $('#sl_sede_filtro').on("change", function() {
        $('#sl_bodega_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega--' }, function() {});
    });
    $('#sl_sede_filtro').trigger('change');

    //Buascar registros de Egresos
    $('#btn_buscar_filtro').on("click", function() {
        $('.is-invalid').removeClass('is-invalid');
        reloadtable('tb_egresos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_egresos');
        }
    });

    /* ---------------------------------------------------
    EGRESO EN BASE A UN PEDIDO
    -----------------------------------------------------*/
    //Seleccionar un Pedido para hacer el traslado
    $('#divForms').on("dblclick", "#txt_des_pedido", function() {
        $.post("buscar_pedidos_frm.php", function(he) {
            $('#divTamModalBus').removeClass('modal-sm');
            $('#divTamModalBus').removeClass('modal-lg');
            $('#divTamModalBus').addClass('modal-xl');
            $('#divModalBus').modal('show');
            $("#divFormsBus").html(he);
        });
    });

    $('#divModalBus').on('dblclick', '#tb_pedidos_egr tr', function() {
        let data = $('#tb_pedidos_egr').DataTable().row(this).data();
        $('#txt_id_pedido').val(data.id_pedido);
        $('#txt_des_pedido').val(data.detalle + '(' + data.fec_pedido + ')');

        if (data.id_pedido) {
            $('#sl_sede_egr').val(data.id_sede).prop('disabled', true);
            $('#id_sede_egr').val(data.id_sede);
            $('#sl_bodega_egr').load('../common/cargar_bodegas_usuario.php', { id_sede: data.id_sede }, function() {
                $(this).val(data.id_bodega).prop('disabled', true);
                $('#id_bodega_egr').val(data.id_bodega);
            });
            $('#sl_centrocosto').val(data.id_cencosto);
        }
        $('#divModalBus').modal('hide');
    });

    $('#divModalBus').on('click', '#tb_pedidos_egr .btn_imprimir', function() {
        let id = $(this).attr('value');
        $.post("../pedidos_cec/imp_pedido.php", { id: id }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    $('#divForms').on("click", "#btn_cancelar_pedido", function() {
        let table = $('#tb_egresos_detalles').DataTable();
        let filas = table.rows().count();
        if (filas == 0) {
            $('#txt_id_pedido').val('');
            $('#txt_des_pedido').val('');
            $('#sl_sede_egr').prop('disabled', false);
            $('#sl_bodega_egr').prop('disabled', false);
        }
    });

    //Imprimit el Pedido
    $('#divForms').on("click", "#btn_imprime_pedido", function() {
        let id = $('#txt_id_pedido').val();
        if (id) {
            $.post("../pedidos_cec/imp_pedido.php", { id: id }, function(he) {
                $('#divTamModalImp').removeClass('modal-sm');
                $('#divTamModalImp').removeClass('modal-lg');
                $('#divTamModalImp').addClass('modal-xl');
                $('#divModalImp').modal('show');
                $("#divImp").html(he);
            });
        }
    });

    /* ---------------------------------------------------
    ENCABEZADO DE UN EGRESO
    -----------------------------------------------------*/

    $('#divForms').on("change", "#sl_sede_egr", function() {
        $('#sl_bodega_egr').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val() }, function() {});
        $('#id_sede_egr').val($('#sl_sede_egr').val());
    });

    $('#divForms').on("change", "#sl_bodega_egr", function() {
        $('#id_bodega_egr').val($('#sl_bodega_egr').val());
    });

    //Editar un registro Orden Egreso
    $('#tb_egresos').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_egresos.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro Egreso
    $('#divForms').on("click", "#btn_guardar", function() {
        let table = $('#tb_egresos_detalles').DataTable();
        let filas = table.rows().count();
        let id_pedido = $('#txt_id_pedido').val();

        if (id_pedido && filas == 0) {
            confirmar_proceso_msg('egreso_pedido', 'Desea Generar el Egreso en base al Pedido ' + id_pedido);
        } else {
            guardar_egreso(0);
        }
    });

    $('#divModalConfDel').on("click", "#egreso_pedido", function() {
        $('#divModalConfDel').modal('hide');
        guardar_egreso(1);
    });

    function guardar_egreso(generar_egreso) {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#sl_sede_egr'));
        error += verifica_vacio($('#sl_bodega_egr'));
        error += verifica_vacio($('#sl_tip_egr'));
        if ($('#sl_tip_egr').find('option:selected').attr('data-intext') == 2) {
            error += verifica_vacio($('#sl_tercero'));
        } else {
            error += verifica_vacio($('#sl_centrocosto'));
        }

        error += verifica_vacio($('#txt_det_egr'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_orden_egreso').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_egresos.php',
                dataType: 'json',
                data: data + "&oper=add" + '&generar_egreso=' + generar_egreso
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_egreso').val() == -1) ? 0 : $('#tb_egresos').DataTable().page.info().page;
                    reloadtable('tb_egresos', pag);

                    if (generar_egreso == 1) reloadtable('tb_egresos_detalles');

                    $('#id_egreso').val(r.id);
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
    };

    //Borrar un registro Orden Egreso
    $('#tb_egresos').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('egresos_del', id);
    });
    $('#divModalConfDel').on("click", "#egresos_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_egresos.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_egresos').DataTable().page.info().page;
                reloadtable('tb_egresos', pag);
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

    //Cerrar un registro Orden Egreso
    $('#divForms').on("click", "#btn_cerrar", function() {
        confirmar_proceso('egresos_close');
    });
    $('#divModalConfDel').on("click", "#egresos_close", function() {
        $.ajax({
            type: 'POST',
            url: 'editar_egresos.php',
            dataType: 'json',
            data: { id: $('#id_egreso').val(), oper: 'close' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_egresos').DataTable().page.info().page;
                reloadtable('tb_egresos', pag);

                $('#txt_num_egr').val(r.num_egreso);
                $('#txt_est_egr').val('CERRADO');

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

    //Anular un registro Orden Egreso
    $('#divForms').on("click", "#btn_anular", function() {
        confirmar_proceso('egresos_annul');
    });
    $('#divModalConfDel').on("click", "#egresos_annul", function() {
        $.ajax({
            type: 'POST',
            url: 'editar_egresos.php',
            dataType: 'json',
            data: { id: $('#id_egreso').val(), oper: 'annul' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_egresos').DataTable().page.info().page;
                reloadtable('tb_egresos', pag);

                $('#txt_est_egr').val('ANULADO');

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
    $('#divModalBus').on('dblclick', '#tb_lotes_articulos tr', function() {
        let id_lote = $(this).find('td:eq(0)').text();
        $.post("frm_reg_egresos_detalles.php", { id_lote: id_lote }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);

        });
    });

    $('#divForms').on('click', '#tb_egresos_detalles .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_egresos_detalles.php", { id: id }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //Guardar registro Detalle
    $('#divFormsReg').on("click", "#btn_guardar_detalle", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_can_egr'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else if (!verifica_valmin($('#txt_can_egr'), 1, "La cantidad debe ser mayor igual a 1")) {
            var data = $('#frm_reg_egresos_detalles').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_egresos_detalles.php',
                dataType: 'json',
                data: data + "&id_egreso=" + $('#id_egreso').val() + "&id_bodega=" + $('#sl_bodega_egr').val() + '&oper=add'
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_detalle').val() == -1) ? 0 : $('#tb_egresos_detalles').DataTable().page.info().page;
                    reloadtable('tb_egresos_detalles', pag);
                    pag = $('#tb_egresos').DataTable().page.info().page;
                    reloadtable('tb_egresos', pag);

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
    $('#divForms').on('click', '#tb_egresos_detalles .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('detalle', id);
    });
    $('#divModalConfDel').on("click", "#detalle", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_egresos_detalles.php',
            dataType: 'json',
            data: { id: id, id_egreso: $('#id_egreso').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_egresos_detalles').DataTable().page.info().page;
                reloadtable('tb_egresos_detalles', pag);
                pag = $('#tb_egresos').DataTable().page.info().page;
                reloadtable('tb_egresos', pag);

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
        reloadtable('tb_egresos');
        $('.is-invalid').removeClass('is-invalid');
        var verifica = verifica_vacio($('#txt_fecini_filtro'));
        verifica += verifica_vacio($('#txt_fecfin_filtro'));
        if (verifica >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe especificar un rango de fechas');
        } else {
            $.post("imp_egresos.php", {
                id_sede: $('#sl_sede_filtro').val(),
                id_bodega: $('#sl_bodega_filtro').val(),
                id_egr: $('#txt_idegr_filtro').val(),
                num_egr: $('#txt_numegr_filtro').val(),
                fec_ini: $('#txt_fecini_filtro').val(),
                fec_fin: $('#txt_fecfin_filtro').val(),
                id_tercero: $('#sl_tercero_filtro').val(),
                id_depende: $('#sl_centrocosto_filtro').val(),
                id_tipegr: $('#sl_tipegr_filtro').val(),
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

    //Imprimit una Orden de Egreso
    $('#divForms').on("click", "#btn_imprimir", function() {
        $.post("imp_egreso.php", {
            id: $('#id_egreso').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);