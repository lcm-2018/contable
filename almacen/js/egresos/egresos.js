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
                    data.id_depende = $('#sl_dependencia_filtro').val();
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
                { 'data': 'nom_tercero' },
                { 'data': 'nom_dependencia' },
                { 'data': 'nom_tipo_egreso' },
                { 'data': 'val_total' },
                { 'data': 'nom_sede' },
                { 'data': 'nom_bodega' },
                { 'data': 'nom_estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [4, 5, 6] },
                { type: "numeric-comma", targets: 8 },
                { orderable: false, targets: 12 }
            ],
            rowCallback: function(row, data) {
                var estado = $($(row).find("td")[11]).text();
                if (estado == 'PENDIENTE') {
                    $($(row).find("td")[0]).css("background-color", "yellow");
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
        $('#tb_egresos').wrap('<div class="overflow"/>');
    });

    //Buascar registros de Egresos
    $('#sl_sede_filtro').on("change", function() {
        $('#sl_bodega_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega--' }, function() {});
    });
    $('#sl_sede_filtro').trigger('change');

    $('#divForms').on("change", "#sl_sede_egr", function() {
        $('#sl_bodega_egr').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val() }, function() {});
    });

    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_egresos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_egresos');
        }
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

    //Guardar registro Orden Egreso
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#sl_sede_egr'));
        error += verifica_vacio($('#sl_bodega_egr'));
        error += verifica_vacio($('#sl_tip_egr'));
        if ($('#sl_tip_egr').find('option:selected').attr('data-intext') == 2) {
            error += verifica_vacio($('#sl_tercero'));
        } else {
            error += verifica_vacio($('#sl_dependencia'));
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
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_egreso').val() == -1) ? 0 : $('#tb_egresos').DataTable().page.info().page;
                    reloadtable('tb_egresos', pag);
                    $('#id_egreso').val(r.id);

                    $('#sl_sede_egr').prop('disabled', true);
                    $('#sl_bodega_egr').prop('disabled', true);
                    $('#btn_cerrar').prop('disabled', false);

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
                data: data + "&id_egreso=" + $('#id_egreso').val() + '&oper=add'
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

})(jQuery);