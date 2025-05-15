(function ($) {
    $('#frm_libros_aux_presupuesto').on("click", "#btn_consultar", function () {        
        var id_cargue = 0;
        id_cargue = $('#id_cargue').val();
        var doc_fuente = 0;
        doc_fuente = $('#sl_doc_fuente').val();
        if (id_cargue == 0) {
            mjeError("Seleccione un tipo de documento");
        }
        else {
            if (doc_fuente != 1) {
                mjeError("Seleccione documento fuente CDP");
            }
            else {
                $.post(window.urlin + '/presupuesto/php/libros_aux_pto/imp_libros_aux_pto.php', {
                    id_cargue: id_cargue,
                    doc_fuente: doc_fuente,
                    fec_ini: $('#txt_fecini').val(),
                    fec_fin: $('#txt_fecfin').val(),
                }, function (he) {
                    $('#divTamModalImp').removeClass('modal-sm');
                    $('#divTamModalImp').removeClass('modal-lg');
                    $('#divTamModalImp').addClass('modal-xl');
                    $('#divModalImp').modal('show');
                    $("#divImp").html(he);
                });
            }
        }
    });
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