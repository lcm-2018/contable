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
        $('#tb_progreso_mantenimientos').DataTable({
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: 'listar_mantenimientos_prog.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_mantenimiento = $('#txt_idmant_filtro').val();
                    data.placa = $('#txt_placa_filtro').val();
                    data.nombre = $('#txt_nombre_filtro').val();
                    data.fec_ini = $('#txt_fecini_filtro').val();
                    data.fec_fin = $('#txt_fecfin_filtro').val();
                    data.id_tip_man = $('#sl_tipomantenimiento_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_mant_detalle' }, //Index=0
                { 'data': 'id_mantenimiento' },
                { 'data': 'fec_mantenimiento' },
                { 'data': 'nom_estado_man' },
                { 'data': 'placa' },
                { 'data': 'nom_articulo' },
                { 'data': 'estado_general' },
                { 'data': 'tipo_mantenimiento' },
                { 'data': 'fec_ini_mantenimiento' },
                { 'data': 'fec_fin_mantenimiento' },
                { 'data': 'estado' },
                { 'data': 'nom_estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 5 },
                { visible: false, targets: 10 },
                { orderable: false, targets: 12 }
            ],
            rowCallback: function(row, data) {
                if (data.estado == 1) {
                    $($(row).find("td")[0]).css("background-color", "yellow");
                } else if (data.estado == 2) {
                    $($(row).find("td")[0]).css("background-color", "DodgerBlue");
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
        $('#tb_progreso_mantenimientos').wrap('<div class="overflow"/>');
    });

    //Buascar registros de mantenimientos
    $('#btn_buscar_filtro').on("click", function() {
        $('.is-invalid').removeClass('is-invalid');
        reloadtable('tb_progreso_mantenimientos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_progreso_mantenimientos');
        }
    });

    /* ---------------------------------------------------
    DETALLES DEL MANTENIMIENTO
    -----------------------------------------------------*/

    $('#tb_progreso_mantenimientos').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_mantenimiento_detalle.php", { id_md: id }, function(he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro de mantenimiento
    $('#divForms').on("click", "#btn_guardar_detalle", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_observacio_mant'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_mantenimiento_detalle').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_mantenimiento_detalle.php',
                dataType: 'json',
                data: data + '&oper=add'
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = $('#tb_progreso_mantenimientos').DataTable().page.info().page;
                    reloadtable('tb_progreso_mantenimientos', pag);
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

    //Finalizar registro de mantenimiento
    $('#divForms').on("click", "#btn_finalizar_detalle", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_observacio_mant'));
        error += verifica_vacio($('#sl_estado_general'));
        error += verifica_vacio($('#txt_observacio_fin_mant'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            confirmar_proceso('mantenimiento_final');
        }
    });
    $('#divModalConfDel').on("click", "#mantenimiento_final", function() {
        var data = $('#frm_reg_mantenimiento_detalle').serialize();
        $.ajax({
            type: 'POST',
            url: 'editar_mantenimiento_detalle.php',
            dataType: 'json',
            data: data + '&oper=close'
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_progreso_mantenimientos').DataTable().page.info().page;
                reloadtable('tb_progreso_mantenimientos', pag);

                $('#btn_guardar_detalle').prop('disabled', true);
                $('#btn_finalizar_detalle').prop('disabled', true);

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
    });

    /* ---------------------------------------------------
    NOTAS DE MANTENIMIENTO
    -----------------------------------------------------*/

    // visualizar la cuadricula de notas de mantenimiento
    $('#tb_progreso_mantenimientos').on('click', '.btn_notas', function() {
        let id = $(this).attr('value');
        $.post("frm_notas_mantenimiento.php", { id_md: id }, function(he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Editar nota de mantenimiento
    $('#divForms').on('click', '#tb_notas_mantenimiento .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_nota_detalle.php", { id: id }, function(he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //Guardar nota de mantenimiento
    $('#divModalReg').on("click", "#btn_guardar_nota", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_observacio_not'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
            return;
        }
        var file = $('#uploadDocAcf')[0].files[0];
        if (file) {
            var validImageTypes = ["application/pdf", "application/pdf"];
            if (!validImageTypes.includes(file.type)) {
                showError('Por favor, selecciona un documento válido')
                return;
            }
        }

        var del_doc = $('#archivo').val().trim() == '' ? 1 : 0;
        var act_doc = file ? 1 : 0;

        let datos = new FormData();
        datos.append('id_md', $('#id_mant_detalle').val());
        datos.append('id_nota', $('#id_nota').val());
        datos.append('txt_fec_not', $('#txt_fec_not').val());
        datos.append('txt_hor_not', $('#txt_hor_not').val());
        datos.append('txt_observacio_not', $('#txt_observacio_not').val());
        datos.append('archivo', $('#archivo').val());
        datos.append('del_doc', del_doc);
        datos.append('act_doc', act_doc);
        datos.append('oper', 'add');
        datos.append('uploadDocAcf', file);

        $.ajax({
            type: 'POST',
            url: 'editar_nota_detalle.php',
            contentType: false,
            data: datos,
            processData: false,
            cache: false,
        }).done(function(res) {
            var res = JSON.parse(res);
            if (res.mensaje == 'ok') {
                let pag = ($('#tb_notas_mantenimiento').val() == -1) ? 0 : $('#tb_notas_mantenimiento').DataTable().page.info().page;
                reloadtable('tb_notas_mantenimiento', pag);
                $('#id_nota').val(res.id_nota);
                $('#archivo').val(res.nombre_archivo);
                $('#divModalDone').modal('show');
                $('#divMsgDone').html("Proceso realizado con éxito");
            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html(res.mensaje);
            }
        }).always(
            function() {}
        ).fail(function(xhr, textStatus, errorThrown) {
            console.error(xhr.responseText)
            alert('Ocurrió un error');
        });
    });

    //Descarar documento de nota de mantenimiento
    $('#divModalReg').on("click", "#btn_ver_documento", function() {
        if ($('#archivo').val()) {
            let nombreDocumento = $('#archivo').val()
                // Construir la URL relativa al archivo
            var urlDescarga = '../../documentos/' + nombreDocumento
                // Redirigir al usuario a la URL para iniciar la descarga
            window.open(urlDescarga, '_blank');
        }
    });

    // Borrar documento de nota de mantenimiento
    $('#divModalReg').on("click", "#btn_borrar_documento", function() {
        $('#archivo').val('');
        $('#uploadDocAcf').val('');
        $('#archivo_sel').text('Seleccionar archivo');
    });

    //Borrar nota de mantenimiento
    $('#divForms').on('click', '#tb_notas_mantenimiento .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('notas_del', id);
    });
    $('#divModalConfDel').on("click", "#notas_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_nota_detalle.php',
            dataType: 'json',
            data: { id: id, id_md: $('#id_mant_detalle').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_notas_mantenimiento').DataTable().page.info().page;
                reloadtable('tb_notas_mantenimiento', pag);
                $('#divModalDone').modal('show');
                $('#divMsgDone').html("Proceso realizado con éxito");
            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html(r.mensaje);
            }
        }).always(function() {}).fail(function(xhr, textStatus, errorThrown) {
            console.error(xhr.responseText)
            alert('Ocurrió un error');
        });
    });

    //Imprimir listado de registros
    $('#btn_imprime_filtro').on('click', function() {
        reloadtable('tb_progreso_mantenimientos');
        $('.is-invalid').removeClass('is-invalid');
        var verifica = verifica_vacio($('#txt_fecini_filtro'));
        verifica += verifica_vacio($('#txt_fecfin_filtro'));
        if (verifica >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe especificar un rango de fechas');
        } else {
            $.post("imp_mantenimientos_prog.php", {
                id_mantenimiento: $('#txt_idmant_filtro').val(),
                placa: $('#txt_placa_filtro').val(),
                nombre: $('#txt_nombre_filtro').val(),
                fec_ini: $('#txt_fecini_filtro').val(),
                fec_fin: $('#txt_fecfin_filtro').val(),
                id_tip_man: $('#sl_tipomantenimiento_filtro').val(),
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

    //Imprimit un registro de Mantenimientos
    $('#divForms').on("click", "#btn_imprimir", function() {
        $.post("imp_mantenimiento_prog.php", {
            id: $('#id_mant_detalle').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);