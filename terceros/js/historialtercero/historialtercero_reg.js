(function ($) {
    $(document).ready(function () {
        $('#tb_cdps').DataTable({
            //va con este codigo para que no se muestre el boton de + encima
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("", { id_articulo: $('#id_tercero').val() }, function (he) {
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
                data: function (data) {
                    data.id_tercero = $('#id_tercero').val();
                    data.nrodisponibilidad = $('#txt_nrodisponibilidad_filtro').val();
                    data.fecini = $('#txt_fecini_filtro').val();
                    data.fecfin = $('#txt_fecfin_filtro').val();
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
        $('#id_cdp').val(id_cdp);
        //----------esto pa cargar modal con clic en el boton
        //$.post("../php/historialtercero/frm_historialtercero.php", { idt: idt }, function (he) {
        //    $('#divTamModalForms').removeClass('modal-lg');
        //   $('#divTamModalForms').removeClass('modal-sm');
        //    $('#divTamModalForms').addClass('modal-xl');
        //    $('#divModalForms').modal('show');
        //    $("#divForms").html(he);
        //    $('#slcActEcon').focus();
        //});

        //------------ cargar la tabla contratos
        if ($.fn.DataTable.isDataTable('#tb_contratos')) {
            $('#tb_contratos').DataTable().destroy();
        }

        $('#tb_contratos').DataTable({
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("", { id_cdp: id_cdp }, function (he) {
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
                data: function (data) {
                    data.id_cdp = id_cdp;
                }
            },
            columns: [
                { 'data': 'num_contrato' },
                { 'data': 'fec_ini' },
                { 'data': 'fec_fin' },
                { 'data': 'val_contrato' },
                { 'data': 'val_adicion' },
                { 'data': 'val_cte' },
                { 'data': 'estado' }
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

        //------------ cargar la tabla registro presupuestal
        if ($.fn.DataTable.isDataTable('#tb_reg_presupuestal')) {
            $('#tb_reg_presupuestal').DataTable().destroy();
        }
        $('#tb_reg_presupuestal').DataTable({
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("", { id_cdp: id_cdp }, function (he) {
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
                url: window.urlin + '/terceros/php/historialtercero/listar_reg_presupuestal.php',
                type: 'POST',
                dataType: 'json',
                data: function (data) {
                    data.id_cdp = id_cdp;
                }
            },
            columns: [
                { 'data': 'id_pto_crp' },
                { 'data': 'id_manu' },
                { 'data': 'fecha' },
                { 'data': 'tipo' },
                { 'data': 'num_contrato' },
                { 'data': 'vr_registro' },
                { 'data': 'vr_saldo' },
                { 'data': 'estado' },
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
        $('#tb_reg_presupuestal').wrap('<div class="overflow"/>');

        //------------ cargar la tabla obligaciones
        if ($.fn.DataTable.isDataTable('#tb_obligaciones')) {
            $('#tb_obligaciones').DataTable().destroy();
        }
        $('#tb_obligaciones').DataTable({
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("", { id_cdp: id_cdp }, function (he) {
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
                url: window.urlin + '/terceros/php/historialtercero/listar_obligaciones.php',
                type: 'POST',
                dataType: 'json',
                data: function (data) {
                    data.id_cdp = id_cdp;
                }
            },
            columns: [
                { 'data': 'id_ctb_doc' },
                { 'data': 'fecha' },
                { 'data': 'num_doc' },
                { 'data': 'valorcausado' },
                { 'data': 'descuentos' },
                { 'data': 'neto' },
                { 'data': 'estado' }
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
        $('#tb_obligaciones').wrap('<div class="overflow"/>');

        //------------ cargar la tabla pagos
        if ($.fn.DataTable.isDataTable('#tb_pagos')) {
            $('#tb_pagos').DataTable().destroy();
        }
        $('#tb_pagos').DataTable({
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("", { id_cdp: id_cdp }, function (he) {
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
                url: window.urlin + '/terceros/php/historialtercero/listar_pagos.php',
                type: 'POST',
                dataType: 'json',
                data: function (data) {
                    data.id_cdp = id_cdp;
                }
            },
            columns: [
                { 'data': 'id_manu' },
                { 'data': 'fecha' },
                { 'data': 'detalle' },
                { 'data': 'valorpagado' },
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2] }
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
        $('#tb_pagos').wrap('<div class="overflow"/>');
    });

    //------------ filtros
    $('#btn_buscar_filtro').on("click", function () {
        reloadtable('tb_cdps');

        $('#tb_contratos').empty();
        $('#tb_contratos').DataTable();

        $('#tb_reg_presupuestal').empty();
        $('#tb_reg_presupuestal').DataTable();

        $('#tb_obligaciones').empty();
        $('#tb_obligaciones').DataTable();

        $('#tb_pagos').empty();
        $('#tb_pagos').DataTable();

        $('#id_cdp').val('');
    });

    $('.filtro').keypress(function (e) {
        if (e.keyCode == 13) {
            reloadtable('tb_cdps');

            $('#tb_contratos').empty();
            $('#tb_contratos').DataTable();

            $('#tb_reg_presupuestal').empty();
            $('#tb_reg_presupuestal').DataTable();

            $('#tb_obligaciones').empty();
            $('#tb_obligaciones').DataTable();

            $('#tb_pagos').empty();
            $('#tb_pagos').DataTable();
        }
    });
    //---- ejemplos de ocasionar disparador - trigger en los select y los botones
    //$('#sl_centroCosto').trigger('change');
    //$('#btn_buscar_filtro').trigger('click');

    //otra opcion de llamar botones
    /*$('#btn_imprimir').on("click", function()
    {
        alert("imprimiendo!.....");
    });*/

    $('#divForms').on("click", "#btn_imprimir", function () {
        $.post(window.urlin + '/terceros/php/historialtercero/imp_historialtercero.php', {
            id_tercero: $('#id_tercero').val(),
            id_cdp: $('#id_cdp').val()
        }, function (he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    //------------------- boton liberar saldos
    $('#body_tb_cdps').on('click', '.btn_liberar', function () {
        let id_cdp = $(this).attr('value');
        $('#id_cdp').val(id_cdp);

        //----------esto pa cargar modal con clic en el boton
        $.post(window.urlin + "/terceros/php/historialtercero/frm_liberarsaldos.php", { id_cdp: id_cdp }, function (he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //--------------------
    $('#divFormsReg').on("click", "#btn_liquidar", function () {
        if ($('#txt_fec_lib').val() < $('#txt_fec_cdp').val()) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La fecha de liberación no puede ser menor a la fecha del CDP');
        }
        else {

            let datos = $('#frm_liberarsaldos').serialize();
            let url;
            url = window.urlin + '/terceros/php/historialtercero/registrar_liberacion.php';
            $.ajax({
                type: 'POST',
                url: url,
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tb_cdps';
                        reloadtable(id);
                        $('#divFormsReg').modal('hide');
                        mje('Liberacion ejecutada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
    //------------------- boton liberar saldos crp
    $('#body_tb_reg_presupuestal').on('click', '.btn_liberar_crp', function () {
        let id_crp = $(this).attr('value');
        //$('#id_cdp').val(id_cdp);

        //----------esto pa cargar modal con clic en el boton
        $.post(window.urlin + "/terceros/php/historialtercero/frm_liberarsaldos_crp.php", { id_crp: id_crp }, function (he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });
    //-----------------------------------------------------
    $('#divFormsReg').on("click", "#btn_liquidar_saldos_crp", function () {
        if ($('#txt_fec_lib_crp').val() < $('#txt_fec_crp').val()) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La fecha de liberación no puede ser menor a la fecha del CRP');
        }
        else {
            let datos = $('#frm_liberarsaldos_crp').serialize();
            let url;
            url = window.urlin + '/terceros/php/historialtercero/registrar_liberacion_crp.php';
            $.ajax({
                type: 'POST',
                url: url,
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tb_reg_presupuestal';
                        reloadtable(id);
                        $('#divFormsReg').modal('hide');
                        mje('Liberacion ejecutada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
})(jQuery);