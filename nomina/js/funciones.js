(function ($) {
    $('#accionListInfoNomina').on('click', '.infoNomina', function () {
        let id = $(this).attr('value');
        let id_nomina = $(this).parent().parent().find('input').val();
        if (Number(id_nomina) <= 0 && Number(id) != 7) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se ha indicado el id de la nómina');
        } else {
            var ruta = '';
            if (id == '1') {
                ruta = 'imp_libranzas';
            } else if (id == '2') {
                ruta = 'imp_embargos';
            } else if (id == '3') {
                ruta = 'imp_sindicatos';
            } else if (id == '4') {
                ruta = 'imp_desprendibles_nomina';
            } else if (id == '5') {
                ruta = 'imp_liq_conceptos';
            } else if (id == '6') {
                ruta = 'imp_parafiscales';
            } else if (id == '7') {
                ruta = 'imp_siho';
                id_nomina = 0;
            }
            $.post(ruta + '.php', { id_nomina: id_nomina }, function (he) {
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(he);
            });
        }
    });
    $('#accionNominas').on('click', '.solcdp', function () {
        let id = $(this).attr('value');
        $.post('../../informes/imp_solicitud_cdp.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#accionNominas').on('click', '.cpdPatronal', function () {
        let id = $(this).attr('value');
        $.post('../../informes/imp_solicitud_cdp_patronal.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#accionNominas').on('click', '.carguePatronal', function () {
        let id = $(this).attr('value');
        $.post('../../informes/form_cargue.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#accionNominas').on('click', '.comparePatronal', function () {
        let id = $(this).attr('value');
        $.post('../../informes/form_compare.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //agregar  horas extra desde excel
    $('#divModalForms').on('click', '#btnCarguePlanilla', function () {
        var id = $('#id_nomina').val();
        if ($('#filePlanilla').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe elegir un archivo!');
        } else {
            let archivo = $('#filePlanilla').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext !== '.xlsx') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Solo se permite documentos .xlsx!');
                return false;
            } else if ($('#filePlanilla')[0].files[0].size > 2097152) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Documento debe tener un tamaño menor a 2Mb!');
                return false;
            }
            let datos = new FormData();
            datos.append('filePlanilla', $('#filePlanilla')[0].files[0]);
            datos.append('id_nomina', id);
            $('#btnCarguePlanilla').attr('disabled', true);
            $('#btnCarguePlanilla').html('<i class="fas fa-spinner fa-pulse"></i> Cargando...');
            $.ajax({
                type: 'POST',
                url: '../datos/registrar/cargar_planilla.php',
                contentType: false,
                data: datos,
                processData: false,
                cache: false,
                success: function (r) {
                    $('#btnCarguePlanilla').attr('disabled', false);
                    $('#btnCarguePlanilla').html('Subir');
                    if (r.trim() === 'ok') {
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Planilla de aportes patronales cargada correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $('#divModalForms').on('click', '#btnComparePlanilla', function () {
        var id = $('#id_nomina').val();
        if ($('#filePlanilla').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe elegir un archivo!');
        } else {
            let archivo = $('#filePlanilla').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext !== '.xlsx') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Solo se permite documentos .xlsx!');
                return false;
            } else if ($('#filePlanilla')[0].files[0].size > 2097152) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Documento debe tener un tamaño menor a 2Mb!');
                return false;
            }
            let datos = new FormData();
            datos.append('filePlanilla', $('#filePlanilla')[0].files[0]);
            datos.append('id_nomina', id);
            $('#btnCarguePlanilla').attr('disabled', true);
            $('#btnCarguePlanilla').html('<i class="fas fa-spinner fa-pulse"></i> Cargando...');
            $.ajax({
                type: 'POST',
                url: '../datos/registrar/comparar_planilla.php',
                contentType: false,
                data: datos,
                processData: false,
                cache: false,
                success: function (r) {
                    $('#btnCarguePlanilla').attr('disabled', false);
                    $('#btnCarguePlanilla').html('Comparar');
                    var encoded = window.btoa(r);
                    $('<form action="' + window.urlin + '/almacen/informes/reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encoded + '" /></form>').appendTo('body').submit();
                }
            });
        }
        return false;
    });
    $('#divModalForms').on('click', '#btnReporteGral', function () {
        let xls = ($('#areaImprimir').html());
        var encoded = window.btoa(xls);
        $('<form action="' + window.urlin + '/nomina/informes/reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encoded + '" /></form>').appendTo('body').submit();
    });
    $('#accionNominas').on('click', '.impPDF', function () {
        let id = $(this).attr('value');
        $.post('../../informes/imp_pdf.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-2x');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '.desprendible', function () {
        let id_nomina = $('#id_nomina').val();
        let cedula = $('#cedula').val();
        let sede = $('#slcSede').val();
        let accion = $(this).attr('value');
        if (accion == '1') {
            $(this).attr('disabled', 'disabled');
            $(this).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
        }
        $.post('imp_desprendibles_nomina.php', { id_nomina: id_nomina, cedula: cedula, accion: accion, sede: sede }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#conceptos_nomina', function () {
        let id_nomina = $('#id_nomina').val();
        let concepto = $('#concepto').val();
        $.post('imp_liq_conceptos.php', { id_nomina: id_nomina, concepto: concepto }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnGenSiho', function () {
        let check = $('#chAcumula').is(':checked');
        var acumulado;
        if (check) {
            acumulado = 1;
        } else {
            acumulado = 0;
        }
        let trimestre = $('#slcTrimestre').val();
        $.post('imp_siho.php', { acumulado: acumulado, trimestre: trimestre }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
})(jQuery);