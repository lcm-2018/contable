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
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
    });
    $('#divModalForms').on('click', '#btnGuardaTpRte', function () {
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
                        $('#tableTipoRetencion').DataTable().ajax.reload();
                        mje('Tipo de retención guardada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
    $('#divModalForms').on('click', '#btnGuardaRetencion', function () {
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
                        $('#tableRetenciones').DataTable().ajax.reload();
                        mje('Retención guardada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
    $('#modificarTipoRetencion').on('click', '.editar', function () {
        var id = $(this).attr('text');
        FormGestionTpRte(id);
    });
    $('#modificarRetencioness').on('click', '.editar', function () {
        var id = $(this).attr('text');
        FormGestionRetencion(id);
    });
    $('#modificarTipoRetencion').on('click', '.estado', function () {
        var data = $(this).attr('text');
        $.ajax({
            type: 'POST',
            url: 'datos/registrar/cambia_estado.php',
            data: { data: data },
            success: function (r) {
                if (r == 'ok') {
                    $('#tableTipoRetencion').DataTable().ajax.reload();
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
                    $('#tableRetenciones').DataTable().ajax.reload();
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
                            $('#tableTipoRetencion').DataTable().ajax.reload();
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
                            $('#tableRetenciones').DataTable().ajax.reload();
                            mje('Registro eliminado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
})(jQuery);