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
        $('#tb_hojavida').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_hojavida.php", function(he) {
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
                url: 'listar_hojasvida.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.placa = $('#txt_placa_filtro').val();
                    data.nombre = $('#txt_nombre_filtro').val();
                    data.num_serial = $('#txt_serial_filtro').val();
                    data.marca = $('#sl_marcas_filtro').val();
                    data.tipoactivo = $('#sl_tipoactivo_filtro').val();
                    data.estado = $('#sl_estado_filtro').val();
                }
            },
            columns: [
                { 'data': 'id' }, //Index=0
                { 'data': 'placa' },
                { 'data': 'cod_articulo' },
                { 'data': 'nom_articulo' },
                { 'data': 'num_serial' },
                { 'data': 'marca' },
                { 'data': 'valor' },
                { 'data': 'tipo_activo' },
                { 'data': 'nom_sede' },
                { 'data': 'nom_area' },
                { 'data': 'nom_responsable' },
                { 'data': 'estado' },
                { 'data': 'nom_estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [3, 5] },
                { type: "numeric-comma", targets: 6 },
                { visible: false, targets: 11 },
                { orderable: false, targets: 13 }
            ],
            rowCallback: function(row, data) {
                if (data.estado == 2) {
                    $($(row).find("td")[0]).css("background-color", "yellow");
                } else if (data.estado == 3) {
                    $($(row).find("td")[0]).css("background-color", "DodgerBlue");
                } else if (data.estado == 4) {
                    $($(row).find("td")[0]).css("background-color", "green");
                } else if (data.estado == 5) {
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
        $('#tb_hojavida').wrap('<div class="overflow"/>');
    });

    //Buascar registros activos fijos
    $('#btn_buscar_filtro').on("click", function() {
        $('.is-invalid').removeClass('is-invalid');
        reloadtable('tb_hojavida');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_hojavida');
        }
    });

    //Editar un registro hoja de vida
    $('#tb_hojavida').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_hojavida.php", { id_hv: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    $('#divForms').on("dblclick", "#nom_articulo", function() {
        $.post("../common/buscar_articulos_act_frm.php", function(he) {
            $('#divTamModalBus').removeClass('modal-sm');
            $('#divTamModalBus').removeClass('modal-xl');
            $('#divTamModalBus').addClass('modal-lg');
            $('#divModalBus').modal('show');
            $("#divFormsBus").html(he);
        });
    });

    //Guardar hoja de vida
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio_2($('#id_sede'), $('#nom_sede'));
        error += verifica_vacio_2($('#id_area'), $('#nom_area'));
        error += verifica_vacio_2($('#id_articulo'), $('#nom_articulo'));
        error += verifica_vacio($('#placa'));
        error += verifica_vacio($('#num_serial'));
        error += verifica_vacio($('#id_marca'));
        error += verifica_vacio($('#id_proveedor'));
        error += verifica_vacio($('#valor'));
        error += verifica_vacio($('#estado_general'));
        error += verifica_vacio($('#estado'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#acf_reg_hojavida').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_hojavida.php',
                dataType: 'json',
                data: data + "&id_hv=" + $('#id_hv').val() + '&oper=add'
            }).done(function(res) {
                if (res.mensaje == 'ok') {
                    let pag = ($('#tb_hojavida').val() == -1) ? 0 : $('#tb_hojavida').DataTable().page.info().page;
                    reloadtable('tb_hojavida', pag);
                    $('#id_hv').val(res.id_hv);

                    $('#btn_imprimir').prop('disabled', false);

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
        }
    });

    //Borrar un registro Hoja de Vida
    $('#tb_hojavida').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('hojavida_del', id);
    });
    $('#divModalConfDel').on("click", "#hojavida_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_hojavida.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_hojavida').DataTable().page.info().page;
                reloadtable('tb_hojavida', pag);
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

    /* -----------------------------------------------------
    REGISTRAR IMAGEN
    -------------------------------------------------------- */

    $('#tb_hojavida').on('click', '.btn_imagen', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_imagen.php", { id_hv: id }, function(he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar imagen de activo fijo
    $('#divForms').on("click", "#btn_guardar_imagen", function() {
        var file = $('#uploadImageAcf')[0].files[0];
        if (file) {
            var validImageTypes = ["image/jpeg", "image/png", "image/gif"];
            if (!validImageTypes.includes(file.type)) {
                showError('Por favor, selecciona un archivo de imagen válido')
                return;
            }
        }
        var del_imagen = $('#imagen').val().trim() == '' ? 1 : 0;
        var act_imagen = file ? 1 : 0;
        let datos = new FormData();
        datos.append('id_hv', $('#id_hv').val());
        datos.append('imagen', $('#imagen').val());
        datos.append('del_imagen', del_imagen);
        datos.append('act_imagen', act_imagen);
        datos.append('oper', 'add');
        datos.append('uploadImageAcf', file);

        $.ajax({
            type: 'POST',
            url: 'editar_imagen.php',
            contentType: false,
            data: datos,
            processData: false,
            cache: false,
        }).done(function(res) {
            var res = JSON.parse(res);
            if (res.mensaje == 'ok') {
                $('#imagen').val(res.nombre_imagen);
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

    //Visualizar la imagen  hoja de vida
    $('#divForms').on("click", "#btn_ver_imagen", function() {
        if ($('#imagen').val()) {
            let nombreImagen = $('#imagen').val()
                //Construir la URL relativa al archivo
            var urlDescarga = '../../imagenes/' + nombreImagen
                // Redirigir al usuario a la URL para iniciar la descarga
            window.open(urlDescarga, '_blank');
        }
    });

    // Borrar imagen
    $('#divForms').on("click", "#btn_borrar_imagen", function() {
        $('#imagen').val('');
        $('#uploadDocAcf').val('');
        $('#imagen_sel').text('Seleccionar archivo');
    });

    /* -----------------------------------------------------
    REGISTRAR COMPONENTES
    -------------------------------------------------------- */

    $('#divModalBus').on('dblclick', '#tb_articulos_activos tr', function() {
        let data = $('#tb_articulos_activos').DataTable().row(this).data();
        if ($("#acf_reg_hojavida").is(":visible")) {
            $('#id_articulo').val(data.id_med);
            $('#nom_articulo').val(data.nom_medicamento);
            $('#divModalBus').modal('hide');

        } else if ($("#frm_reg_componentes").is(":visible")) {
            $.post("frm_reg_componente.php", { id_med: data.id_med }, function(he) {
                $('#divTamModalReg').addClass('modal-lg');
                $('#divModalReg').modal('show');
                $("#divFormsReg").html(he);
            });
        }
    });

    $('#tb_hojavida').on('click', '.btn_componente', function() {
        let id = $(this).attr('value');
        $.post("frm_componentes_hojavida.php", { id_hv: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Editar un componente
    $('#divForms').on('click', '#tb_componentes_hojavida .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_componente.php", { id: id }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //Guardar componente
    $('#divModalReg').on("click", "#btn_guardar_componente", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_num_serial'));
        error += verifica_vacio($('#sl_marca'));
        error += verifica_vacio($('#txt_modelo'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_componente').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_componente.php',
                dataType: 'json',
                data: data + '&id_hv=' + $('#id_hv').val() + '&oper=add'
            }).done(function(res) {
                if (res.mensaje == 'ok') {
                    let pag = ($('#tb_componentes_hojavida').val() == -1) ? 0 : $('#tb_componentes_hojavida').DataTable().page.info().page;
                    reloadtable('tb_componentes_hojavida', pag);
                    $('#id_componente').val(res.id_componente);
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
        }
    });

    //Borrar componente
    $('#divForms').on('click', '#tb_componentes_hojavida .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('componente_del', id);
    });
    $('#divModalConfDel').on("click", "#componente_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_componente.php',
            dataType: 'json',
            data: { id: id, id_hv: $('#id_hv').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_componentes_hojavida').DataTable().page.info().page;
                reloadtable('tb_componentes_hojavida', pag);
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

    /* -----------------------------------------------------
     REGISTRAR DOCUMENTOS
     -------------------------------------------------------- */

    $('#tb_hojavida').on('click', '.btn_archivos', function() {
        let id = $(this).attr('value');
        $.post("frm_documentos_hojavida.php", { id_hv: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Editar documento
    $('#divForms').on('click', '#tb_documentos_hojavida .btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_documento.php", { id: id }, function(he) {
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //Guardar documentos
    $('#divModalReg').on("click", "#btn_guardar_documento", function() {
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#tipo'));
        error += verifica_vacio($('#descripcion'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
            return;
        }
        var file = $('#uploadDocAcf')[0].files[0];
        var nuevo_file = file ? 1 : 0;
        if (!$('#archivo').val()) {
            if (!file) {
                showError('Por favor, selecciona un archivo')
                return;
            }
            var validImageTypes = ["application/pdf", "application/pdf"];
            if (!validImageTypes.includes(file.type)) {
                showError('Por favor, selecciona un documento válido')
                return;
            }
        }

        let datos = new FormData();
        datos.append('id_hv', $('#id_hv').val());
        datos.append('id_documento', $('#id_documento').val());
        datos.append('tipo', $('#tipo').val());
        datos.append('descripcion', $('#descripcion').val());
        datos.append('archivo', $('#archivo').val());
        datos.append('oper', 'add');
        datos.append('uploadDocAcf', file);
        datos.append('nuevo_file', nuevo_file);

        $.ajax({
            type: 'POST',
            url: 'editar_documento.php',
            contentType: false,
            data: datos,
            processData: false,
            cache: false,
        }).done(function(res) {
            var res = JSON.parse(res);
            if (res.mensaje == 'ok') {
                let pag = ($('#tb_documentos_hojavida').val() == -1) ? 0 : $('#tb_documentos_hojavida').DataTable().page.info().page;
                reloadtable('tb_documentos_hojavida', pag);
                $('#id_documento').val(res.id_documento);
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

    //Descarar documento
    $('#divModalReg').on("click", "#btn_ver_documento", function() {
        if ($('#archivo').val()) {
            let nombreDocumento = $('#archivo').val()
                // Construir la URL relativa al archivo
            var urlDescarga = '../../documentos/' + nombreDocumento
                // Redirigir al usuario a la URL para iniciar la descarga
            window.open(urlDescarga, '_blank');
        }
    });

    //Borrar documento
    $('#divForms').on('click', '#tb_documentos_hojavida .btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('documentos_del', id);
    });
    $('#divModalConfDel').on("click", "#documentos_del", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_documento.php',
            dataType: 'json',
            data: { id: id, id_hv: $('#id_hv').val(), oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_documentos_hojavida').DataTable().page.info().page;
                reloadtable('tb_documentos_hojavida', pag);
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

})(jQuery);