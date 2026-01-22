(function ($) {
    $('#frm_supersalud').on("click", "#btn_buscar", function () {
        $tipo_libro = $('#sl_tipo_libro').val();
        if ($tipo_libro == 1) {
            $.post( window.urlin + '/contabilidad/php/supersalud/imp_cuentasxpagar.php', {
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