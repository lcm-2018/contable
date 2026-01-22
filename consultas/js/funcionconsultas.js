(function ($) {
    $(document).ready(function () {
        //dataTable adquisiciones
        var id_consulta = $('#id_consulta').length ? $('#id_consulta').val() : '0';
        $('#tableConsultas').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("formadd_consulta.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-fullscreen');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos.php',
                type: 'POST',
                dataType: 'json',
                data: { id_consulta: id_consulta }
            },
            "columns": [
                { 'data': 'id_consulta' },
                { 'data': 'nombre' },
                { 'data': 'fec_reg' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableConsultas').wrap('<div class="overflow" />');
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus fa-lg"></span>');
    });
    $("#divForms").on("click", "#btnAddConsulta", function () {
        if ($('#jsonParam').val() == '') {
            mjeError('Debe ingresar un JSON de parámetros');
        } else if ($('#txtConsultaSQL').val() == '') {
            mjeError('Debe ingresar una consulta SQL');
        } else if ($('#txtNombreConsulta').val() == '') {
            mjeError('Debe ingresar un nombre para la consulta');
        } else {
            let datos = $('#formAddConsulta').serialize();
            datos += '&id_consulta=' + $('#id_consulta').val();
            $.ajax({
                type: 'POST',
                url: 'new_consulta.php',
                data: datos,
                success: function (r) {
                    if (r === 'ok') {
                        $('#divModalForms').modal('hide');
                        $('#tableConsultas').DataTable().ajax.reload();
                        mje('Consulta agregada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });

        }
        return false;
    });
    $("#accionConsultas").on("click", ".ejecuta", function () {
        let id = $(this).attr('value');
        $.post("ejecuta_sql.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-fullscreen');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#divForms").on("click", "#btnEjecutarConsulta", function () {
        let parametros = $('#formParams').serialize();
        $.post("crea_tabla.php", parametros, function (response) {
            try {
                let data = typeof response === 'string' ? JSON.parse(response) : response;
                $("#resultado").empty();
                const container = document.getElementById('resultado');
                const hot = new Handsontable(container, {
                    data: data,
                    colHeaders: Object.keys(data[0] || {}),
                    rowHeaders: true, // Muestra encabezados de fila
                    filters: true, // Habilita filtros
                    dropdownMenu: true, // Habilita menú desplegable
                    stretchH: 'all', // Ajusta las columnas al ancho del contenedor
                    height: 400, // Ajusta la altura según el tamaño de tu modal
                    width: '100%', // Ajusta al ancho del contenedor
                    licenseKey: 'non-commercial-and-evaluation', // Versión comunitaria
                    className: 'htLeft',
                });
            } catch (error) {
                console.error("Error procesando la respuesta del servidor:", error);
                mjeError("Error al generar la tabla.");
            }
        });
    });
})(jQuery);