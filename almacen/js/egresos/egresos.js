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
                    data.id_area = $('#sl_area_filtro').val();
                    data.id_tipegr = $('#sl_tipegr_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                    data.modulo = $('#sl_modulo_origen').val();
                }
            },
            columns: [
                { 'data': 'id_egreso' }, //Index=0
                { 'data': 'num_egreso' },
                { 'data': 'fec_egreso' },
                { 'data': 'hor_egreso' },
                { 'data': 'detalle' },
                { 'data': 'nom_tipo_egreso' },
                { 'data': 'nom_sede' },
                { 'data': 'nom_bodega' },
                { 'data': 'nom_tercero' },
                { 'data': 'nom_centro' },
                { 'data': 'nom_area' },
                { 'data': 'val_total' },
                { 'data': 'estado' },
                { 'data': 'nom_estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [4, 6, 7, 8, 9, 10] },
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
        $('#tb_egresos').wrap('<div class="overflow"/>');
    });

    //Filtrar las Bodegas acorde a la Sede y Usuario de sistema
    $('#sl_sede_filtro').on("change", function() {
        $('#sl_bodega_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega--' }, function() {});
    });
    $('#sl_sede_filtro').trigger('change');

    $('#sl_centrocosto_filtro').on("change", function() {
        $('#sl_area_filtro').load('../common/cargar_areas_centrocosto.php', { id_centrocosto: $(this).val(), titulo: '--Areas--' }, function() {});
    });
    $('#sl_centrocosto_filtro').trigger('change');

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
    EGRESO EN BASE A UN PEDIDO O EN BASE A UN INGRESO
    -----------------------------------------------------*/
    // Selecciono el tipo de Egreso
    $('#divForms').on("change", "#sl_tip_egr", function() {
        let es_conpedido = $('#sl_tip_egr').find('option:selected').attr('data-conpedido');
        let es_devfianza = $('#sl_tip_egr').find('option:selected').attr('data-devfianza');
        $('#divConPedido').hide();
        $('#divDevFianza').hide();
        if (es_conpedido == 1) {
            $('#divConPedido').show();
        } else if (es_devfianza == 1) {
            $('#divDevFianza').show();
        }
    });

    //--------------------------------------------------------
    //Seleccionar un Pedido para hacer el egreso
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
            $('#sl_tip_egr').prop('disabled', true);
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

    //Imprimit el Pedido desde la datatable
    $('#divModalBus').on('click', '#tb_pedidos_egr .btn_imprimir', function() {
        let id = $(this).attr('value');
        $.post("imp_pedido.php", { id: id }, function(he) {
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
            $('#sl_tip_egr').prop('disabled', false);
            $('#txt_id_pedido').val('');
            $('#txt_des_pedido').val('');
            $('#sl_sede_egr').prop('disabled', false);
            $('#sl_bodega_egr').prop('disabled', false);
        }
    });

    //Imprimit el Pedido desde el formulario 
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

    //--------------------------------------------------------
    //Seleccionar un Ingreso Fianza para hacer el egreso
    $('#divForms').on("dblclick", "#txt_des_ingreso", function() {
        $.post("buscar_ingresos_frm.php", function(he) {
            $('#divTamModalBus').removeClass('modal-sm');
            $('#divTamModalBus').removeClass('modal-lg');
            $('#divTamModalBus').addClass('modal-xl');
            $('#divModalBus').modal('show');
            $("#divFormsBus").html(he);
        });
    });

    $('#divModalBus').on('dblclick', '#tb_ingresos_fz tr', function() {
        let data = $('#tb_ingresos_fz').DataTable().row(this).data();
        $('#txt_id_ingreso').val(data.id_ingreso);
        $('#txt_des_ingreso').val(data.detalle + '(' + data.fec_ingreso + ')');

        if (data.id_ingreso) {
            $('#sl_tip_egr').prop('disabled', true);
            $('#sl_sede_egr').val(data.id_sede).prop('disabled', true);
            $('#id_sede_egr').val(data.id_sede);
            $('#sl_bodega_egr').load('../common/cargar_bodegas_usuario.php', { id_sede: data.id_sede }, function() {
                $(this).val(data.id_bodega).prop('disabled', true);
                $('#id_bodega_egr').val(data.id_bodega);
            });
            $('#sl_tercero').val(data.id_provedor);
        }
        $('#divModalBus').modal('hide');
    });

    //Imprimit el Ingreso desde la datatable
    $('#divModalBus').on('click', '#tb_ingresos_fz .btn_imprimir', function() {
        let id = $(this).attr('value');
        $.post("imp_ingreso.php", { id: id }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    $('#divForms').on("click", "#btn_cancelar_ingreso", function() {
        let table = $('#tb_egresos_detalles').DataTable();
        let filas = table.rows().count();
        if (filas == 0) {
            $('#sl_tip_egr').prop('disabled', false);
            $('#txt_id_ingreso').val('');
            $('#txt_des_ingreso').val('');
            $('#sl_sede_egr').prop('disabled', false);
            $('#sl_bodega_egr').prop('disabled', false);
        }
    });

    //Imprimit el Ingreso desde el formulario 
    $('#divForms').on("click", "#btn_imprime_ingreso", function() {
        let id = $('#txt_id_ingreso').val();
        if (id) {
            $.post("imp_ingreso.php", { id: id }, function(he) {
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

    $('#divForms').on("change", "#sl_tip_egr", function() {
        $('#id_tip_egr').val($('#sl_tip_egr').val());
    });

    $('#divForms').on("change", "#sl_centrocosto", function() {
        $('#sl_area').load('../common/cargar_areas_centrocosto.php', { id_centrocosto: $(this).val() }, function() {});
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
        $('.is-invalid').removeClass('is-invalid');
        let es_conpedido = $('#sl_tip_egr').find('option:selected').attr('data-conpedido');
        let es_devfianza = $('#sl_tip_egr').find('option:selected').attr('data-devfianza');
        let es_intext = $('#sl_tip_egr').find('option:selected').attr('data-intext');

        let error = verifica_vacio($('#sl_sede_egr'));

        error += verifica_vacio($('#sl_bodega_egr'));
        error += verifica_vacio($('#sl_tip_egr'));
        if (es_conpedido == 1) {
            error += verifica_vacio($('#txt_des_pedido'));
        }
        if (es_devfianza == 1) {
            error += verifica_vacio($('#txt_des_ingreso'));
        }
        if (es_intext == 2) {
            error += verifica_vacio($('#sl_tercero'));
        } else {
            error += verifica_vacio($('#sl_centrocosto'));
        }
        error += verifica_vacio($('#txt_det_egr'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            let table = $('#tb_egresos_detalles').DataTable();
            let filas = table.rows().count();
            let id_pedido = $('#txt_id_pedido').val();
            let id_ingreso = $('#txt_id_ingreso').val();

            if (es_conpedido == 1 && filas == 0 && id_pedido) {
                confirmar_proceso_msg('egreso_gen', 'Desea Generar el Egreso en base al Pedido ' + id_pedido);
            } else if (es_devfianza == 1 && filas == 0 && id_ingreso) {
                confirmar_proceso_msg('egreso_gen', 'Desea Generar el Egreso en base a la Orden de Ingreso Fianza ' + id_ingreso);
            } else {
                guardar_egreso(0);
            }
        }
    });

    $('#divModalConfDel').on("click", "#egreso_gen", function() {
        $('#divModalConfDel').modal('hide');
        let es_conpedido = $('#sl_tip_egr').find('option:selected').attr('data-conpedido');
        let es_devfianza = $('#sl_tip_egr').find('option:selected').attr('data-devfianza');
        let generar = 0;
        if (es_conpedido == 1) {
            generar = 1;
        } else if (es_devfianza == 1) {
            generar = 2;
        }
        guardar_egreso(generar);
    });

    function guardar_egreso(generar_egreso) {
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

                if (generar_egreso == 1 || generar_egreso == 2) {
                    reloadtable('tb_egresos_detalles');
                    $('#txt_val_tot').val(r.val_total);
                }
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
                id_cencost: $('#sl_centrocosto_filtro').val(),
                id_area: $('#sl_area_filtro').val(),
                id_tipegr: $('#sl_tipegr_filtro').val(),
                estado: $('#sl_estado_filtro').val(),
                modulo: $('#sl_modulo_origen').val()
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