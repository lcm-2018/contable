(function($) {
    $(document).ready(function() {
        $('#tb_cdps').DataTable({
            //va con este codigo para que no se muestre el boton de + encima
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("", { id_articulo: $('#id_tercero').val() }, function(he) {
                        $('#divTamModalReg').removeClass('modal-xl');
                        $('#divTamModalReg').removeClass('modal-sm');
                        $('#divTamModalReg').addClass('modal-lg');
                        $('#divModalReg').modal('show');
                        $("#divFormsReg").html(he);
                    });
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: window.urlin + '/terceros/php/historialtercero/listar_cdps.php', 
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_tercero = $('#id_tercero').val();
                }
            },
            columns: [
                { 'data': 'id_pto_cdp' }, 
                { 'data': 'nit_tercero' },
                { 'data': 'fecha' },
                { 'data': 'objeto' },
                { 'data': 'valor_cdp' },
                { 'data': 'saldo' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [3] }
            ],
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_cdps').wrap('<div class="overflow"/>');
        //$('#tb_cdps_wrapper').addClass("w-100");
    });

    //-------------------------------
    //---boton listar de la tabla cdps
    $('#body_tb_cdps').on('click', '.btn_listar', function () {
        let id_cdp = $(this).attr('value');
        
        //----------esto pa cargar modal 
        //$.post("../php/historialtercero/frm_historialtercero.php", { idt: idt }, function (he) {
        //    $('#divTamModalForms').removeClass('modal-lg');
        //   $('#divTamModalForms').removeClass('modal-sm');
        //    $('#divTamModalForms').addClass('modal-xl');
        //    $('#divModalForms').modal('show');
        //    $("#divForms").html(he);
        //    $('#slcActEcon').focus();
        //});

        //------------ para intentar cargar la nueva tabla
        $('#tb_contratos').DataTable({
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("", { id_cdp: id_cdp }, function(he) {
                        $('#divTamModalReg').removeClass('modal-xl');
                        $('#divTamModalReg').removeClass('modal-sm');
                        $('#divTamModalReg').addClass('modal-lg');
                        $('#divModalReg').modal('show');
                        $("#divFormsReg").html(he);
                    });
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: window.urlin + '/terceros/php/historialtercero/listar_contratos.php', 
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_tercero = $('#id_tercero').val();
                }
            },
            columns: [
                { 'data': 'id_pto_cdp' }, 
                { 'data': 'nit_tercero' },
                { 'data': 'fecha' },
                { 'data': 'objeto' },
                { 'data': 'valor_cdp' },
                { 'data': 'saldo' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [3] }
            ],
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_contratos').wrap('<div class="overflow"/>');
        //$('#tb_cdps_wrapper').addClass("w-100");
        //------------------------------------------------
    });
})(jQuery);