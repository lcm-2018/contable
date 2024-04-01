(function ($) {
    //Superponer modales
    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function () {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    var reloadtable = function (nom) {
        $(document).ready(function () {
            var table = $('#' + nom).DataTable();
            table.ajax.reload();
        });
    };
    var confdel = function (i, t) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: window.urlin + '/almacen/eliminar/confirdel.php',
            data: { id: i, tip: t }
        }).done(function (res) {
            $('#divModalConfDel').modal('show');
            $('#divMsgConfdel').html(res.msg);
            $('#divBtnsModalDel').html(res.btns);
        });
        return false;
    };
    var setIdioma = {
        "decimal": "",
        "emptyTable": "No hay información",
        "info": "Mostrando _START_ - _END_ registros de _TOTAL_ ",
        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
        "infoFiltered": "(Filtrado de _MAX_ entradas en total )",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "Ver _MENU_ Filas",
        "loadingRecords": "Cargando...",
        "processing": "Procesando...",
        "search": '<i class="fas fa-search fa-flip-horizontal" style="font-size:1.5rem; color:#2ECC71;"></i>',
        "zeroRecords": "No se encontraron registros",
        "paginate": {
            "first": "&#10096&#10096",
            "last": "&#10097&#10097",
            "next": "&#10097",
            "previous": "&#10096"
        }
    };
    var setdom;
    if ($("#peReg").val() === '1') {
        setdom = "<'row'<'col-md-5'l><'bttn-plus-dt col-md-2'B><'col-md-5'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    } else {
        setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    }
    $(document).ready(function () {
        $('#tableCertfForm220').DataTable({
            language: setIdioma,
            "pageLength": 100,
            "ajax": {
                url: 'datos/listar/empleados_form220.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'doc' },
                { 'data': 'apellidos' },
                { 'data': 'nombres' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableCertfForm220').wrap('<div class="overflow" />');
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
    });
    $('#btnGenCertifForm220').on('click', function () {
        $.post("datos/listar/form_lista_empleados.php", function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#txtBuscTercero').on('input', function () {
        var tipo = $('#slcTipoCertf').val();
        var boton = '';
        $('.form-control').removeClass('is-invalid');
        if (tipo == '0') {
            $('#slcTipoCertf').focus();
            $('#slcTipoCertf').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Elegir tipo de certificado');
            return false;
        }
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "datos/listar/buscar_terceros.php",
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
                let cc = ui.item.id;
                $('#noDocTercero').val(cc);
                if (tipo == '2' || tipo == '3') {
                    boton = '<a id="btnListContratos" class="btn btn-outline-success btn-sm btn-block mb-2" data-toggle="collapse" href="#listContratos" role="button" aria-expanded="false" aria-controls="collapseExample">' +
                        '<i class="fas fa-eye fa-lg"></i> Ver Contratos' +
                        '</a>' +
                        '<div class="collapse" id="listContratos">' +
                        '</div>';
                    $('#divListContratos').html(boton);
                }
            }
        });
    });
    $('#slcTipoCertf').on('change', function () {
        $('#txtBuscTercero').val('');
        $('#noDocTercero').val('0');
        $('#divListContratos').html('');
    });
    $('#divListContratos').on('click', '#btnListContratos', function () {
        let cc = $('#noDocTercero').val();
        let fini = $('#fecInicia').val();
        let ffin = $('#fecFin').val();
        $.post("datos/listar/contratos.php", { cc: cc, fini: fini, ffin: ffin }, function (he) {
            $("#listContratos").html(he);
        });
    });
    $('#btnGenCertificado').on('click', function () {
        $('.form-control').removeClass('is-invalid');
        if ($('#slcTipoCertf').val() == '0') {
            $('#slcTipoCertf').focus();
            $('#slcTipoCertf').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Elegir tipo de certificado');
        } else if ($('#noDocTercero').val() == '0') {
            $('#txtBuscTercero').focus();
            $('#txtBuscTercero').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Elegir un tercero válido');
        } else {
            var ruta = '';
            let valor = $('#slcTipoCertf').val();
            var elemento = $(this);
            switch (valor) {
                case '1':
                    ruta = window.urlin + "/nomina/certificaciones/registrar/formularios220.php";
                    break;
                case '2':
                case '3':
                    ruta = window.urlin + "/nomina/certificaciones/registrar/claboral.php";
                    break;
                case '4':
                    ruta = window.urlin + "/nomina/certificaciones/registrar/clnomina.php";
                    break;
                default:
                    ruta = '';
                    break;
            }
            if (valor == '2' || valor == '3') {
                var valida = 0;
                $('input[type="checkbox"]').each(function () {
                    if ($(this).is(":checked")) {
                        valida++;
                    }
                });
                if (valida == 0) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html("Debe elegir al menos un contrato");
                    return false;
                }
            }
            let data = $('#formGenCertificado').serialize();
            elemento.attr('disabled', true);
            elemento.html('<i class="fas fa-spinner fa-spin fa-xs"></i> Generando...');
            $.ajax({
                type: 'POST',
                url: ruta,
                data: data,
                dataType: 'json',
                success: function (r) {
                    elemento.attr('disabled', false);
                    elemento.html('<i class="fas fa-atom fa-xs"></i> Generar');
                    if (r.status.trim() === 'ok') {
                        var downloadLink = document.createElement('a');
                        downloadLink.href = 'data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,' + r.msg;
                        downloadLink.download = r.name;
                        downloadLink.click();
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r.msg);
                    }
                }
            });
        }
        return false;
    });
    $('#divListContratos').on('change', '#selectAll', function () {
        if ($(this).prop('checked')) {
            $('#selectAll').attr('title', 'Desmarcar todos');
        } else {
            $('#selectAll').attr('title', 'Marcar todos');
        }

        $('input[type=checkbox]').prop('checked', $(this).is(':checked'));
    });
})(jQuery);