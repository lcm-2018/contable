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
        $('#tableQRsActivoFijo').DataTable({
            language: setIdioma,
            "pageLength": 25,
            "ajax": {
                url: 'datos/listar/activos_fijos_individual.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id_serie' },
                { 'data': 'nombre' },
                { 'data': 'serial' },
                { 'data': 'placa' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableQRsActivoFijo').wrap('<div class="overflow" />');
        $('#tableDepreciaciones').DataTable({
            language: setIdioma,
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_val_depreciacion.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-sm');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            "ajax": {
                url: 'datos/listar/valores_depreciacion.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'nom_mes' },
                { 'data': 'fin_mes' },
                { 'data': 'fec_reg' },
                { 'data': 'total' },
                { 'data': 'botones' },
            ],
            "order": [
                [1, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableDepreciaciones').wrap('<div class="overflow" />');
        $('#tableEntradasActFijosProveedor').DataTable({
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_entradas_actfijos.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id_adq' },
                { 'data': 'objeto' },
                { 'data': 'fecha' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }]
        });
        $('#tableEntradasActFijosProveedor').wrap('<div class="overflow" />');
        let ids_confentradas = $('#idsconfentrada').val();
        $('#tableEntradasActivoFijos').DataTable({
            language: setIdioma,
            "ajax": {
                url: '../listar/datos_confirmar_entradas.php',
                type: 'POST',
                dataType: 'json',
                data: { ids_confentradas: ids_confentradas },
            },
            "columns": [
                { 'data': 'id_prod' },
                { 'data': 'id_api' },
                { 'data': 'bnsv' },
                { 'data': 'cant_act' },
                { 'data': 'precio' },
                { 'data': 'fec_venc' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableEntradasActivoFijos').wrap('<div class="overflow" />');
        let tip_eaf = $('#slctipoEntradaAF').val();
        $('#tableEntradasActFijosDona').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_entra_acfijo_do.php", { tip_eaf: tip_eaf }, function (he) {
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
                url: 'datos/listar/datos_entrada_acfijo_do.php',
                type: 'POST',
                dataType: 'json',
                data: { tip_eaf: tip_eaf },
            },
            "columns": [
                { 'data': 'id_entrada' },
                { 'data': 'tercero' },
                { 'data': 'acta_remi' },
                { 'data': 'fecha' },
                { 'data': 'observa' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableEntradasActFijosDona').wrap('<div class="overflow" />');
        //table Detalles activos fijos
        let tip_eaf_det = $('#id_tipo_entra_acfi_det').val();
        let id_acfi_det = $('#id_acfi_det').val();
        $('#tableDetallesActFijoDO').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("../datos/registrar/form_reg_detalle_acfijo_do.php", { tip_eaf_det: tip_eaf_det }, function (he) {
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
                url: '../datos/listar/datos_entra_detalle_acfijo_do.php',
                type: 'POST',
                dataType: 'json',
                data: { tip_eaf_det: tip_eaf_det, id_acfi_det: id_acfi_det },
            },
            "columns": [
                { 'data': 'id_acfijo' },
                { 'data': 'bien_servicio' },
                { 'data': 'mantenimiento' },
                { 'data': 'depreciable' },
                { 'data': 'marca' },
                { 'data': 'modelo' },
                { 'data': 'val_unit' },
                { 'data': 'descripcion' },
                { 'data': 'cantidad' },
                { 'data': 'serial' },
                { 'data': 'tipo_activo' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableDetallesActFijoDO').wrap('<div class="overflow" />');
        //tabla componentes activo fijo
        let id_ser = $('#id_ser_comp').val();
        $('#tableComponentesActFijo').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_componente_acfijo.php", { id_ser: id_ser }, function (he) {
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
                url: 'datos/listar/componentes_acfijo.php',
                type: 'POST',
                dataType: 'json',
                data: { id_ser: id_ser },
            },
            "columns": [
                { 'data': 'id_acfijo' },
                { 'data': 'bien_servicio' },
                { 'data': 'mantenimiento' },
                { 'data': 'depreciable' },
                { 'data': 'marca' },
                { 'data': 'modelo' },
                { 'data': 'val_unit' },
                { 'data': 'descripcion' },
                { 'data': 'cantidad' },
                { 'data': 'serial' },
                { 'data': 'tipo_activo' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableComponentesActFijo').wrap('<div class="overflow" />');
        //tabla metodos de depreciación de activos fijos
        $('#tableDepreciacionesAcfijo').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_metodo_depreciacion.php", { id_ser: id_ser }, function (he) {
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
                url: 'datos/listar/depreciaciones_acfijo.php',
                type: 'POST',
                dataType: 'json',
                data: { id_ser: id_ser },
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'tdeprecia' },
                { 'data': 'fecha' },
                { 'data': 'vida_util' },
                { 'data': 'val_resid' },
                { 'data': 'capacidad' },
                { 'data': 'observa' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableDepreciacionesAcfijo').wrap('<div class="overflow" />');
        //tabla ubicación y traslado de activos fijos
        $('#tableUbicacionTrasladoAcfijo').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_ubica_traslado.php", { id_ser: id_ser }, function (he) {
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
                url: 'datos/listar/ubica_traslado_acfijo.php',
                type: 'POST',
                dataType: 'json',
                data: { id_ser: id_ser },
            },
            "columns": [
                { 'data': 'id_traslado' },
                { 'data': 'sede' },
                { 'data': 'centro_costo' },
                { 'data': 'fecha' },
                { 'data': 'estado' },
                { 'data': 'resposable' },
                { 'data': 'observaciones' },
                { 'data': 'botones' },
            ],
            "order": [
                [3, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableUbicacionTrasladoAcfijo').wrap('<div class="overflow" />');
        //tabla mantenimiento de activos fijos
        $('#tableMantenimientoAcfijo').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_mantenimiento.php", { id_ser: id_ser }, function (he) {
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
                url: 'datos/listar/mantenimiento_acfijo.php',
                type: 'POST',
                dataType: 'json',
                data: { id_ser: id_ser },
            },
            "columns": [
                { 'data': 'id_mmto' },
                { 'data': 'orden' },
                { 'data': 'fec_ini' },
                { 'data': 'fec_end' },
                { 'data': 'tipo' },
                { 'data': 'concepto' },
                { 'data': 'deterioro' },
                { 'data': 'observaciones' },
                { 'data': 'botones' },
            ],
            "order": [
                [2, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableMantenimientoAcfijo').wrap('<div class="overflow" />');
        //tabla notas de activos fijos
        $('#tableNotasAcfijo').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_reg_notas.php", { id_ser: id_ser }, function (he) {
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
                url: 'datos/listar/notas_acfijo.php',
                type: 'POST',
                dataType: 'json',
                data: { id_ser: id_ser },
            },
            "columns": [
                { 'data': 'id_nota' },
                { 'data': 'descripcion' },
                { 'data': 'fecha_n' },
                { 'data': 'valor' },
                { 'data': 'observacion' },
                { 'data': 'botones' },
            ],
            "order": [
                [2, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1,
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
        });
        $('#tableNotasAcfijo').wrap('<div class="overflow" />');
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
    });
    $('#slctipoEntradaAF').on('change', function () {
        let tipo = $(this).val();
        $('<form action="entradas_activos_fijos.php" method="post"><input type="hidden" name="tipo" value="' + tipo + '" /></form>').appendTo('body').submit();
    });
    $('#tableEntradasActFijosProveedor').on('click', '.detalles', function () {
        let ids = $(this).attr('value');
        $.post("datos/listar/datos_num_entradas.php", { ids: ids }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '.genera_recepcion', function () {
        let ids = $(this).attr('value');
        $.post("datos/registrar/inicia_entrega.php", { ids: ids }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '#btnRegEntraActFijoPr', function () {
        if ($('#numActaRem').val() == '' && $('#numFactura').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar al menos un número de acta o factura');
        } else if ($('#fecActRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha de acta, remisión o factura');
        } else {
            var datos = $('#formRegEntraActFijoPr').serialize();
            var idf = $('#identificador').val();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_header_entrada.php',
                dataType: 'json',
                data: datos,
                success: function (r) {
                    if (r.status == 1) {
                        let ids = idf + '|' + r.msg;
                        $('<form action="datos/registrar/confirma_entradas.php" method="post"><input type="hidden" name="ids" value="' + ids + '" /></form>').appendTo('body').submit();
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r.msg);
                    }

                }
            });
        }
    });
    $("#tableEntradasActivoFijos").on('dblclick', 'tr', function (e) {
        e.preventDefault();
        if ($('#txtNoRemEntrada').val() == '' && $('#txtNoFactEntrada').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un numero de factura o remisión');
            return false;
        }
        var campos = '';
        $(this).children("td").each(function () {
            campos += $(this).text() + '|';
        })
        if (campos != '') {
            $.post("form_reg_entrada.php", { campos: campos }, function (he) {
                $('#divTamModalForms').removeClass('modal-2x');
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(he);
            });
        }
    });
    $("#tableEntradasActivoFijos").on('click', 'tr .recepcionar', function (e) {
        e.preventDefault();
        var datostr = $(this).parent().parent()
        var otro = datostr.parent();
        var campos = '';
        $(otro).children("td").each(function () {
            campos += $(this).text() + '|';
        })
        $.post("form_reg_entrada.php", { campos: campos }, function (he) {
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '#btnMasSerie', function () {
        let serial = $('#txtNoSerie').val();
        if ($('#cantidad').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la cantidad de activos fijos');
            return false;
        }
        if (serial == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un numero de serie');
            return false;
        } else {
            let array = $('#txtSeriales').val().split('|');
            let existe = false;
            for (let index = 0; index < array.length; index++) {
                if (array[index] == serial) {
                    existe = true;
                }
            }
            if (existe) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('El numero de serie ya existe');
                return false;
            }
        }
        let array = $('#txtSeriales').val().split('|');
        if (parseInt($('#cantidad').val()) < parseInt(array.length)) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad de series ingresadas superan la cantidad máxima permitida');
            return false;
        }
        let num = parseInt(array.length) + 1;
        let series = $('#txtSeriales').val();
        let cadena = series + '|' + serial;
        $('#txtSeriales').val(cadena);
        $('#txtNoSerie').val('');
        $('#divSeriales').append('<div class="input-group mb-1 col-3"><input name="serieUp[]" class="form-control form-control-sm altura" value="' + serial + '"><div class="input-group-append"><button class="pt-0 altura btn btn-sm btn-danger delSerialUp " title="Eliminar No. de Serie"><span class="fas fa-times"></span></button></div></div>');
        return false;
    });
    //recibir activo fijo
    $('#divForms').on('click', '.btnRecActFijo', function () {
        let ctd = parseInt($('#cantidad').val());
        let max = parseInt($('#cantidad').attr('max'));
        let array = $('#txtSeriales').val().split('|');
        if ($('#cantidad').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad');
        } else if (ctd > max) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad ingresada supera la cantidad máxima permitida');
        } else if ($('#mantenimiento').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará mantenimiento');
        } else if ($('#slcDepresiacion') == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará depreciación');
        } else if ($('#txtMarca').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una marca');
        } else if ($('#txtModelo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un modelo');
        } else if ($('#slcTipoActivo').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de Activo');
        } else if ($('#txtSeriales').val() == '0' || $('#txtSeriales').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar al menos un numero de serie');
        } else if ((parseInt(array.length) - 1) > max || (parseInt(array.length) - 1) != ctd) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad de series ingresadas debe ser igual a la cantidad máxima permitida');
        } else {
            let penul = $('#comp_cant').length ? '1' : '0';
            let data = $('#formRegActivosFijos').serialize() + '&' + $('#formEncabEntraActFijo').serialize() + '&penul=' + penul;
            $.ajax({
                type: "POST",
                url: "../../registrar/new_activo_fijo.php",
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableEntradasActivoFijos';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Registro realizado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        if ($('#comp_cant').length) {
            console.log('No hay mas datos para registrar');
        }
    });
    $('#divModalForms').on('click', '.delSerial', function () {
        let serial = $(this).val();
        let seriales = $('#txtSeriales').val().split('|');
        let idx = seriales.indexOf(serial);
        let cadena = '0';
        seriales.splice(idx, 1);
        $('#divSeriales').html('');
        seriales.forEach(element => {
            if (element != '' && element != '0') {
                $('#divSeriales').append('<div class="col-md-3 border-bottom border-right" style="color:gray;">' + element + '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle scaled delSerial" value="' + element + '"><i class="fas fa-times fa-lg"></i></button></div>');
                cadena = cadena + '|' + element;
            }
        });
        $('#txtSeriales').val(cadena);
        return false;
    });
    $('#divModalForms').on('click', '.delSerialUp', function () {
        let val = $(this).attr('value');
        let serie = $('#serieUp_' + val).val();
        let seriales = $('#txtSeriales').val().split('|');
        $(this).parent().parent().remove();
        let idx = seriales.indexOf(serie);
        let cadena = '0';
        seriales.splice(idx, 1);
        seriales.forEach(element => {
            if (element != '' && element != '0') {
                cadena = cadena + '|' + element;
            }
        });
        $('#txtSeriales').val(cadena);
        return false;
    });
    $('#divForms').on('click', '#regEntraActFijoDO', function () {
        //$('#').val() == ''
        if ($('#compleTerecero').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tercero');
        } else if ($('#id_tercero_pd').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe selecionar una tercero válido');
        } else if ($('#numActaRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar el número de acta y/o remisión');
        } else if ($('#fecActRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar fecha de acta y/o remisión');
        } else {
            let datos = $('#formRegEntraActFijoDO').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_actfijo_do.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableEntradasActFijosDona';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Registro realizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
    });
    //editar entrada de activo fijo por donación y otros 
    $('#modificarEntradasActFijosDon').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_entra_acfijo_do.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    //actualizar entrada de activo fijo por donación y otros
    $('#divForms').on('click', '#btnUpEntraActFijoDO', function () {
        if ($('#compleTerecero').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tercero');
        } else if ($('#id_tercero_pd').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe selecionar una tercero válido');
        } else if ($('#numActaRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar el número de acta y/o remisión');
        } else if ($('#fecActRem').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar fecha de acta y/o remisión');
        } else {
            let datos = $('#formUpEntraActFijoDO').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_actfijo_do.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableEntradasActFijosDona';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Registro actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
    });
    //eliminar entrada de activo fijo por donación y otros
    $('#modificarEntradasActFijosDon').on('click', '.borrar', function () {
        let id_pd = $(this).attr('value');
        let tip = 'ActFijoDO';
        confdel(id_pd, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelActFijoDO', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_entrada_acfijo_do.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableEntradasActFijosDona';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Registro eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //detalles de activos fijos por donación y otros
    $('#modificarEntradasActFijosDon').on('click', '.detalles', function () {
        let id = $(this).attr('value');
        $('<form action="registrar/detalles_actfijo_do.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    //buscar por lote
    $('#divModalForms').on('input', '#busc_acfijo', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/activos_fijos/datos/listar/activos_fijos.php",
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
                $('#id_acfijo').val(ui.item.id);
                $('#cantidad').focus();
            }
        });
    });
    //buscar activo fijo por lote o placa
    $('#buscarActFijo').on('input', function () {
        let tbus = $('#tipoBusqueda').val();
        if (tbus == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de búsqueda');
        } else {
            $(this).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "datos/listar/activos_fijos_x_serie_placa.php",
                        dataType: "json",
                        type: 'POST',
                        data: { term: request.term, tbus: tbus },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    let datos = tbus + '|' + ui.item.id + '|' + ui.item.tipo + '|' + ui.item.label;
                    $('<form action="componentes_acfijos.php" method="post"><input type="hidden" name="datos" value="' + datos + '" /></form>').appendTo('body').submit();
                }
            });
        }
        return false;
    });
    //buscar activo fijo por lote o placa para mantenimiento
    $('#buscarActFijoMnto').on('input', function () {
        let tbus = $('#tipoBusqueda').val();
        if (tbus == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de búsqueda');
        } else {
            $(this).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "datos/listar/activos_fijos_x_serie_placa.php",
                        dataType: "json",
                        type: 'POST',
                        data: { term: request.term, tbus: tbus },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    let datos = tbus + '|' + ui.item.id + '|' + ui.item.tipo + '|' + ui.item.label;
                    $('<form action="mantenimiento_acfijos.php" method="post"><input type="hidden" name="datos" value="' + datos + '" /></form>').appendTo('body').submit();
                }
            });
        }
        return false;
    });
    //buscar activo fijo por lote o placa
    $('#divModalForms').on('input', '#busAcFijoXSerPla', function () {
        let tbus = $('#tipBusq').val();
        if (tbus == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de búsqueda');
        } else {
            $(this).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "datos/listar/activos_fijos_x_serie_placa.php",
                        dataType: "json",
                        type: 'POST',
                        data: { term: request.term, tbus: tbus },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    $('#id_serpla').val(ui.item.id);
                }
            });
        }
        return false;
    });
    //buscar tercero por nombre
    $('#divModalForms').on('input', '#txtBuscaTerceroResp', function () {
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
                $('#numTercerResp').val(ui.item.id);
            }
        });
    });
    //registrar detalle de entrada de activo fijo por donación y otros
    $('#divForms').on('click', '#btnRegDetActFijoDO', function () {
        let ctd = parseInt($('#cantidad').val());
        let max = parseInt($('#cantidad').attr('max'));
        let array = $('#txtSeriales').val().split('|');
        let aprobar = 1;
        if ($('#busc_acfijo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo');
        } else if ($('#id_acfijo').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo válido');
        } else if ($('#cantidad').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad');
        } else if (ctd > max) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad ingresada supera la cantidad máxima permitida');
        } else if ($('#mantenimiento').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará mantenimiento');
        } else if ($('#slcDepresiacion').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará depreciación');
        } else if ($('#txtMarca').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una marca');
        } else if ($('#txtModelo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un modelo');
        } else if ($('#slcTipoActivo').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de Activo');
        } else if ($('#txtSeriales').val() == '0' || $('#txtSeriales').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar al menos un numero de serie');
        } else if ((parseInt(array.length) - 1) > max || (parseInt(array.length) - 1) != ctd) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad de series ingresadas debe ser igual a la cantidad máxima permitida');
        } else if ($('#numValUnita').val() == '' || parseInt($('#numValUnita').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El valor unitario debe ser mayor o igual a cero');
        } else {
            $('input[name^=serieUp]').each(function () {
                $(this).removeClass('border-danger');
                if ($(this).val() == '') {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('border-danger');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('El campo serie no puede estar vacío');
                }
                if (aprobar == 0) {
                    return false;
                }
            });
            if (aprobar == 1) {
                let data = $('#formRegDetActFijoDO').serialize() + '&' + $('#formDatosActivoFijoDet').serialize();
                $.ajax({
                    type: "POST",
                    url: "../registrar/new_detalle_activo_fijo.php",
                    data: data,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableDetallesActFijoDO';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Detalle Registrado correctamente');
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    // modificar detalle de entrada de activo fijo por donación y otros
    $('#modificarDetalleActFijDO').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("../datos/actualizar/form_up_detalle_acfijo_do.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    $('#divForms').on('click', '#btnUpDetActFijoDO', function () {
        let ctd = parseInt($('#cantidad').val());
        let max = parseInt($('#cantidad').attr('max'));
        let array = $('#txtSeriales').val().split('|');
        if ($('#busc_acfijo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo');
        } else if ($('#id_acfijo').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo válido');
        } else if ($('#cantidad').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad');
        } else if (ctd > max) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad ingresada supera la cantidad máxima permitida');
        } else if ($('#mantenimiento').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará mantenimiento');
        } else if ($('#slcDepresiacion').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará depreciación');
        } else if ($('#txtMarca').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una marca');
        } else if ($('#txtModelo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un modelo');
        } else if ($('#slcTipoActivo').val() == '0') { //
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de Activo');
        } else if ($('#txtSeriales').val() == '0' || $('#txtSeriales').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar al menos un numero de serie');
        } else if ((parseInt(array.length) - 1) > max || (parseInt(array.length) - 1) != ctd) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad de series ingresadas debe ser igual a la cantidad máxima permitida');
        } else if ($('#numValUnita').val() == '' || parseInt($('#numValUnita').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El valor unitario debe ser mayor o igual a cero');
        } else {
            let aprobar = 1;
            $('input[name^=serieUp]').each(function () {
                $(this).removeClass('border-danger');
                if ($(this).val() == '') {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('border-danger');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('El campo serie no puede estar vacío');
                }
                if (aprobar == 0) {
                    return false;
                }
            });
            if (aprobar == 1) {
                let data = $('#formUpDetActFijoDO').serialize();
                $.ajax({
                    type: "POST",
                    url: "../actualizar/up_detalle_activo_fijo.php",
                    data: data,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableDetallesActFijoDO';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Detalle actualizado correctamente');
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    // eliminar detalle de entrada de activo fijo por donación y otros
    $('#modificarDetalleActFijDO').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let btn = 'DetActFijoDO';
        confdel(id, btn);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelDetActFijoDO', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: '../eliminar/del_detalle_acfijo_do.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableDetallesActFijoDO';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Registro eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#btnCerrarDOActFijo').on('click', function () {
        var table = $('#tableDetallesActFijoDO').DataTable();
        let filas = table.rows().count();
        if (filas <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Imposible cerrar, no se ha agregado ningun elemento');
            return false;
        }
        let id_do = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: '../actualizar/mod_estado_actfijo_do.php',
            data: { id_do: id_do },
            success: function (r) {
                if (r == '1') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Se ha cerrado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //registrar componentes de activo fijo
    $('#divForms').on('click', '#btnRegComponenteActFijo', function () {
        let ctd = parseInt($('#cantidad').val());
        let max = 1;
        let array = $('#txtSeriales').val().split('|');
        let aprobar = 1;
        if ($('#busc_acfijo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo');
        } else if ($('#id_acfijo').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo válido');
        } else if ($('#cantidad').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad');
        } else if (ctd > max) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad ingresada supera la cantidad máxima permitida');
        } else if ($('#mantenimiento').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará mantenimiento');
        } else if ($('#slcDepresiacion').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará depreciación');
        } else if ($('#txtMarca').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una marca');
        } else if ($('#txtModelo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un modelo');
        } else if ($('#slcTipoActivo').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de Activo');
        } else if ($('#txtSeriales').val() == '0' || $('#txtSeriales').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar al menos un numero de serie');
        } else if ((parseInt(array.length) - 1) > max || (parseInt(array.length) - 1) != ctd) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad de series ingresadas debe ser igual a la cantidad máxima permitida');
        } else if ($('#numValUnita').val() == '' || parseInt($('#numValUnita').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El valor unitario debe ser mayor o igual a cero');
        } else {
            $('input[name^=serieUp]').each(function () {
                $(this).removeClass('border-danger');
                if ($(this).val() == '') {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('border-danger');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('El campo serie no puede estar vacío');
                }
                if (aprobar == 0) {
                    return false;
                }
            });
            if (aprobar == 1) {
                let data = $('#formRegComponenteAcFijo').serialize();
                $.ajax({
                    type: "POST",
                    url: "registrar/new_componente_acfijo.php",
                    data: data,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableComponentesActFijo';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Componente Registrado correctamente');
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    //Agregar componentes de activo fijo
    $('#divForms').on('click', '#btnAddComponenteAcFijo', function () {
        if ($('#busAcFijoXSerPla').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo');
        } else if ($('#busAcFijoXSerPla').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo válido');
        } else if (parseInt($('#id_serpla').val()) == parseInt($('#id_ser_comp').val())) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El componente seleccionado es el mismo activo fijo');
        } else {
            let data = $('#id_serpla').val();
            let id_sc = $('#id_ser_comp').val();
            $.ajax({
                type: "POST",
                url: "registrar/add_componente_acfijo.php",
                data: { data: data, id_sc: id_sc },
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableComponentesActFijo';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Componente Agregado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }

    });
    //actualizar componentes de activo fijo
    $('#modificarComponentesActFijo').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_componente_acfijo.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //actualizar componentes de activo fijo
    $('#divForms').on('click', '#btnUpComponenteAcFijo', function () {
        let ctd = parseInt($('#cantidad').val());
        let max = 1;
        let array = $('#txtSeriales').val().split('|');
        if ($('#busc_acfijo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo');
        } else if ($('#id_acfijo').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un activo fijo válido');
        } else if ($('#cantidad').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad');
        } else if (ctd > max) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad ingresada supera la cantidad máxima permitida');
        } else if ($('#mantenimiento').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará mantenimiento');
        } else if ($('#slcDepresiacion').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe indicar si al activo fijo se le realizará depreciación');
        } else if ($('#txtMarca').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una marca');
        } else if ($('#txtModelo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un modelo');
        } else if ($('#slcTipoActivo').val() == '0') { //
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de Activo');
        } else if ($('#txtSeriales').val() == '0' || $('#txtSeriales').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar al menos un numero de serie');
        } else if ((parseInt(array.length) - 1) > max || (parseInt(array.length) - 1) != ctd) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La cantidad de series ingresadas debe ser igual a la cantidad máxima permitida');
        } else if ($('#numValUnita').val() == '' || parseInt($('#numValUnita').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El valor unitario debe ser mayor o igual a cero');
        } else {
            let aprobar = 1;
            $('input[name^=serieUp]').each(function () {
                $(this).removeClass('border-danger');
                if ($(this).val() == '') {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('border-danger');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('El campo serie no puede estar vacío');
                }
                if (aprobar == 0) {
                    return false;
                }
            });
            if (aprobar == 1) {
                let data = $('#formUpComponenteActFijo').serialize();
                $.ajax({
                    type: "POST",
                    url: "actualizar/up_componente_acfijo.php",
                    data: data,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableComponentesActFijo';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Componenete actualizado correctamente');
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    //eliminar componentes de activo fijo
    $('#modificarComponentesActFijo').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let btn = 'CompAcFijo';
        confdel(id, btn);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelCompAcFijo', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_detalle_acfijo_do.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableComponentesActFijo';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Componente eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //quitar componentes de activo fijo
    $('#modificarComponentesActFijo').on('click', '.eliminar', function () {
        let id = $(this).attr('value');
        let btn = 'ComActFijo';
        confdel(id, btn);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelComActFijo', function () {
        let data = $(this).attr('value');
        let id_sc = 0;
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'registrar/add_componente_acfijo.php',
            data: { data: data, id_sc: id_sc },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableComponentesActFijo';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Componente liberado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //registrar depreciación de activo fijo
    $('#divForms').on('click', '#btnRegMetDepreciaActFijo', function () {
        if ($('#metodoDeprecia').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un método de depreciación');
        } else if ($('#fecIniDeprecia').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una fecha de inicio de depreciación');
        } else if ($('#numMesesDeprecia').val() == '' || parseInt($('#numMesesDeprecia').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad de meses de depreciación mayor a cero');
        } else if ($('#numValResidual').val() == '' || parseInt($('#numValResidual').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un valor residual mayor a cero');
        } else if ($('#numCapacProd').val() == '' || parseInt($('#numCapacProd').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una capacidad de producción mayor o igual a cero');
        } else {
            let data = $('#formRegMetDepreciaAcFijo').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_depreciacion_acfijo.php',
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableDepreciacionesAcfijo';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Método de depreciación registrado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });

    //actualizar depreciación de activo fijo
    $('#modificarDepreciacionAcfijo').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_metodo_depreciacion.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    $('#divForms').on('click', '#btnUpMetDepreciaActFijo', function () {
        if ($('#fecIniDeprecia').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una fecha de inicio de depreciación');
        } else if ($('#numMesesDeprecia').val() == '' || parseInt($('#numMesesDeprecia').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una cantidad de meses de depreciación mayor a cero');
        } else if ($('#numValResidual').val() == '' || parseInt($('#numValResidual').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un valor residual mayor a cero');
        } else if ($('#numCapacProd').val() == '' || parseInt($('#numCapacProd').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una capacidad de producción mayor o igual a cero');
        } else {
            let data = $('#formRegMetDepreciaAcFijo').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_depreciacion_acfijo.php',
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableDepreciacionesAcfijo';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Método de depreciación actualizado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //eliminar depreciación de activo fijo
    $('#modificarDepreciacionAcfijo').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let btn = 'DepreciaActFijo';
        confdel(id, btn);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelDepreciaActFijo', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_depreciacion.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableDepreciacionesAcfijo';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Depreciación eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //mostrar centros de costo 
    $('#divForms').on('change', '#slcSedeUbTr', function () {
        let id = $(this).val();
        $.ajax({
            type: 'POST',
            url: 'datos/listar/centros_costo.php',
            data: { id: id },
            success: function (r) {
                $('#slcCentroCosto').html(r);
            }
        });
        return false;
    });
    //registrar ubicación o traslado a centro de costo
    $('#divForms').on('click', '#btnRegUbicaTrasladoActFijo', function () {
        if ($('#slcSedeUbTr').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar una sede');
        } else if ($('#slcCentroCosto').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un centro de costo');
        } else if ($('#fecUbicTrasl').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una fecha de ubicación o traslado');
        } else if ($('#slcEstadoAcFijo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un estado para el activo fijo');
        } else if ($('#txtBuscaTerceroResp').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un responsable');
        } else if ($('#numTercerResp').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un responsable válido');
        } else {
            let data = $('#formRegUbicaTRasladAcFijo').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_ubicacion_traslado_acfijo.php',
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableUbicacionTrasladoAcfijo';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Ubicación o traslado registrado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    //Actualizar ubicación o traslado a centro de costo
    $('#modificarUbicacionTrasladoAcfijo').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_ubica_traslado.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    //actualizar ubicación o traslado a centro de costo
    $('#divForms').on('click', '#btnUpUbicaTrasladoActFijo', function () {
        if ($('#slcCentroCosto').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un centro de costo');
        } else if ($('#fecUbicTrasl').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar una fecha de ubicación o traslado');
        } else if ($('#txtBuscaTerceroResp').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un responsable');
        } else if ($('#numTercerResp').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un responsable válido');
        } else {
            let data = $('#formUpUbicaTRasladAcFijo').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_ubicacion_traslado_acfijo.php',
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableUbicacionTrasladoAcfijo';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Ubicación o traslado actualizado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    //eliminar ubicación o traslado a centro de costo
    $('#modificarUbicacionTrasladoAcfijo').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let btn = 'UbicaTrActFijo';
        confdel(id, btn);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelUbicaTrActFijo', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_ubicatraslado.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableUbicacionTrasladoAcfijo';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Ubicación y/o Traslado eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //registrar mantenimiento a activo fijo
    $('#divForms').on('click', '#btnRegMmtoActFijo', function () {
        if ($('#txtNoOrdenMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de orden');
        } else if ($('#fecIniciaMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar fecha de inicio');
        } else if ($('#fecFinMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar fecha de retorno');
        } else if ($('#fecIniciaMmto').val() > $('#fecFinMmto').val()) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La fecha de inicio no puede ser mayor a la fecha de retorno');
        } else if ($('#slcTipoMmto').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de mantenimiento');
        } else if ($('#txtConcptoMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un concepto');
        } else if ($('#numValDeterioro').val() == '' || parseInt($('#numValDeterioro').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('el valor de deterioro debe ser mayor a cero');
        } else {
            let data = $('#formRegMantenimientoAcFijo').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_mantenimiento_acfijo.php',
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id_T = 'tableMantenimientoAcfijo';
                        reloadtable(id_T);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Mantenimiento registrado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //actualizar mantenimiento a activo fijo
    $('#modificarMantenimientoAcfijo').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_mantenimiento.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '#btnUpMmtoActFijo', function () {
        if ($('#txtNoOrdenMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de orden');
        } else if ($('#fecIniciaMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar fecha de inicio');
        } else if ($('#fecFinMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar fecha de retorno');
        } else if ($('#fecIniciaMmto').val() > $('#fecFinMmto').val()) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La fecha de inicio no puede ser mayor a la fecha de retorno');
        } else if ($('#slcTipoMmto').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de mantenimiento');
        } else if ($('#txtConcptoMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un concepto');
        } else if ($('#numValDeterioro').val() == '' || parseInt($('#numValDeterioro').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('el valor de deterioro debe ser mayor a cero');
        } else {
            let data = $('#formUpMantenimientoAcFijo').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_mantenimiento_acfijo.php',
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id_T = 'tableMantenimientoAcfijo';
                        reloadtable(id_T);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Mantenimiento actualizado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //eliminar mantenimiento a activo fijo
    $('#modificarMantenimientoAcfijo').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let btn = 'MmtoActFijo';
        confdel(id, btn);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelMmtoActFijo', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_mantenimiento.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableMantenimientoAcfijo';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Mantenimiento eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //registrar hoja de vida de activo fijo
    $('#modificarMantenimientoAcfijo').on('click', '.dhvida', function () {
        let id = $(this).attr('value');
        let id_serie = $('#id_ser_pla').val();
        let data = $('#formDataAcFijo').serialize() + '&id_serie=' + id_serie + '&id=' + id;
        $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //ver hoja de vida de activo fijo registrada
    $('#modificarMantenimientoAcfijo').on('click', '.vhvida', function () {
        let id = $(this).attr('value');
        let id_serie = $('#id_ser_pla').val();
        let data = $('#formDataAcFijo').serialize() + '&id_serie=' + id_serie + '&id=' + id;
        $.post("datos/actualizar/ver_hoja_vida.php", data, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //registrar notas de activo fijo
    $('#divForms').on('click', '#btnRegNotaActFijo', function () {
        if ($('#slcNota').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tipo de nota');
        } else if ($('#fecNota').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar fecha de nota');
        } else if ($('#numValNota').val() == '' || parseInt($('#numValNota').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El valor de la nota debe ser mayor a cero');
        } else {
            let data = $('#formRegNotasAcFijo').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_nota_acfijo.php',
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id_T = 'tableNotasAcfijo';
                        reloadtable(id_T);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Nota registrada correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //actualizar notas de activo fijo
    $('#modificarNotasAcfijo').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_notas.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '#btnUpNotaActFijo', function () {
        if ($('#fecNota').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar fecha de nota');
        } else if ($('#numValNota').val() == '' || parseInt($('#numValNota').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El valor de la nota debe ser mayor a cero');
        } else {
            let data = $('#formUpNotasAcFijo').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_nota_acfijo.php',
                data: data,
                success: function (r) {
                    if (r == '1') {
                        let id_T = 'tableNotasAcfijo';
                        reloadtable(id_T);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Nota registrada correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //eliminar nota de activo fijo
    $('#modificarNotasAcfijo').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let btn = 'NotaActFijo';
        confdel(id, btn);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelNotaActFijo', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_nota.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableNotasAcfijo';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Nota eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //generar código QR
    $('#modificarQRActivoFijo').on('click', '.codeqr', function () {
        let id = $(this).attr('value');
        $.post("datos/listar/codigo_qr.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //imprimir código QR
    $('#divModalForms').on('click', '#btnPrintQR', function () {
        //imprimir codigo QR
        function imprSelec(nombre) {
            var div = document.getElementById(nombre);
            var ventimp = window.open(' ', 'Imprime QR');
            ventimp.document.write('<html><head><title>' + document.title + '</title>');
            ventimp.document.write('<link rel="stylesheet" href="' + window.urlin + '/css/styles.css">');
            ventimp.document.write('<link rel="stylesheet" href="' + window.urlin + '/css/estilos.css">');
            ventimp.document.write('<link rel="stylesheet" href="' + window.urlin + '/css/font-awesome.min.css">');
            ventimp.document.write('<script type="text/javascript" src="' + window.urlin + '/js/all.min.js"></script>');
            ventimp.document.write('</head><body class="bg-light">');
            ventimp.document.write(div.innerHTML);
            ventimp.document.write('</body></html>');
            ventimp.document.close();
            ventimp.focus();
            ventimp.onload = function () {
                ventimp.print();
                ventimp.close();
            };
        }
        $('#divModalForms .collapse').addClass('show');
        imprSelec('PrintQR');
        $('#divModalForms').modal('hide');
    });
    //actualizar hv equipo general
    $('#divModalForms').on('click', '#btnUpHVEquipoGral', function () {
        if (parseInt($('#valEstado').val()) == 0) {
            if ($('#upImageAF').val() == '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Debe elegir una imagen!');
                return false;
            } else {
                let archivo = $('#upImageAF').val();
                let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
                if (!(ext == '.jpg' || ext == '.jpeg' || ext == '.png')) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('¡Solo se permite imagenes con formato .jpg, .jpeg y .png!');
                    return false;
                } else if ($('#upImageAF')[0].files[0].size > 1048576) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('¡Documento debe tener un tamaño menor a 1Mb!');
                    return false;
                }
            }
        } else {
            if ($('#upImageAF').val() != '') {
                let archivo = $('#upImageAF').val();
                let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
                if (!(ext == '.jpg' || ext == '.jpeg' || ext == '.png')) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('¡Solo se permite imagenes con formato .jpg, .jpeg y .png!');
                    return false;
                } else if ($('#upImageAF')[0].files[0].size > 1048576) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('¡Documento debe tener un tamaño menor a 1Mb!');
                    return false;
                }

            }
        }
        if ($('#txtLoteAF').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar lote del Equipo');
        } else if ($('#fecFabricacion').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese la fecha de fabricación');
        } else if ($('#txtRegINVIMA').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese el registro INVIMA');
        } else if ($('#txtFabricante').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese el fabricante');
        } else if ($('#txtOrigen').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese el  lugar de origen');
        } else if ($('#txtRepre').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese el representante');
        } else if ($('#txtDirRepre').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese la dirección del representante');
        } else if ($('#txtTelRepre').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese el teléfono del representante');
        } else {
            let datos = new FormData();
            datos.append('ruta_del', $('#ruta_del').val());
            datos.append('valEstado', $('#valEstado').val());
            datos.append('id_serial_hv', $('#id_serial_hv').val());
            datos.append('txtLoteAF', $('#txtLoteAF').val());
            datos.append('fecFabricacion', $('#fecFabricacion').val());
            datos.append('txtRegINVIMA', $('#txtRegINVIMA').val());
            datos.append('txtFabricante', $('#txtFabricante').val());
            datos.append('txtOrigen', $('#txtOrigen').val());
            datos.append('txtRepre', $('#txtRepre').val());
            datos.append('txtDirRepre', $('#txtDirRepre').val());
            datos.append('txtTelRepre', $('#txtTelRepre').val());
            datos.append('upImageAF', $('#upImageAF')[0].files[0]);
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_hvgral_acfijo.php',
                contentType: false,
                data: datos,
                processData: false,
                cache: false,
                success: function (r) {
                    if (r == 1) {
                        let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                        $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                            $('#divTamModalForms').removeClass('modal-xl');
                            $('#divTamModalForms').removeClass('modal-lg');
                            $('#divTamModalForms').addClass('modal-xl');
                            $('#divModalForms').modal('show');
                            $("#divForms").html(he);
                            $('#collapsemodDos').addClass('show');
                        });
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Datos de equipo actualizados correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }

    });
    $('#divModalForms').on('input', '.valueMin', function () {
        let id_input = $(this).attr('id');
        this.value = Math.min(this.value, this.parentNode.childNodes[5].value - 1);
        var value = (100 / (parseInt(this.max) - parseInt(this.min))) * parseInt(this.value) - (100 / (parseInt(this.max) - parseInt(this.min))) * parseInt(this.min);
        var children = this.parentNode.childNodes[1].childNodes;
        children[1].style.width = value + '%';
        children[5].style.left = value + '%';
        children[7].style.left = value + '%';
        $('#' + id_input + 'Min').html(this.value);
        $('#' + id_input + 'MinInput').val(this.value);
    });
    $('#divModalForms').on('input', '.valueMax', function () {
        let id_input = $(this).attr('id');
        this.value = Math.max(this.value, this.parentNode.childNodes[3].value - (-1));
        var value = (100 / (parseInt(this.max) - parseInt(this.min))) * parseInt(this.value) - (100 / (parseInt(this.max) - parseInt(this.min))) * parseInt(this.min);
        var children = this.parentNode.childNodes[1].childNodes;
        children[3].style.width = (100 - value) + '%';
        children[5].style.right = (100 - value) + '%';
        children[9].style.left = value + '%';
        $('#' + id_input + 'Max').html(this.value);
        $('#' + id_input + 'MaxInput').val(this.value);
    });
    //actualizar rangos de valores de los campos de la tabla de datos de equipo
    $('#divModalForms').on('click', '#btnUpRegTecFmto', function () {
        let rangos = $('#formRangosVFPCT').serialize() + '&id_serie=' + $('#id_serial_hv').val();
        $.ajax({
            type: 'POST',
            url: 'actualizar/up_hvreg_tecnico.php',
            data: rangos,
            success: function (r) {
                if (r == 1) {
                    let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                    $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#collapesemodCuatro').addClass('show');
                    });
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Registro técnico de funcionamiento actualizados correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //actualizar apoyo tecnico de la tabla de datos de equipo
    $('#divModalForms').on('click', '#btnUpApoyoTecnico', function () {
        let apoyos = $('#formApoyoTecnico').serialize() + '&id_serie=' + $('#id_serial_hv').val();
        $.ajax({
            type: 'POST',
            url: 'actualizar/up_hvapoyo_tecnico.php',
            data: apoyos,
            success: function (r) {
                if (r == 1) {
                    let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                    $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#collapsemodSeis').addClass('show');
                    });
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Registro técnico de funcionamiento actualizados correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //actualizar registro de planos del equipo
    $('#divModalForms').on('click', '#btnUpHVPlanos', function () {
        if (parseInt($('#id_instal').val()) == 0) {
            if (!($('input[name="instalPlano"]:checked')).length) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe elegir una opción para el plano de <b>Instalación</b>');
                return false;
            } else {
                if (parseInt($('input[name="instalPlano"]:checked').val()) == 1) {
                    if ($('#fileInstal').val() == '') {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Debe subir una archivo en PDF de el(los) plano(s) de <b>Instalación</b>');
                        return false;
                    } else {
                        let archivo = $('#fileInstal').val();
                        let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
                        if (ext != '.pdf') {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Solo se permite documentos .pdf para el plano de <b>Instalación</b>!');
                            return false;
                        } else if ($('#fileInstal')[0].files[0].size > 1048576) {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Documento del plano de <b>Instalación</b> debe tener un tamaño menor a 1Mb!');
                            return false;
                        }
                    }
                }
            }
            if (!($('input[name="partePlano"]:checked')).length) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe elegir una opción para el plano de <b>Partes</b>');
                return false;
            } else {
                if (parseInt($('input[name="partePlano"]:checked').val()) == 1) {
                    if ($('#fileParts').val() == '') {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Debe subir una archivo en PDF de el(los) plano(s) de <b>Partes</b>');
                        return false;
                    } else {
                        let archivo = $('#fileParts').val();
                        let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
                        if (ext != '.pdf') {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Solo se permite documentos .pdf para el plano de <b>Partes</b>!');
                            return false;
                        } else if ($('#fileParts')[0].files[0].size > 1048576) {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Documento del plano de <b>Partes</b> debe tener un tamaño menor a 1Mb!');
                            return false;
                        }
                    }
                }
            }
        } else {
            if (parseInt($('input[name="instalPlano"]:checked').val()) == 1) {
                if ($('#rai').length) {
                    if ($('#fileInstal').val() != '') {
                        let archivo = $('#fileInstal').val();
                        let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
                        if (ext != '.pdf') {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Solo se permite documentos .pdf para el plano de <b>Instalación</b>!');
                            return false;
                        } else if ($('#fileInstal')[0].files[0].size > 1048576) {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Documento del plano de <b>Instalación</b> debe tener un tamaño menor a 1Mb!');
                            return false;
                        }
                    }
                } else {
                    if ($('#fileInstal').val() == '') {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Debe subir una archivo en PDF de el(los) plano(s) de <b>Instalación</b>');
                        return false;
                    } else {
                        let archivo = $('#fileInstal').val();
                        let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
                        if (ext != '.pdf') {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Solo se permite documentos .pdf para el plano de <b>Instalación</b>!');
                            return false;
                        } else if ($('#fileInstal')[0].files[0].size > 1048576) {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Documento del plano de <b>Instalación</b> debe tener un tamaño menor a 1Mb!');
                            return false;
                        }
                    }

                }
            }
            if (parseInt($('input[name="partePlano"]:checked').val()) == 1) {
                if ($('#rap').length) {
                    if ($('#fileParts').val() != '') {
                        let archivo = $('#fileParts').val();
                        let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
                        if (ext != '.pdf') {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Solo se permite documentos .pdf para el plano de <b>Partes</b>!');
                            return false;
                        } else if ($('#fileParts')[0].files[0].size > 1048576) {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Documento del plano de <b>Partes</b> debe tener un tamaño menor a 1Mb!');
                            return false;
                        }
                    }
                } else {
                    if ($('#fileParts').val() == '') {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Debe subir una archivo en PDF de el(los) plano(s) de <b>Partes</b>');
                        return false;
                    } else {
                        let archivo = $('#fileParts').val();
                        let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
                        if (ext != '.pdf') {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Solo se permite documentos .pdf para el plano de <b>Partes</b>!');
                            return false;
                        } else if ($('#fileParts')[0].files[0].size > 1048576) {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('¡Documento del plano de <b>Partes</b> debe tener un tamaño menor a 1Mb!');
                            return false;
                        }
                    }

                }
            }
        }
        let datos = new FormData();
        datos.append('id_instal', $('#id_instal').val());
        datos.append('id_partes', $('#id_partes').val());
        datos.append('id_serie', $('#id_serial_hv').val());
        datos.append('instalPlano', $('input[name="instalPlano"]:checked').val());
        datos.append('partePlano', $('input[name="partePlano"]:checked').val());
        datos.append('fileInstal', $('#fileInstal')[0].files[0]);
        datos.append('fileParts', $('#fileParts')[0].files[0]);
        datos.append('rai', $('#rai').length ? $('#rai').val() : '');
        datos.append('rap', $('#rap').length ? $('#rap').val() : '');
        $.ajax({
            type: 'POST',
            url: 'actualizar/up_hv_planos.php',
            contentType: false,
            data: datos,
            processData: false,
            cache: false,
            success: function (r) {
                if (r == 1) {
                    let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                    $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#collapsemodSiete').addClass('show');
                    });
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Registro de planos actualizados correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //actualizar registro de manuales del equipo
    $('#divModalForms').on('click', '#btnUpHVManuales', function () {
        function validaDataFile(manual, file) {
            let archivo = $('#' + file).val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext != '.pdf') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Solo se permite documentos .pdf para el manual de <b>' + manual + '</b>!');
                return false;
            } else if ($('#' + file)[0].files[0].size > 1048576) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Documento del manual de <b>' + manual + '</b> debe tener un tamaño menor a 1Mb!');
                return false;
            }
            return true;
        }

        function validaFile(manual, file) {
            if ($('#' + file).val() == '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe subir una archivo en PDF de el(los) manual(es) de <b>' + manual + '</b>');
                return false;
            } else {
                if (!(validaDataFile(manual, file))) {
                    return false;
                }
            }
            return true;
        }

        function validacion(radio, manual, file) {
            if (!($('input[name="' + radio + '"]:checked')).length) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe elegir una opción para el manual de <b>' + manual + '</b>');
                return false;
            } else {
                if (parseInt($('input[name="' + radio + '"]:checked').val()) == 1) {
                    if (!(validaFile(manual, file))) {
                        return false;
                    }
                }
            }
            return true;
        }

        function validacionUpData(radio, manual, file, ra) {
            if (parseInt($('input[name="' + radio + '"]:checked').val()) == 1) {
                if ($('#' + ra).length) {
                    if ($('#' + file).val() != '') {
                        if (!(validaDataFile(manual, file))) {
                            return false;
                        }
                    }
                } else {
                    if (!(validaFile(manual, file))) {
                        return false;
                    }

                }
            }
            return true;
        }
        if (parseInt($('#id_mSerTec').val()) == 0) {
            if (!(validacion('servTecManual', 'Servicio Técnico', 'filemst'))) {
                return false;
            }
            if (!(validacion('userManual', 'Usuario', 'filemu'))) {
                return false;
            }
            if (!(validacion('guiaFastManual', 'Guía de manejo rápido', 'filemf'))) {
                return false;
            }
        } else {
            if (!(validacionUpData('servTecManual', 'Servicio Técnico', 'filemst', 'ramst'))) {
                return false;
            }
            if (!(validacionUpData('userManual', 'Usuario', 'filemu', 'ramu'))) {
                return false;
            }
            if (!(validacionUpData('guiaFastManual', 'Guía de manejo rápido', 'filemf', 'ramf'))) {
                return false;
            }
        }
        let datos = new FormData();
        datos.append('id_mSerTec', $('#id_mSerTec').val());
        datos.append('id_mUser', $('#id_mUser').val());
        datos.append('id_mGFast', $('#id_mGFast').val());
        datos.append('id_serie', $('#id_serial_hv').val());
        datos.append('servTecManual', $('input[name="servTecManual"]:checked').val());
        datos.append('userManual', $('input[name="userManual"]:checked').val());
        datos.append('guiaFastManual', $('input[name="guiaFastManual"]:checked').val());
        datos.append('filemst', $('#filemst')[0].files[0]);
        datos.append('filemu', $('#filemu')[0].files[0]);
        datos.append('filemf', $('#filemf')[0].files[0]);
        datos.append('ramst', $('#ramst').length ? $('#ramst').val() : '');
        datos.append('ramu', $('#ramu').length ? $('#ramu').val() : '');
        datos.append('ramf', $('#ramf').length ? $('#ramf').val() : '');
        $.ajax({
            type: 'POST',
            url: 'actualizar/up_hv_manuales.php',
            contentType: false,
            data: datos,
            processData: false,
            cache: false,
            success: function (r) {
                if (r == 1) {
                    let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                    $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#collapsemodOcho').addClass('show');
                    });
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Registro de manuales actualizados correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });

    //descargar manuales de funcionamiento
    $('#divModalForms').on('click', '.descargaManual', function () {
        let id = $(this).attr('value');
        if (id == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se puede descargar el manual, no se ha cargado ningun archivo');
            return false;
        } else {
            $('<form action="datos/descargar/manuales.php" method="post"><input type="hidden" name="id_manual" value="' + id + '" /></form>').appendTo('body').submit();

        }
    });
    //Actualizar recomendaciones de funcionamiento 
    $('#divModalForms').on('click', '#btnUpHVReCons', function () {
        if ($('#txtaReCons').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar las recomendaciones y condiciones de funcionamiento');
        } else {
            let recon = $('#txtaReCons').val();
            let id_serie = $('#id_serial_hv').val();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_hv_recomendaciones.php',
                data: {
                    id_serie: id_serie,
                    recon: recon
                },
                success: function (r) {
                    if (r == 1) {
                        let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                        $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                            $('#divTamModalForms').removeClass('modal-xl');
                            $('#divTamModalForms').removeClass('modal-lg');
                            $('#divTamModalForms').addClass('modal-xl');
                            $('#divModalForms').modal('show');
                            $("#divForms").html(he);
                            $('#collapsemodNueve').addClass('show');
                        });
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Registro de recomendaciones actualizado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Actualizar estado general de activo fijo 
    $('#divModalForms').on('click', '#btnUpHVEstGral', function () {
        if (!($('input[name ="estadoGral"]:checked').length)) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe seleccior una estado general del activo fijo');
        } else if (parseInt($('#id_traslado').val()) == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Primero se debe registrar un traslado para el activo fijo');
        } else {
            let data = $('#formEstadoGral').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_hv_estadogral.php',
                data: data,
                success: function (r) {
                    if (r == 1) {
                        let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                        $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                            $('#divTamModalForms').removeClass('modal-xl');
                            $('#divTamModalForms').removeClass('modal-lg');
                            $('#divTamModalForms').addClass('modal-xl');
                            $('#divModalForms').modal('show');
                            $("#divForms").html(he);
                            $('#collapsemodDiez').addClass('show');
                        });
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Registro de estado general actualizado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //actualizar funcionamiento de activo fijo
    $('#divModalForms').on('click', '#btnUpHVFuncionamiento', function () {
        if (!($('input[name ="estadoFunca"]:checked').length)) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe seleccior una opcion del estado de funcionamiento del activo fijo');
        } else if (parseInt($('input[name ="estadoFunca"]:checked').val()) == 4 && ($('#numAniosOut').val() == '' || parseInt($('#numAniosOut').val()) < 0)) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar el numero de años que ha estado fuera de servicio');
        } else {
            let data = $('#formUpFuncaAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_hv_funcionamiento.php',
                data: data,
                success: function (r) {
                    if (r == 1) {
                        let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                        $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                            $('#divTamModalForms').removeClass('modal-xl');
                            $('#divTamModalForms').removeClass('modal-lg');
                            $('#divTamModalForms').addClass('modal-xl');
                            $('#divModalForms').modal('show');
                            $("#divForms").html(he);
                            $('#collapsemodOnce').addClass('show');
                        });
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Registro de funcionamiento actualizado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $('#divModalForms').on('input', '#buscaTercero', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + '/terceros/gestion/datos/listar/buscar_terceros.php',
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
                $('#idTercero').val(ui.item.id);
            }
        });
    });
    //Registrar mantenimiento de activo fijo preventivo o correctivo
    $('#divModalForms').on('click', '#btnUpHVRegMmtoCoPr', function () {
        if ($('#fecMmto').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar la fecha del mantenimiento');
        } else if (parseInt($('#tipoMmnto').val()) == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe seleccionar el tipo de mantenimiento');
        } else if (parseInt($('#numReporte').val()) < 0 || $('#numReporte').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar el numero de reporte');
        } else if (parseInt($('#idTercero').val()) == 0 || $('#buscaTercero').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Se debe ingresar el tercero responsable');
        } else {
            let data = $('#formRegMmtoCoPr').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id_mmto=' + $('#id_mmto').val();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_hv_regmmto.php',
                data: data,
                success: function (r) {
                    if (r == 1) {
                        let id = 'tableMantenimientoAcfijo';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Registro de mantenimiento realizado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    //eliminar registro de mantenimiento
    $('#divModalForms').on('click', '#formRegMmtoCoPr .borrar', function () {
        let id = $(this).attr('value');
        let tip = 'RegMmtoCP';
        confdel(id, tip);
    });
    //eliminar registro de funcionamiento
    $('#divModalConfDel').on('click', '#btnConfirDelRegMmtoCP', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/reg_mantenimiento.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let data = $('#formDataAcFijo').serialize() + '&id_serie=' + $('#id_serial_hv').val() + '&id=' + $('#id_mmto').val();
                    $.post("datos/actualizar/form_up_hvida_acfijo.php", data, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#collapsemodDoce').addClass('show');
                    });
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Registro de mantenimiento eliminado correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#divModalForms').on('click', '#btnRegCalcDepreciacion', function () {
        var elemento = $(this);
        elemento.attr('disabled', true);
        elemento.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registrando...');
        var mes = $('#slcMesDp').val();
        if (mes == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un mes');
        } else {
            $.ajax({
                type: 'POST',
                url: 'registrar/new_calculo_dprecia.php',
                data: { mes: mes },
                dataType: 'json',
                success: function (r) {
                    elemento.attr('disabled', false);
                    elemento.html('Calcular y Registrar');
                    if (r.status.trim() === 'ok') {
                        let id = 'tableDepreciaciones';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html(r.msg);
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r.msg);
                    }
                }
            });
        }
        return false;
    });
    $('#tableDepreciaciones').on('click', '.ver', function () {
        let mes = $(this).attr('value');
        $.post("informes/imp_liq_depreciacion.php", { mes: mes }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
})(jQuery);