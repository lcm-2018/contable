(function ($) {
    //---- ejemplos de ocasionar disparador - trigger en los select y los botones
    //$('#sl_centroCosto').trigger('change');
    //$('#btn_buscar_filtro').trigger('click');

    //otra opcion de llamar botones
    /*$('#btn_imprimir').on("click", function()
    {
        alert("imprimiendo!.....");
    });*/

    $('#frm_libros_aux_tesoreria').on("click", "#btn_consultar", function () {
        $tipo_libro = $('#sl_tipo_libro').val();
        if ($tipo_libro == 1) {
            $.post( window.urlin + '/tesoreria/php/informes/imp_rel_causacion.php', {
                fec_ini:  $('#txt_fecini').val(),
                fec_fin: $('#txt_fecfin').val()
            }, function(he) {
                $('#divTamModalImp').removeClass('modal-sm');
                $('#divTamModalImp').removeClass('modal-lg');
                $('#divTamModalImp').addClass('modal-xl');
                $('#divModalImp').modal('show');
                $("#divImp").html(he);
            });
        }
        else {
            $.post( window.urlin + '/tesoreria/php/informes/imp_rel_compr_egresos.php', {
                fec_ini:  $('#txt_fecini').val(),
                fec_fin: $('#txt_fecfin').val()
            }, function(he) {
                $('#divTamModalImp').removeClass('modal-sm');
                $('#divTamModalImp').removeClass('modal-lg');
                $('#divTamModalImp').addClass('modal-xl');
                $('#divModalImp').modal('show');
                $("#divImp").html(he);
            });
        }
    });
})(jQuery);