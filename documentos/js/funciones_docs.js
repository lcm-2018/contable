(function ($) {
    //Función para mostrar formulario de gestión de documentos
    FormGestionDocs = function (ids) {
        $.post("datos/formulario/form_gestion_docs.php", { ids: ids }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    }
    FormDetalleDocs = function (data) {
        $.post("datos/formulario/form_responsable_docs.php", { data: data }, function (he) {
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    }
    $(document).ready(function () {
        $('#tableGeDocs').DataTable({
            dom: setdom,
            language: setIdioma,
            buttons: [{
                //Registar modalidad de contratación
                action: function (e, dt, node, config) {
                    FormGestionDocs(0);
                }
            }],
            ajax: {
                url: 'datos/listar/documentos.php',
                type: 'POST',
                dataType: 'json',
            },
            columns: [
                { 'data': 'id' },
                { 'data': 'modulo' },
                { 'data': 'doc' },
                { 'data': 'version' },
                { 'data': 'fecha' },
                { 'data': 'control' },
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
        $('#tableGeDocs').wrap('<div class="overflow" />');
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
    });
    $('#divModalForms').on('click', '#btnGuardarDocs', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#id_doc_fte').val() == '0') {
            $('#id_doc_fte').addClass('is-invalid');
            $('#id_doc_fte').focus();
            mjeError('Seleccione el tipo fuente');
        } else if ($('#id_modulo').val() == '0') {
            $('#id_modulo').addClass('is-invalid');
            $('#id_modulo').focus();
            mjeError('Seleccione el módulo');
        } else if ($('#version_doc').val() == '') {
            $('#version_doc').addClass('is-invalid');
            $('#version_doc').focus();
            mjeError('Ingrese la versión del documento');
        } else if ($('#fecha_doc').val() == '') {
            $('#fecha_doc').addClass('is-invalid');
            $('#fecha_doc').focus();
            mjeError('Ingrese la fecha del documento');
        } else {
            var data = $('#formGestDocs').serialize();
            $.ajax({
                type: 'POST',
                url: 'proceso/guarda_documento.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalForms').modal('hide');
                        $('#tableGeDocs').DataTable().ajax.reload();
                        mje('Documento guardado correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
        ActivaBoton(btn);
    });
    $('#divModalForms').on('click', '#btnGuardarDetDocs', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#control').val() == '1' && $('#tipo_control').val() == '0') {
            $('#tipo_control').addClass('is-invalid');
            $('#tipo_control').focus();
            mjeError('Seleccione el tipo de control');
        } else if ($('#id_tercero').val() == '0') {
            $('#SeaTercer').addClass('is-invalid');
            $('#SeaTercer').focus();
            mjeError('Seleccione un responsable del documento');
        } else if ($('#cargo_resp').val() == '') {
            $('#cargo_resp').addClass('is-invalid');
            $('#cargo_resp').focus();
            mjeError('Ingrese el cargo del responsable');
        } else if ($('#fecha_ini').val() == '') {
            $('#fecha_ini').addClass('is-invalid');
            $('#fecha_ini').focus();
            mjeError('Ingrese la fecha de inicio');
        } else if ($('#fecha_fin').val() == '') {
            $('#fecha_fin').addClass('is-invalid');
            $('#fecha_fin').focus();
            mjeError('Ingrese la fecha de fin');
        } else if ($('#fecha_fin').val() < $('#fecha_ini').val()) {
            $('#fecha_fin').addClass('is-invalid');
            $('#fecha_fin').focus();
            mjeError('La fecha de fin no puede ser menor a la fecha de inicio');
        } else {
            var data = $('#formGestDetDocs').serialize();
            $.ajax({
                type: 'POST',
                url: 'proceso/guarda_responsable.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        var d = btoa($('#id_maestro').val() + '|0');
                        FormDetalleDocs(d);
                        mje('Documento guardado correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
        ActivaBoton(btn);
    });
    $('#modificarGeDocs').on('click', '.editar', function () {
        var id = $(this).attr('text');
        FormGestionDocs(id);
    });
    $('#modificarGeDocs').on('click', '.detalles', function () {
        var data = $(this).attr('text');
        FormDetalleDocs(data);
    });
    $('#divModalForms').on('click', '#modificarDetDocs .editar', function () {
        var data = $(this).attr('text');
        FormDetalleDocs(data);
    }); $('#divModalForms').on('click', '#modificarDetDocs .estado', function () {
        var data = $(this).attr('text');
        $.ajax({
            type: 'POST',
            url: 'proceso/upestadodet.php',
            data: { data: data },
            success: function (r) {
                if (r == 'ok') {
                    var d = btoa($('#id_maestro').val() + '|0');
                    FormDetalleDocs(d);
                } else {
                    mjeError(r);
                }
            }
        });
    });
    $('#divModalForms').on('click', '#modificarDetDocs .borrar', function () {
        var data = $(this).attr('text');
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
                    url: 'proceso/elimina_detalle.php',
                    data: { data: data },
                    success: function (r) {
                        if (r == 'ok') {
                            var d = btoa($('#id_maestro').val() + '|0');
                            FormDetalleDocs(d);
                            mje('Registro eliminado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#modificarGeDocs').on('click', '.estado', function () {
        var data = $(this).attr('text');
        $.ajax({
            type: 'POST',
            url: 'proceso/upestado.php',
            data: { data: data },
            success: function (r) {
                if (r == 'ok') {
                    $('#tableGeDocs').DataTable().ajax.reload();
                } else {
                    mjeError(r);
                }
            }
        });
    });
    $('#modificarGeDocs').on('click', '.borrar', function () {
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
                    url: 'proceso/elimina_documento.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            $('#tableGeDocs').DataTable().ajax.reload();
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