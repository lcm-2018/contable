(function ($) {
    //Función para mostrar formulario de gestión de documentos
    FormGestionTpRte = function (id_tipo) {
        $.post("datos/registrar/form_tipo_retencion.php", { id_tipo: id_tipo }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    }
    FormGestionRetencion = function (id) {
        $.post("datos/registrar/form_retencion.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    }
    FormGestionRetRango = function (id) {
        $.post("datos/registrar/form_retencion_rango.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    }
    $(document).ready(function () {
        $('#tableTipoRetencion').DataTable({
            dom: setdom,
            language: setIdioma,
            buttons: [{
                //Registar modalidad de contratación
                action: function (e, dt, node, config) {
                    FormGestionTpRte(0);
                }
            }],
            ajax: {
                url: 'datos/listar/datos_tipo_impuesto.php',
                type: 'POST',
                dataType: 'json',
            },
            columns: [
                { 'data': 'id' },
                { 'data': 'tipo' },
                { 'data': 'tercero' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            order: [
                [0, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('#tableTipoRetencion').wrap('<div class="overflow" />');
        $('#tableRetenciones').DataTable({
            dom: setdom,
            language: setIdioma,
            buttons: [{
                //Registar modalidad de contratación
                action: function (e, dt, node, config) {
                    FormGestionRetencion(0);
                }
            }],
            ajax: {
                url: 'datos/listar/datos_retenciones.php',
                type: 'POST',
                dataType: 'json',
            },
            columns: [
                { 'data': 'id' },
                { 'data': 'tipo' },
                { 'data': 'retencion' },
                { 'data': 'cuenta' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            order: [
                [0, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('#tableRetenciones').wrap('<div class="overflow" />');
        $('#tableRangoRet').DataTable({
            dom: setdom,
            language: setIdioma,
            buttons: [{
                //Registar modalidad de contratación
                action: function (e, dt, node, config) {
                    FormGestionRetRango(0);
                }
            }],
            ajax: {
                url: 'datos/listar/datos_retenciones_rango.php',
                type: 'POST',
                dataType: 'json',
            },
            columns: [
                { 'data': 'id' },
                { 'data': 'tipo' },
                { 'data': 'retencion' },
                { 'data': 'base' },
                { 'data': 'tope' },
                { 'data': 'tarifa' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            order: [
                [0, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('#tableRangoRet').wrap('<div class="overflow" />');
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
    });
    $('#divModalForms').on('click', '#btnGuardaTpRte', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#txtTipoRte').val() == '') {
            $('#txtTipoRte').addClass('is-invalid');
            $('#txtTipoRte').focus();
            mjeError('Ingrese el tipo de retención');
        } else if ($('#id_tercero').val() == '0') {
            $('#SeaTercer').addClass('is-invalid');
            $('#SeaTercer').focus();
            mjeError('Seleccione un responsable del tipo de retención');
        } else {
            var data = $('#formGestTpRet').serialize();
            $.ajax({
                type: 'POST',
                url: 'datos/registrar/registrar_tipo_retencion.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalForms').modal('hide');
                        $('#tableTipoRetencion').DataTable().ajax.reload(null, false);
                        mje('Tipo de retención guardada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
        ActivaBoton(btn);
    });
    $('#divModalForms').on('click', '#btnGuardaRetencion', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#txtTipoRte').val() == '0') {
            $('#txtTipoRte').addClass('is-invalid');
            $('#txtTipoRte').focus();
            mjeError('Seleccione el tipo de retención');
        } else if ($('#txtNombreRte').val() == '') {
            $('#txtNombreRte').addClass('is-invalid');
            $('#txtNombreRte').focus();
            mjeError('Ingrese el nombre de la retención');
        } else if ($('#id_codigoCta').val() == '0') {
            $('#codigoCta').addClass('is-invalid');
            $('#codigoCta').focus();
            mjeError('Seleccione la cuenta contable');
        } else if ($('#tipoDato').val() != 'D') {
            $('#codigoCta').addClass('is-invalid');
            $('#codigoCta').focus();
            mjeError('La cuenta contable debe ser de tipo detalle');
        } else {
            var data = $('#formGestRetencion').serialize();
            $.ajax({
                type: 'POST',
                url: 'datos/registrar/registrar_retencion.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalForms').modal('hide');
                        $('#tableRetenciones').DataTable().ajax.reload(null, false);
                        mje('Retención guardada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
        ActivaBoton(btn);
    });
    $('#divModalForms').on('click', '#btnGuardaRango', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#id_retencion').val() == '0') {
            $('#buscaRetencion').addClass('is-invalid');
            $('#buscaRetencion').focus();
            mjeError('Seleccione la retención');
        } else if (Number($('#valor_base').val()) < 0) {
            $('#valor_base').addClass('is-invalid');
            $('#valor_base').focus();
            mjeError('El valor base no puede ser menor a cero');
        } else if (Number($('#valor_tope').val()) < 0) {
            $('#valor_tope').addClass('is-invalid');
            $('#valor_tope').focus();
            mjeError('El valor tope no puede ser menor a cero');
        } else if (Number($('#tarifa').val()) < 0) {
            $('#tarifa').addClass('is-invalid');
            $('#tarifa').focus();
            mjeError('La tarifa no puede ser menor a cero');
        } else {
            var data = $('#formGestRango').serialize();
            $.ajax({
                type: 'POST',
                url: 'datos/registrar/registrar_rango.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalForms').modal('hide');
                        $('#tableRangoRet').DataTable().ajax.reload(null, false);
                        mje('Rango guardado correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
        ActivaBoton(btn);
    });
    $('#modificarTipoRetencion').on('click', '.editar', function () {
        var id = $(this).attr('text');
        FormGestionTpRte(id);
    });
    $('#modificarRetencioness').on('click', '.editar', function () {
        var id = $(this).attr('text');
        FormGestionRetencion(id);
    });
    $('#modificarRangoRet').on('click', '.editar', function () {
        var id = $(this).attr('text');
        FormGestionRetRango(id);
    });
    $('#modificarTipoRetencion').on('click', '.estado', function () {
        var data = $(this).attr('text');
        $.ajax({
            type: 'POST',
            url: 'datos/registrar/cambia_estado.php',
            data: { data: data },
            success: function (r) {
                if (r == 'ok') {
                    $('#tableTipoRetencion').DataTable().ajax.reload(null, false);
                } else {
                    mjeError(r);
                }
            }
        });
    });
    $('#modificarRetencioness').on('click', '.estado', function () {
        var data = $(this).attr('text');
        $.ajax({
            type: 'POST',
            url: 'datos/registrar/cambia_estado_ret.php',
            data: { data: data },
            success: function (r) {
                if (r == 'ok') {
                    $('#tableRetenciones').DataTable().ajax.reload(null, false);
                } else {
                    mjeError(r);
                }
            }
        });
    }); $('#modificarRangoRet').on('click', '.estado', function () {
        var data = $(this).attr('text');
        $.ajax({
            type: 'POST',
            url: 'datos/registrar/cambia_estado_rango.php',
            data: { data: data },
            success: function (r) {
                if (r == 'ok') {
                    $('#tableRangoRet').DataTable().ajax.reload(null, false);
                } else {
                    mjeError(r);
                }
            }
        });
    });
    $('#modificarTipoRetencion').on('click', '.borrar', function () {
        var id = $(this).attr('text');
        Swal.fire({
            title: "¿Confirma que desea eliminar el registro?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'datos/eliminar/eliminar_tipo_retencion.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            $('#tableTipoRetencion').DataTable().ajax.reload(null, false);
                            mje('Registro eliminado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#modificarRetencioness').on('click', '.borrar', function () {
        var id = $(this).attr('text');
        Swal.fire({
            title: "¿Confirma que desea eliminar el registro?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'datos/eliminar/eliminar_retencion.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            $('#tableRetenciones').DataTable().ajax.reload(null, false);
                            mje('Registro eliminado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#tableRangoRet').on('click', '.borrar', function () {
        var id = $(this).attr('text');
        Swal.fire({
            title: "¿Confirma que desea eliminar el registro?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'datos/eliminar/eliminar_rango.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            $('#tableRangoRet').DataTable().ajax.reload(null, false);
                            mje('Registro eliminado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#divModalForms').on('input', '#buscaRetencion', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "datos/consultar/busca_retenciones.php",
                    dataType: "json",
                    type: 'POST',
                    data: { term: request.term },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $("#id_retencion").val(ui.item.id);
            }
        });
    });
})(jQuery);