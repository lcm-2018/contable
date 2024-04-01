(function ($) {
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
        //dataTable adquisiciones
        $('#tableConsultas').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("formadd_consulta.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
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
            alert('Debe ingresar al menos un parámetro');
            return false;
        } else if ($('#txtConsultaSQL').val() == '') {
            alert('Debe ingresar una consulta SQL');
            return false;
        } else if ($('#txtNombreConsulta').val() == '') {
            alert('Debe ingresar un nombre para la consulta');
            return false;
        } else {
            let datos = $('#formAddConsulta').serialize();
            $.ajax({
                type: 'POST',
                url: 'new_consulta.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableConsultas';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Consulta agregada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });

        }

    });
    $("#accionConsultas").on("click", ".ejecuta", function () {
        let id = $(this).attr('value');
        $.post("ejecuta_sql.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#divForms").on("click", "#btnEjecutarConsulta", function () {
        let parametros = $('#formParams').serialize();
        $.post("crea_tabla.php", parametros, function (he) {
            $("#resultado").html(he);
        });
    });
})(jQuery);