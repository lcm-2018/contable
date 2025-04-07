(function ($) {
    //---boton buscar
    $("#divForms").on("click", "#btn_buscar" , function () {
        var fecha = $('#txt_fecha').val();
        //------------ cargar la tabla terceros segun la fecha
        if ($.fn.DataTable.isDataTable('#tb_terceros')) {
            $('#tb_terceros').DataTable().destroy();
        }

        $('#tb_terceros').DataTable({
            dom: setdom = "<'row'<'col-md-6'l><'col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: window.urlin + '/tesoreria/php/historico_pagos_pendientes/listar_terceros.php',
                type: 'POST',
                dataType: 'json',
                data: function (data) {
                    data.fecha = fecha;
                }
            },
            columns: [
                { 'data': 'id_tercero_api' },
                { 'data': 'nit_tercero' },
                { 'data': 'nom_tercero' },
               // { 'data': 'id_ctb_doc' },
                { 'data': 'fecha_credito' },
                { 'data': 'sumacredito' },
                { 'data': 'menos30' },
                { 'data': 'de30a60' },
                { 'data': 'de60a90' },
                { 'data': 'de90a180' },
                { 'data': 'de180a360' },
                { 'data': 'mas360' },
                { 'data': 'saldo' },
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
        $('#tb_contratos').wrap('<div class="overflow"/>');
    });

    //------------ filtros
    $('#btn_buscar_filtro').on("click", function () {
        reloadtable('tb_cdps');

        /*$('#tb_contratos').empty();
        $('#tb_contratos').DataTable();

        $('#tb_reg_presupuestal').empty();
        $('#tb_reg_presupuestal').DataTable();

        

        $('#tb_obligaciones').empty();
        $('#tb_obligaciones').DataTable();

        $('#tb_pagos').empty();
        $('#tb_pagos').DataTable();

        $('#id_cdp').val('');*/
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

    //------------------- abrir form liberar saldos cdp
    $('#body_tb_cdps').on('click', '.btn_liberar', function () {
        let id_cdp = $(this).attr('value');
        $('#id_cdp').val(id_cdp);

        $.post(window.urlin + "/terceros/php/historialtercero/frm_liberarsaldos.php", { id_cdp: id_cdp }, function (he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //-------------------- liberar saldos cdp
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
                data: datos + "&oper=add",
                success: function (r) {
                    if (r == '1') {
                        let id = 'tb_cdps';
                        reloadtable(id);
                        mje('Liberacion ejecutada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
            $(this).closest('.modal').modal('hide');
        }
    });
    //---------------------listar liberaciones realizadas cdp
    $('#body_tb_cdps').on('click', '.btn_liberaciones', function () {
        let id_cdp = $(this).attr('value');
        $('#id_cdp').val(id_cdp);

        $.post(window.urlin + "/terceros/php/historialtercero/frm_listar_liberaciones_cdp.php", { id_cdp: id_cdp }, function (he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //----------------anular liberacion cdp
    $('#divFormsReg').on('click', '.btn_anular_liberacion_cdp', function () {
        let id = $(this).attr('value');
        confirmar_del('liberacion_cdp', id);
    });

    $('#divModalConfDel').on("click", "#liberacion_cdp", function () {
        /*var data = $('#frm_reg_articulos').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_articulos.php',
                dataType: 'json',
                data: data + "&oper=add" // esto para llamar a un php editar que si es guardar envia serializado + opcion=add
            }).done(function(r) {*/


        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: window.urlin + '/terceros/php/historialtercero/registrar_liberacion.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function (r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                reloadtable('tb_liberacionescdp');
                reloadtable('tb_cdps');
            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html(r.mensaje);
            }
        }).always(function () { }).fail(function () {
            alert('Ocurrió un error');
        });
    });

    // -------- imprimir liberacion cdp
    $('#divFormsReg').on("click", ".btn_imprimir_liberacion_cdp", function () {
        var id = $(this).attr('value');
        $.post(window.urlin + '/terceros/php/historialtercero/imp_liberacion_cdp.php', {
            id_lib: id,
            id_cdp: $('#id_cdp').val()
        }, function (he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    //------------------- abrir form liberar saldos crp
    $('#body_tb_reg_presupuestal').on('click', '.btn_liberar_crp', function () {
        let id_crp = $(this).attr('value');

        $.post(window.urlin + "/terceros/php/historialtercero/frm_liberarsaldos_crp.php", { id_crp: id_crp }, function (he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });
    //----------------------------- liberar saldos crp
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
                data: datos + "&oper=add",
                success: function (r) {
                    if (r == '1') {
                        let id2 = 'tb_cdps';
                        reloadtable(id2);
                        let id = 'tb_reg_presupuestal';
                        reloadtable(id);
                        mje('Liberacion ejecutada correctamente');
                        //$(this).closest('.modal').modal('hide');
                    } else {
                        mjeError(r);
                    }
                }
            });
            $(this).closest('.modal').modal('hide');
        }
    });
    /*$('#divFormsReg').on("click", "#btn_liquidar_saldos_crp", function () {
        //$('.modal.show').modal('hide'); // este destruye todos los modales
        $(this).closest('.modal').modal('hide');
    });*/

    //----------- listar liberaciones realizadas crp
    $('#body_tb_reg_presupuestal').on('click', '.btn_liberaciones_crp', function () {
        let id_crp = $(this).attr('value');
        $.post(window.urlin + "/terceros/php/historialtercero/frm_listar_liberaciones_crp.php", { id_crp: id_crp }, function (he) {
            $('#divTamModalReg').removeClass('modal-xl');
            $('#divTamModalReg').removeClass('modal-sm');
            $('#divTamModalReg').addClass('modal-lg');
            $('#divModalReg').modal('show');
            $("#divFormsReg").html(he);
        });
    });

    //----------------anular liberacion crp
    $('#divFormsReg').on('click', '.btn_anular_liberacion_crp', function () {
        let id = $(this).attr('value');
        confirmar_del('liberacion_crp', id);
    });

    $('#divModalConfDel').on("click", "#liberacion_crp", function () {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: window.urlin + '/terceros/php/historialtercero/registrar_liberacion_crp.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function (r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                reloadtable('tb_liberacionescrp');
                reloadtable('tb_reg_presupuestal');
                reloadtable('tb_cdps');
            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html(r.mensaje);
            }
        }).always(function () { }).fail(function () {
            alert('Ocurrió un error');
        });
    });

    // -------- imprimir liberacion crp
    $('#divFormsReg').on("click", ".btn_imprimir_liberacion_crp", function () {
        var id = $(this).attr('value');
        $.post(window.urlin + '/terceros/php/historialtercero/imp_liberacion_crp.php', {
            id_lib: id,
            id_crp: $('#id_crp').val()
        }, function (he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });
})(jQuery);