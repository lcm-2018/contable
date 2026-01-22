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
        $('#tb_ingresos').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_ingresos.php", function(he) {
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
                url: 'listar_ingresos.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_ing = $('#txt_iding_filtro').val();
                    data.num_ing = $('#txt_numing_filtro').val();
                    data.num_fac = $('#txt_numfac_filtro').val();
                    data.fec_ini = $('#txt_fecini_filtro').val();
                    data.fec_fin = $('#txt_fecfin_filtro').val();
                    data.id_tercero = $('#sl_tercero_filtro').val();
                    data.id_tiping = $('#sl_tiping_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                    data.modulo = $('#sl_modulo_origen').val();
                }
            },
            columns: [
                { 'data': 'id_ingreso' }, //Index=0
                { 'data': 'num_ingreso' },
                { 'data': 'fec_ingreso' },
                { 'data': 'hor_ingreso' },
                { 'data': 'num_factura' },
                { 'data': 'fec_factura' },
                { 'data': 'detalle' },
                { 'data': 'nom_tipo_ingreso' },
                { 'data': 'nom_tercero' },
                { 'data': 'nom_sede' },
                { 'data': 'nom_bodega' },
                { 'data': 'val_total' },
                { 'data': 'estado' },
                { 'data': 'nom_estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [6, 7, 8, 9, 10] },
                { type: "numeric-comma", targets: 11 },
                { visible: false, targets: 12 },
                { orderable: false, targets: 14 }
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
        $('#tb_ingresos').wrap('<div class="overflow"/>');
    });

    //Buscar registros de Ingresos
    $('#btn_buscar_filtro').on("click", function() {
        $('.is-invalid').removeClass('is-invalid');
        reloadtable('tb_ingresos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_ingresos');
        }
    });

    /* ---------------------------------------------------
    INGRESO EN BASE A UN PEDIDO
    -----------------------------------------------------*/
    // Activar campos para Orden de Compra y seleccionar el pedido de orden de compra
    $('#divForms').on("change", "#sl_tip_ing", function() {
        if ($(this).find('option:selected').attr('data-ordcom') == 1) {
            $('#divPedido').show();
        } else {
            $('#divPedido').hide();
        }
        if ($('#divPedido').is(':visible') && $('#txt_id_pedido').val() == "") {
            let id_sede = $('#id_txt_sede').val(),
                id_bodega = $('#id_txt_nom_bod').val();

            $.post("buscar_pedidos_frm.php", { id_sede: id_sede, id_bodega: id_bodega }, function(he) {
                $('#divTamModalBus').removeClass('modal-sm');
                $('#divTamModalBus').removeClass('modal-xl');
                $('#divTamModalBus').addClass('modal-lg');
                $('#divModalBus').modal('show');
                $("#divFormsBus").html(he);
            });
        }
    });

    $('#divForms').on("dblclick", "#txt_des_pedido", function() {
        if ($('#divPedido').is(':visible')) {
            let id_sede = $('#id_txt_sede').val(),
                id_bodega = $('#id_txt_nom_bod').val();

            $.post("buscar_pedidos_frm.php", { id_sede: id_sede, id_bodega: id_bodega }, function(he) {
                $('#divTamModalBus').removeClass('modal-sm');
                $('#divTamModalBus').removeClass('modal-xl');
                $('#divTamModalBus').addClass('modal-lg');
                $('#divModalBus').modal('show');
                $("#divFormsBus").html(he);
            });
        }
    });

    $('#divModalBus').on('dblclick', '#tb_pedidos_ing tr', function() {
        let data = $('#tb_pedidos_ing').DataTable().row(this).data();
        $('#txt_id_pedido').val(data.id_pedido);
        $('#txt_des_pedido').val(data.detalle + '(' + data.fec_pedido + ')');
        $('#divModalBus').modal('hide');
    });

    $('#divForms').on("click", "#btn_cancelar_pedido", function() {
        let table = $('#tb_ingresos_detalles').DataTable();
        let filas = table.rows().count();
        if (filas == 0) {
            $('#txt_id_pedido').val('');
            $('#txt_des_pedido').val('');
        }
    });

    $('#divModalBus').on('click', '#tb_pedidos_ing .btn_imprimir', function() {
        let id = $(this).attr('value');
        $.post("imp_pedido.php", { id: id }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    //Imprimit el Pedido
    $('#divForms').on("click", "#btn_imprime_pedido", function() {
        let id = $('#txt_id_pedido').val();
        if (id) {
            $.post("imp_pedido.php", { id: id }, function(he) {
                $('#divTamModalImp').removeClass('modal-sm');
                $('#divTamModalImp').removeClass('modal-lg');
                $('#divTamModalImp').addClass('modal-xl');
                $('#divModalImp').modal('show');
                $("#divImp").html(he);
            });
        }
    });

    /* ---------------------------------------------------
    ENCABEZADO DE UN INGRESO
    -----------------------------------------------------*/

    $('#divForms').on("change", "#sl_tip_ing", function() {
        $('#id_tip_ing').val($('#sl_tip_ing').val());
    });

    // Autocompletar Terceros
    $('#divForms').on("input", "#txt_tercero", function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "../common/cargar_terceros_ls.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term }
                }).done(function(data) {
                    response(data);
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#id_txt_tercero').val(ui.item.id);
            }
        });
    });

    //Editar un registro Orden Ingreso
    $('#tb_ingresos').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_ingresos.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    function valor_aproximado(){
        var valtot = $('#txt_val_tot').val() ? $('#txt_val_tot').val() : 0,
            valpes = $('#txt_val_aprpeso').val() ? $('#txt_val_aprpeso').val() : 0;
        var valapr = parseFloat(valtot) + parseFloat(valpes);
        $('#txt_val_tot_apr').val(
          new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 2 }).format(valapr)
        );
    }

    $('#divForms').on('input', '#txt_val_aprpeso', function() {
        valor_aproximado();
    });

    //Guardar registro Orden Ingreso
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio_2($('#id_txt_nom_bod'), $('#txt_nom_bod'));
        error += verifica_vacio($('#txt_num_fac'));
        error += verifica_vacio($('#txt_fec_fac'));
        error += verifica_vacio($('#sl_tip_ing'));

        if ($('#sl_tip_ing').find('option:selected').attr('data-intext') == 2) {
            error += verifica_valmin_2($('#id_txt_tercero'), $('#txt_tercero'), 1);
        }
        if ($('#sl_tip_ing').find('option:selected').attr('data-ordcom') == 1) {
            error += verifica_vacio($('#txt_des_pedido'));
        }

        error += verifica_vacio($('#txt_det_ing'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_orden_ingreso').serialize() + '&' + $('#frm_reg_orden_ingreso_total').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_ingresos.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_ingreso').val() == -1) ? 0 : $('#tb_ingresos').DataTable().page.info().page;
                    reloadtable('tb_ingresos', pag);
                    $('#id_ingreso').val(r.id);
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
    });

    //Borrar un registro Orden Ingreso
    $('#tb_ingresos').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('ingresos_del', id);
    });
    $('#divModalConfDel').on("click", "#ingresos_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_ingresos.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_ingresos').DataTable().page.info().page;
                reloadtable('tb_ingresos', pag);
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

    //Cerrar un registro Orden Ingreso
    $('#divForms').on("click", "#btn_cerrar", function() {
        confirmar_proceso('ingresos_close');
    });
    $('#divModalConfDel').on("click", "#ingresos_close", function() {
        $.ajax({
            type: 'POST',
            url: 'editar_ingresos.php',
            dataType: 'json',
            data: { id: $('#id_ingreso').val(), oper: 'close' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_ingresos').DataTable().page.info().page;
                reloadtable('tb_ingresos', pag);

                $('#txt_num_ing').val(r.num_ingreso);
                $('#txt_est_ing').val('CERRADO');

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

    //Anular un registro Orden Ingreso
    $('#divForms').on("click", "#btn_anular", function() {
        confirmar_proceso('ingresos_annul');
    });
    $('#divModalConfDel').on("click", "#ingresos_annul", function() {
        $.ajax({
            type: 'POST',
            url: 'editar_ingresos.php',
            dataType: 'json',
            data: { id: $('#id_ingreso').val(), oper: 'annul' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_ingresos').DataTable().page.info().page;
                reloadtable('tb_ingresos', pag);

                $('#txt_est_ing').val('ANULADO');

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
        let id_bodega = $('#id_txt_nom_bod').val();
        let data = $('#tb_lotes_articulos').DataTable().row(this).data();
        $.post("frm_reg_ingresos_detalles.php", { id_articulo: data.id_med, articulo: data.nom_medicamento, id_lote: data.id_lote, id_bodega: id_bodega }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });

        $('#divModalReg').on('shown.bs.modal', function() {
            $('#sl_lote_art').trigger('change');
        });
    });

    $('#divModalBus').on('dblclick', '#tb_articulos_pedido tr', function() {
        let id_bodega = $('#id_txt_nom_bod').val();
        let data = $('#tb_articulos_pedido').DataTable().row(this).data();
        $.post("frm_reg_ingresos_detalles.php", { id_articulo: data.id_med, articulo: data.nom_medicamento, cantidad: data.cantidad_pen, id_bodega: id_bodega }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });

        $('#divModalReg').on('shown.bs.modal', function() {
            if ($('#sl_lote_art option').length == 2 && $('#id_detalle').val() == -1) {
                $('#sl_lote_art').prop('selectedIndex', $('#sl_lote_art option').length - 1);
                $('#sl_lote_art').trigger('change');
            }
        });
    });

    $('#divModalReg').on("change", "#sl_lote_art", function() {
        let lote = $(this).find('option:selected');
        $('#txt_nom_art').val(lote.attr('data-nom_articulo'));
        if ($('#id_detalle').val() == -1) {
            $('#txt_pre_lot').val(lote.attr('data-nom_presentacion'));
            $('#id_txt_pre_lot').val(lote.attr('data-id_presentacion'));
            $('#txt_can_lot').val(lote.attr('data-cantidad_umpl'));
            if ($('#sl_tip_ing').find('option:selected').attr('data-fianza') == 1) {
                $('#txt_val_uni').val(lote.attr('data-val_promedio'));
            }
        }
    });

    $('#divForms').on('click', '#tb_ingresos_detalles .btn_editar', function() {
        let id_bodega = $('#id_txt_nom_bod').val();
        let id = $(this).attr('value');
        $.post("frm_reg_ingresos_detalles.php", { id: id, id_bodega: id_bodega }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    // Autocompletar Presentacion de Lote
    $('#divFormsReg').on("input", "#txt_pre_lot", function() {
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
                $('#id_txt_pre_lot').val(ui.item.id);
                $('#txt_can_lot').val(ui.item.cantidad);
            }
        });
    });

    $('#divModalReg').on('input', '#txt_can_ing, #txt_val_uni, #sl_por_iva', function() {
        var valor = $('#txt_val_uni').val() ? $('#txt_val_uni').val() : 0,
            iva = $('#sl_por_iva').val() ? $('#sl_por_iva').val() : 0;
        $('#txt_val_cos').val(parseFloat(valor) + parseFloat(valor) * parseFloat(iva) / 100);
    });

    //Guardar registro Detalle
    $('#divFormsReg').on("click", "#btn_guardar_detalle", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#sl_lote_art'));
        error -= verifica_vacio($('#txt_can_ing'));
        error += verifica_vacio($('#txt_val_uni'));
        error += verifica_vacio($('#txt_val_cos'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else if (!verifica_valmin($('#txt_can_ing'), 1, "La cantidad debe ser mayor igual a 1")) {
            var data = $('#frm_reg_ingresos_detalles').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_ingresos_detalles.php',
                dataType: 'json',
                data: data + "&id_ingreso=" + $('#id_ingreso').val() +
                    "&id_tipo_ing=" + $('#id_tip_ing').val() +
                    "&id_pedido=" + $('#txt_id_pedido').val() + '&oper=add'
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_detalle').val() == -1) ? 0 : $('#tb_ingresos_detalles').DataTable().page.info().page;
                    reloadtable('tb_ingresos_detalles', pag);
                    pag = $('#tb_ingresos').DataTable().page.info().page;
                    reloadtable('tb_ingresos', pag);

                    $('#id_detalle').val(r.id);
                    $('#txt_val_tot').val(r.val_total);
                    valor_aproximado();

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
    $('#divForms').on('click', '#tb_ingresos_detalles .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('detalle', id);
    });
    $('#divModalConfDel').on("click", "#detalle", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_ingresos_detalles.php',
            dataType: 'json',
            data: { id: id, id_ingreso: $('#id_ingreso').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_ingresos_detalles').DataTable().page.info().page;
                reloadtable('tb_ingresos_detalles', pag);
                pag = $('#tb_ingresos').DataTable().page.info().page;
                reloadtable('tb_ingresos', pag);

                $('#txt_val_tot').val(r.val_total);
                valor_aproximado();

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
    CREAR UN NUEVO LOTE
    -----------------------------------------------------*/
    $('#divFormsReg').on("click", "#btn_nuevo_lote", function() {
        $.post("../articulos/frm_reg_articulos_lotes.php", { id_articulo: $('#id_txt_nom_art').val() }, function(he) {
            $('#divTamModalAux').removeClass('modal-xl');
            $('#divTamModalAux').removeClass('modal-sm');
            $('#divTamModalAux').addClass('modal-lg');
            $('#divModalAux').modal('show');
            $("#divFormsAux").html(he);
        });
    })

    // Autocompletar Presentacion de Lote
    $('#divFormsAux').on("input", "#txt_pre_lote", function() {
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
                $('#id_txt_pre_lote').val(ui.item.id);
                $('#txt_can_lote').val(ui.item.cantidad);
            }
        });
    });

    //Guardar registro LOTE
    $('#divFormsAux').on("click", "#btn_guardar_lote", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio_2($('#id_txt_nom_bod'), $('#txt_nom_bod'));
        error += verifica_vacio($('#txt_num_lot'));
        error += verifica_vacio($('#txt_fec_ven'));
        error += verifica_vacio($('#sl_estado_lot'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else if (!verifica_valmin($('#txt_can_lote'), 1, "El valor de la Cantidad en la Unidad debe ser mayor a 1")) {
            var data = $('#frm_reg_articulos_lotes').serialize();
            $.ajax({
                url: '../articulos/editar_articulos_lotes.php',
                type: 'POST',
                dataType: 'json',
                data: data + "&id_articulo=" + $('#id_txt_nom_art').val() + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    $('#sl_lote_art').load('../common/cargar_lotes_articulo.php', {
                        id_articulo: $('#id_txt_nom_art').val(),
                        id_bodega: $('#id_txt_nom_bod').val()
                    }, function() {});

                    $('#divModalAux').modal('hide');
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

    /* ---------------------------------------------------
    IMPRIMIR REGISTROS
    -----------------------------------------------------*/
    //Imprimir los registros filtrados
    $('#btn_imprime_filtro').on('click', function() {
        reloadtable('tb_ingresos');
        $('.is-invalid').removeClass('is-invalid');
        var verifica = verifica_vacio($('#txt_fecini_filtro'));
        verifica += verifica_vacio($('#txt_fecfin_filtro'));
        if (verifica >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe especificar un rango de fechas');
        } else {
            let id_reporte = $('#sl_tipo_reporte').val();
            let reporte = "imp_ingresos.php";

            switch (id_reporte) {
                case '1':
                    reporte = "imp_ingresos_attsg.php";
                    break;
            }
            $.post(reporte, {
                id_ing: $('#txt_iding_filtro').val(),
                num_ing: $('#txt_numing_filtro').val(),
                num_fac: $('#txt_numfac_filtro').val(),
                fec_ini: $('#txt_fecini_filtro').val(),
                fec_fin: $('#txt_fecfin_filtro').val(),
                id_tercero: $('#sl_tercero_filtro').val(),
                id_tiping: $('#sl_tiping_filtro').val(),
                estado: $('#sl_estado_filtro').val(),
                modulo: $('#sl_modulo_origen').val(),
                id_reporte: id_reporte
            }, function(he) {
                $('#divTamModalImp').removeClass('modal-sm');
                $('#divTamModalImp').removeClass('modal-lg');
                $('#divTamModalImp').addClass('modal-xl');
                $('#divModalImp').modal('show');
                $("#divImp").html(he);
            });
        }
    });

    //Imprimit un registro 
    $('#divForms').on("click", "#btn_imprimir", function() {
        $.post("imp_ingreso.php", {
            id: $('#id_ingreso').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);