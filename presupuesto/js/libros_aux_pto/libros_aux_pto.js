(function ($) {
    /*$('#frm_libros_aux_bancos').on("click", "#btn_consultar", function () {
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
    });*/
})(jQuery);

//buscar con 2 letras tipo documento
document.addEventListener("keyup", (e) => {
    if (e.target.id == "txt_tipo_doc") {
        $("#txt_tipo_doc").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/presupuesto/php/libros_aux_pto/listar_rubros.php",
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
                $("#txt_tipo_doc").val(ui.item.label);
                $("#id_cargue").val(ui.item.id);
                return false;
            },
            focus: function (event, ui) {
                $("#txt_tipo_doc").val(ui.item.label);
                return false;
            },
        });
    }
});