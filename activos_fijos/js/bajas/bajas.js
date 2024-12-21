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
        $('#tb_bajas').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_baja.php", function(he) {
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
                url: 'listar_bajas.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_BAJA= $('#txt_idbaja_filtro').val();
                    data.fec_ini = $('#txt_fecini_filtro').val();
                    data.fec_fin = $('#txt_fecfin_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_baja' }, //Index=0
                { 'data': 'observaciones' },
                { 'data': 'fecha_baja' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2, 3] },
                { orderable: false, targets: 4 }
            ],
            rowCallback: function(row, data) {
                var estado = $($(row).find("td")[11]).text();
                if (estado == 'PENDIENTE') {
                    $($(row).find("td")[0]).css("background-color", "yellow");
                } else if (estado == 'CERRADO') {
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
        $('#tb_bajas').wrap('<div class="overflow"/>');
    });

    //Buascar registros de Ingresos
    $('#btn_buscar_filtro').on("click", function() {
        $('.is-invalid').removeClass('is-invalid');
        reloadtable('tb_bajas');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_bajas');
        }
    });

    //Editar un registro Orden Ingreso
    $('#tb_bajas').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_baja.php", { id_baja: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro Orden mantenimiento
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#observaciones'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_baja').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_baja.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_baja').val() == -1) ? 0 : $('#tb_bajas').DataTable().page.info().page;
                    reloadtable('tb_bajas', pag);
                    $('#id_baja').val(r.id);

                    $('#btn_cerrar').prop('disabled', false);
                    $('#btn_imprimir').prop('disabled', false);

                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Proceso realizado con éxito");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r.mensaje);
                }
            }).always(
                function() {}
            ).fail(function(xhr, textStatus, errorThrown) {
                console.error(xhr.responseText)
                alert('Ocurrió un error');
            });
        }
    });

    //Borrar un registro Orden de mantenimiento
    $('#tb_bajas').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('bajas_del', id);
    });
    $('#divModalConfDel').on("click", "#bajas_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_baja.php',
            dataType: 'json',
            data: { id_baja: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_bajas').DataTable().page.info().page;
                reloadtable('tb_bajas', pag);
                $('#divModalDone').modal('show');
                $('#divMsgDone').html("Proceso realizado con éxito");
            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html(r.mensaje);
            }
        }).always(function() {

        }).fail(function(xhr, textStatus, errorThrown) {
            console.error(xhr.responseText)
            alert('Ocurrió un error');
        });
    });

    //Cerrar orden de baja
    $('#divForms').on("click", "#btn_cerrar", function() {
        confirmar_proceso('baja_cerrar');
    });
    $('#divModalConfDel').on("click", "#baja_cerrar", function() {
        $.ajax({
            type: 'POST',
            url: 'editar_baja.php',
            dataType: 'json',
            data: { id_baja: $('#id_baja').val(), oper: 'cerrar' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_bajas').DataTable().page.info().page;
                reloadtable('tb_bajas', pag);

                $('#estado').val('CERRADO');

                $('#btn_guardar').prop('disabled', true);
                $('#btn_cerrado').prop('disabled', true);

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

    //Guardar registro Detalle
    $('#divModalBus').on("click", "#btn_guardar_detalle", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_activo_fijo'));
        error += verifica_vacio($('#observacion_baja'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_baja_detalle').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_baja_detalle.php',
                dataType: 'json',
                data: data + "&id_baja_detalle=" + $('#id_baja_detalle').val() + "&id_baja=" + $('#id_baja').val() + '&oper=add'
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_baja_detalle').val() == -1) ? 0 : $('#tb_baja_detalles').DataTable().page.info().page;
                    reloadtable('tb_baja_detalles', pag);

                    $('#id_baja_detalle').val(r.id);
                    $('#divModalReg').modal('hide');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Proceso realizado con éxito");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r.mensaje);
                }
            }).always(function() {}).fail(function(xhr, textStatus, errorThrown) {
                console.error(xhr.responseText)
                alert('Error al guardar detalle');
            });
        }
    });

    // Autocompletar Activo fijo
    $('#divTamModalBus').on("input", "#txt_activo_fijo", function() {
        $(this).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "../common/cargar_activos_fijos.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term }
                }).done(function(data) {
                    response(data);
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#id_txt_activo_fijo').val(ui.item.id);
            }
        });
    });


    

})(jQuery);