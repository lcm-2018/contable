(function ($) {
    $('#frm_libros_aux_bancos').on("click", "#btn_consultar", function () {
        $.post(window.urlin + '/tesoreria/php/informes_bancos/imp_libros_bancos.php', {
            id_cuenta_ini: $('#id_txt_cuentainicial').val(),
            id_cuenta_fin: $('#id_txt_cuentafinal').val(),
            fec_ini: $('#txt_fecini').val(),
            fec_fin: $('#txt_fecfin').val(),
            id_tipo_doc: $('#sl_tipo_documento').val(),
            id_tercero: $('#id_txt_tercero').val()
        }, function (he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });
})(jQuery);

//buscar con 2 letras cuentas y terceros 
document.addEventListener("keyup", (e) => {
    if (e.target.id == "txt_cuentainicial") {
        $("#txt_cuentainicial").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/tesoreria/php/informes_bancos/buscar_cuentas.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        term: request.term,
                    },
                    success: function (data) {
                        response(data);
                    },
                });
            },
            select: function (event, ui) {
                $("#txt_cuentainicial").val(ui.item.label);
                $("#id_txt_cuentainicial").val(ui.item.id);
                return false;
            },
            focus: function (event, ui) {
                $("#txt_cuentainicial").val(ui.item.label);
                return false;
            },
        });
    }
    if (e.target.id == "txt_cuentafinal") {
        $("#txt_cuentafinal").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/tesoreria/php/informes_bancos/buscar_cuentas.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        term: request.term,
                    },
                    success: function (data) {
                        response(data);
                    },
                });
            },
            select: function (event, ui) {
                $("#txt_cuentafinal").val(ui.item.label);
                $("#id_txt_cuentafinal").val(ui.item.id);
                return false;
            },
            focus: function (event, ui) {
                $("#txt_cuentafinal").val(ui.item.label);
                return false;
            },
        });
    }
    if (e.target.id == "txt_tercero_filtro") {
        $("#txt_tercero_filtro").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/tesoreria/php/informes_bancos/buscar_terceros.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        term: request.term,
                    },
                    success: function (data) {
                        response(data);
                    },
                });
            },
            select: function (event, ui) {
                $("#txt_tercero_filtro").val(ui.item.label);
                $("#id_txt_tercero").val(ui.item.id);
                return false;
            },
            focus: function (event, ui) {
                $("#txt_tercero_filtro").val(ui.item.label);
                return false;
            },
        });
    }
});