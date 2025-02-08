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
        $('#tb_traslados').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_traslados.php", function(he) {
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
                url: 'listar_traslados.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_sedori = $('#sl_sedori_filtro').val();
                    data.id_bodori = $('#sl_bodori_filtro').val();
                    data.id_tra = $('#txt_idtra_filtro').val();
                    data.num_tra = $('#txt_numtra_filtro').val();
                    data.fec_ini = $('#txt_fecini_filtro').val();
                    data.fec_fin = $('#txt_fecfin_filtro').val();
                    data.id_seddes = $('#sl_seddes_filtro').val();
                    data.id_boddes = $('#sl_boddes_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                    data.modulo = $('#sl_modulo_origen').val();
                }
            },
            columns: [
                { 'data': 'id_traslado' }, //Index=0
                { 'data': 'num_traslado' },
                { 'data': 'fec_traslado' },
                { 'data': 'hor_traslado' },
                { 'data': 'detalle' },
                { 'data': 'nom_sede_origen' },
                { 'data': 'nom_bodega_origen' },
                { 'data': 'nom_sede_destino' },
                { 'data': 'nom_bodega_destino' },
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
        $('#tb_traslados').wrap('<div class="overflow"/>');
    });

    //Filtrar las Bodegas acorde a la Sede y Usuario de sistema
    $('#sl_sedori_filtro').on("change", function() {
        $('#sl_bodori_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega Origen--' }, function() {});
    });
    $('#sl_sedori_filtro').trigger('change');

    $('#sl_seddes_filtro').on("change", function() {
        $('#sl_boddes_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega Destino--', todas: true }, function() {});
    });
    $('#sl_seddes_filtro').trigger('change');

    //Buascar registros de traslados
    $('#btn_buscar_filtro').on("click", function() {
        $('.is-invalid').removeClass('is-invalid');
        reloadtable('tb_traslados');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_traslados');
        }
    });

    /* ---------------------------------------------------
    TRASLADO EN BASE A UN PEDIDO
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

    $('#divModalBus').on('dblclick', '#tb_pedidos_tra tr', function() {
        let data = $('#tb_pedidos_tra').DataTable().row(this).data();
        $('#txt_id_pedido').val(data.id_pedido);
        $('#txt_des_pedido').val(data.detalle + '(' + data.fec_pedido + ')');

        if (data.id_pedido) {
            $('#sl_sede_origen').val(data.id_sede_origen).prop('disabled', true);
            $('#id_sede_origen').val(data.id_sede_origen);
            $('#sl_bodega_origen').load('../common/cargar_bodegas_usuario.php', { id_sede: data.id_sede_origen }, function() {
                $(this).val(data.id_bodega_origen).prop('disabled', true);
                $('#id_bodega_origen').val(data.id_bodega_origen);
            });

            $('#sl_sede_destino').val(data.id_sede_destino).prop('disabled', true);
            $('#id_sede_destino').val(data.id_sede_destino);
            $('#sl_bodega_destino').load('../common/cargar_bodegas_usuario.php', { id_sede: data.id_sede_destino, titulo: '', todas: true }, function() {
                $(this).val(data.id_bodega_destino).prop('disabled', true);
                $('#id_bodega_destino').val(data.id_bodega_destino);
            });
        }
        $('#divModalBus').modal('hide');
    });

    $('#divModalBus').on('click', '#tb_pedidos_tra .btn_imprimir', function() {
        let id = $(this).attr('value');
        $.post("../pedidos_bod/imp_pedido.php", { id: id }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    $('#divForms').on("click", "#btn_cancelar_pedido", function() {
        let table = $('#tb_traslados_detalles').DataTable();
        let filas = table.rows().count();
        if (filas == 0) {
            $('#txt_id_pedido').val('');
            $('#txt_des_pedido').val('');
            $('#sl_sede_origen').prop('disabled', false);
            $('#sl_bodega_origen').prop('disabled', false);
            $('#sl_sede_destino').prop('disabled', false);
            $('#sl_bodega_destino').prop('disabled', false);
        }
    });

    //Imprimit el Pedido
    $('#divForms').on("click", "#btn_imprime_pedido", function() {
        let id = $('#txt_id_pedido').val();
        if (id) {
            $.post("../pedidos_bod/imp_pedido.php", { id: id }, function(he) {
                $('#divTamModalImp').removeClass('modal-sm');
                $('#divTamModalImp').removeClass('modal-lg');
                $('#divTamModalImp').addClass('modal-xl');
                $('#divModalImp').modal('show');
                $("#divImp").html(he);
            });
        }
    });

    /* ---------------------------------------------------
    ENCABEZADO DE UN TRASLADO
    -----------------------------------------------------*/

    $('#divForms').on("change", "#sl_sede_origen", function() {
        $('#sl_bodega_origen').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val() }, function() {});
        $('#id_sede_origen').val($('#sl_sede_origen').val());
    });

    $('#divForms').on("change", "#sl_bodega_origen", function() {
        $('#id_bodega_origen').val($('#sl_bodega_origen').val());
    });

    $('#divForms').on("change", "#sl_sede_destino", function() {
        $('#sl_bodega_destino').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), todas: true }, function() {});
        $('#id_sede_destino').val($('#sl_sede_destino').val());
    });

    $('#divForms').on("change", "#sl_bodega_destino", function() {
        $('#id_bodega_destino').val($('#sl_bodega_destino').val());
    });

    //Editar un registro Traslado
    $('#tb_traslados').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_traslados.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro Traslado
    $('#divForms').on("click", "#btn_guardar", function() {
        let table = $('#tb_traslados_detalles').DataTable();
        let filas = table.rows().count();
        let id_pedido = $('#txt_id_pedido').val();

        if (id_pedido && filas == 0) {
            confirmar_proceso_msg('traslados_pedido', 'Desea Generar el Traslado en base al Pedido ' + id_pedido);
        } else {
            guardar_traslado(0);
        }
    });

    $('#divModalConfDel').on("click", "#traslados_pedido", function() {
        $('#divModalConfDel').modal('hide');
        guardar_traslado(1);
    });

    function guardar_traslado(generar_traslado) {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#sl_sede_origen'));
        error += verifica_vacio($('#sl_bodega_origen'));
        error += verifica_vacio($('#sl_sede_destino'));
        error += verifica_vacio($('#sl_bodega_destino'));
        error += verifica_vacio($('#txt_det_traslado'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            if ($('#sl_bodega_origen').val() == $('#sl_bodega_destino').val()) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('La Bodega Origen y la Bodega destino deben ser diferentes');
            } else {
                var data = $('#frm_reg_traslados').serialize();
                $.ajax({
                    type: 'POST',
                    url: 'editar_traslados.php',
                    dataType: 'json',
                    data: data + "&oper=add" + '&generar_traslado=' + generar_traslado
                }).done(function(r) {
                    if (r.mensaje == 'ok') {
                        let pag = ($('#id_traslado').val() == -1) ? 0 : $('#tb_traslados').DataTable().page.info().page;
                        reloadtable('tb_traslados', pag);
                        if (generar_traslado == 1) reloadtable('tb_traslados_detalles');

                        $('#id_traslado').val(r.id);
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
    };

    //Borrar un registro Traslado
    $('#tb_traslados').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('traslados_del', id);
    });
    $('#divModalConfDel').on("click", "#traslados_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_traslados.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_traslados').DataTable().page.info().page;
                reloadtable('tb_traslados', pag);
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

    //Cerrar un registro Traslado
    $('#divForms').on("click", "#btn_cerrar", function() {
        confirmar_proceso('traslados_close');
    });
    $('#divModalConfDel').on("click", "#traslados_close", function() {
        $.ajax({
            type: 'POST',
            url: 'editar_traslados.php',
            dataType: 'json',
            data: { id: $('#id_traslado').val(), oper: 'close' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_traslados').DataTable().page.info().page;
                reloadtable('tb_traslados', pag);

                $('#txt_num_traslado').val(r.num_traslado);
                $('#txt_est_traslado').val('CERRADO');

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

    //Anular un registro Orden traslado
    $('#divForms').on("click", "#btn_anular", function() {
        confirmar_proceso('traslados_annul');
    });
    $('#divModalConfDel').on("click", "#traslados_annul", function() {
        $.ajax({
            type: 'POST',
            url: 'editar_traslados.php',
            dataType: 'json',
            data: { id: $('#id_traslado').val(), oper: 'annul' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_traslados').DataTable().page.info().page;
                reloadtable('tb_traslados', pag);

                $('#txt_est_traslado').val('ANULADO');

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
        $.post("frm_reg_traslados_detalles.php", { id_lote: id_lote }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);

        });
    });

    $('#divForms').on('click', '#tb_traslados_detalles .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_traslados_detalles.php", { id: id }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //Guardar registro Detalle
    $('#divFormsReg').on("click", "#btn_guardar_detalle", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_can_tra'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else if (!verifica_valmin($('#txt_can_tra'), 1, "La cantidad debe ser mayor igual a 1")) {
            var data = $('#frm_reg_traslados_detalles').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_traslados_detalles.php',
                dataType: 'json',
                data: data + '&id_traslado=' + $('#id_traslado').val() + "&id_bodega=" + $('#sl_bodega_origen').val() + '&oper=add'
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_detalle').val() == -1) ? 0 : $('#tb_traslados_detalles').DataTable().page.info().page;
                    reloadtable('tb_traslados_detalles', pag);
                    pag = $('#tb_traslados').DataTable().page.info().page;
                    reloadtable('tb_traslados', pag);

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
    $('#divForms').on('click', '#tb_traslados_detalles .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('detalle', id);
    });
    $('#divModalConfDel').on("click", "#detalle", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_traslados_detalles.php',
            dataType: 'json',
            data: { id: id, id_traslado: $('#id_traslado').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_traslados_detalles').DataTable().page.info().page;
                reloadtable('tb_traslados_detalles', pag);
                pag = $('#tb_traslados').DataTable().page.info().page;
                reloadtable('tb_traslados', pag);

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
        reloadtable('tb_traslados');
        $('.is-invalid').removeClass('is-invalid');
        var verifica = verifica_vacio($('#txt_fecini_filtro'));
        verifica += verifica_vacio($('#txt_fecfin_filtro'));
        if (verifica >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe especificar un rango de fechas');
        } else {
            $.post("imp_traslados.php", {
                id_sedori: $('#sl_sedori_filtro').val(),
                id_bodori: $('#sl_bodori_filtro').val(),
                id_tra: $('#txt_idtra_filtro').val(),
                num_tra: $('#txt_numtra_filtro').val(),
                fec_ini: $('#txt_fecini_filtro').val(),
                fec_fin: $('#txt_fecfin_filtro').val(),
                id_tercero: $('#sl_tercero_filtro').val(),
                id_seddes: $('#sl_seddes_filtro').val(),
                id_boddes: $('#sl_boddes_filtro').val(),
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

    //Imprimit un Traslado
    $('#divForms').on("click", "#btn_imprimir", function() {
        $.post("imp_traslado.php", {
            id: $('#id_traslado').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);