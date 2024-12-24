(function ($) {
    //Superponer modales
    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function () {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    var showError = function (id) {
        $('#' + id).focus();
        $('#e' + id).show();
        setTimeout(function () {
            $('#e' + id).fadeOut(600);
        }, 800);
        return false;
    };
    var bordeError = function (p) {
        $('#' + p).css("border", "2px solid #F5B7B1");
        $('#' + p).css('box-shadow', '0 0 4px 3px pink');
        return false;
    };
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
            url: '../../nomina/empleados/eliminar/confirdel.php',
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
        //dataTable adquisiciones
        $('#tableAdquisiciones').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_adquisicion.php", { id_adq: 0 }, function (he) {
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
                url: 'datos/listar/datos_adquisiciones.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'modalidad' },
                //{ 'data': 'adquisicion' },
                { 'data': 'valor' },
                { 'data': 'fecha' },
                { 'data': 'objeto' },
                { 'data': 'tercero' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            columnDefs: [{
                class: 'text-wrap',
                targets: [1, 4, 5]
            }],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableAdquisiciones').wrap('<div class="overflow" />');
        $('#tableLisTerCot').wrap('<div class="overflow" />');
        $('#tableUpAdqBnSv').DataTable({
            language: setIdioma,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableUpAdqBnSv').wrap('<div class="overflow" />');

        $('.tableCotRecibidas').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_servicios.php", { id_adq: $('#id_compra').val(), tipo_servicio: $('#tipo_servicio').val() }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            columnDefs: [{
                class: 'text-wrap',
                targets: [1]
            }],
            "pageLength": -1
        });
        $('.tableCotRecibidas').wrap('<div class="overflow" />');
        //tabla lista de compra recibida
        $('#tableListProdRecibidos').DataTable({
            language: setIdioma,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableListProdRecibidos').wrap('<div class="overflow" />');
        //tabla documentos soporte de contratos
        let id_csp = $('#id_contrato_compra').val();
        $('#tableDocSopContrato').DataTable({
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_docs_soporte_c.php',
                type: 'POST',
                data: { id_csp: id_csp },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'num' },
                { 'data': 'doc' },
                { 'data': 'archivo' },
                { 'data': 'estado' },
            ],
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableDocSopContrato').wrap('<div class="overflow" />');
        $('#tableNovedadesContrato').DataTable({
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_novedades_contrato.php',
                type: 'POST',
                data: { id_csp: id_csp },
                dataType: 'json',
            },
            "columns": [
                { 'data': 't_novedad' },
                { 'data': 'fecha' },
                { 'data': 'valor1' },
                { 'data': 'valor2' },
                { 'data': 'inicia' },
                { 'data': 'fin' },
                { 'data': 'observacion' },
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
                targets: [6]
            }],
        });
        $('#tableNovedadesContrato').wrap('<div class="overflow" />');
        $('#divForms').on('change', '#slcTipoBnSv', function () {
            let id_bs = $(this).val();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/contratacion/adquisiciones/registrar/slc_objeto_predef.php',
                data: { id_bs: id_bs },
                success: function (r) {
                    $('#txtObjeto').html(r);
                }
            });
        });
        $('#slcTipoBnSv').on('change', function () {
            let id_bs = $(this).val();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/contratacion/adquisiciones/registrar/slc_objeto_predef.php',
                data: { id_bs: id_bs },
                success: function (r) {
                    $('#txtObjeto').html(r);
                }
            });
        });
        var orderCh = function () {
            $('input[type=checkbox]:checked').each(function () {
                var $this = $(this),
                    fila = $this.closest('tr'),
                    tbody = $this.closest('tbody')
                if ($this.is(':checked')) {
                    fila.prependTo(tbody);
                } else {
                    fila.appendTo(tbody);
                }
            });
        };
        orderCh();
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#orderCheck').on('click', function () {
            orderCh();
        });
    });
    $('#divForms').on('change', '#slcAreaSolicita', function () {
        var id = $(this).val();
        $.ajax({
            type: 'POST',
            url: 'datos/listar/tipo_bs_adq.php',
            data: { id: id },
            dataType: 'json',
            success: function (r) {
                if (r.status == 'ok') {
                    $('#filtro').val(r.filtro);
                    if (r.tipo == '0') {
                        $('#slcTipoBnSv').val(0);
                        $('#txtBuscarTipoBnSv').val('');
                        $('#txtBuscarTipoBnSv').attr('disabled', false);
                        $('#txtBuscarTipoBnSv').attr('readonly', false);
                    } else {
                        $('#txtBuscarTipoBnSv').attr('disabled', true);
                        $('#txtBuscarTipoBnSv').attr('readonly', true);
                        $('#slcTipoBnSv').val(r.id);
                        $('#txtBuscarTipoBnSv').val(r.nombre);
                    }
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r.msg);
                }
            }
        });
    });
    //Agregar adquisicion
    $('#divForms').on('click', '#btnAddAdquisicion', function () {
        if ($('#datFecAdq').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Fecha de aquisición no puede ser Vacía!');
        } else if ($('#slcTipoBnSv').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Tipo de bien o servicio no puede ser Vacío!');
        } else if ($('#slcModalidad').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe seleccionar una modalidad de contratación!');
        } else if ($('#slcAreaSolicita').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe seleccionar el área solicitante!');
        } else if ($('#txtObjeto').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Objeto no puede ser Vacío!');
        } else {
            datos = $('#formAddAdquisicion').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_adquisicion.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableAdquisiciones';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Adquisición Agregada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Editar adquisición 
    $('#modificarAdquisiciones').on('click', '.detalles', function () {
        let id_det = $(this).attr('value');
        $('<form action="detalles_adquisicion.php" method="post"><input type="hidden" name="detalles" value="' + id_det + '" /></form>')
            .appendTo('body').submit();
    });
    $('#modificarAdquisiciones').on('click', '.comprobar', function () {
        let id_det = $(this).attr('value');
        $('<form action="detalles_adquisicion.php" method="post"><input type="hidden" name="detalles" value="' + id_det + '" /></form>')
            .appendTo('body').submit();
    });
    $('#modificarAdquisiciones').on('click', '.editar', function () {
        let id_up = $(this).attr('value');
        $.post("datos/registrar/formadd_adquisicion.php", { id_adq: id_up }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });

    });
    $('#tableAdqBnSv input[type=checkbox]').on('change', function () {
        if ($('#tipo_contrato').val() == '1') {
            var id = $(this).attr('value');
            var tipo = $('#tipo_' + id).val();
            if ($(this).is(':checked')) {
                $.post("datos/listar/val_honorarios.php", { id: id, tipo: tipo }, function (data) {
                    $('#val_bnsv_' + id).val(data);
                    $('#bnsv_' + id).val('1');
                });
            } else {
                $('#val_bnsv_' + id).val('0');
                $('#bnsv_' + id).val('');
            }
        }
    });
    $('#tableAdqBnSv').on('input', '.cantidad', function () {
        if ($('#tipo_contrato').val() == '1') {
            var id = $(this).attr('id').split('_')[1];
            var tipo = $('#tipo_' + id).val();
            var cantidad = Number($(this).val());
            $.post("datos/listar/val_honorarios.php", { id: id, tipo: tipo }, function (data) {
                $('#val_bnsv_' + id).val(cantidad * Number(data));
            });

        }
    });
    $('#btnDestContra').on('click', function () {
        let accion = $(this).attr('value');
        let id_adq = $('#id_compra').val();
        var validar = true;
        var centros = [];
        $('.form-control').removeClass('border-danger');
        $('#contenedor select').each(function () {
            let val = $(this).val();
            if ($(this).hasClass('slcCentroCosto')) {
                if (centros.includes(val)) {
                    validar = false;
                    $(this).addClass('border-danger');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Centro de costo ya se encuentra seleccionado');
                    return false;
                }
                if (val != '0') {
                    centros.push(val);
                }

            }
            if (val == 0) {
                validar = false;
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('Seleccionar una opción');
                return false;
            }
        });
        if (!validar) {
            return false;
        }
        $('#contenedor input[type="number"]').each(function () {
            let val = $(this).val();
            if (val == '' || Number(val) < 1) {
                validar = false;
                $(this).addClass('border-danger');
                $('#divModalError').modal('show');
                $('#divMsgError').html('Ingresar un valor mayor o igual a 1');
                return false;
            }
        });
        if (validar) {
            let datos = $('#formDestContra').serialize() + '&id_adq=' + id_adq + '&accion=' + accion;
            $.ajax({
                type: 'POST',
                url: 'registrar/new_dest_contra.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {

                        $('#divModalDone a').attr('data-dismiss', '');
                        $('#divModalDone a').attr('href', 'javascript:location.reload()');
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Destino de contrato guardado con éxito');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    //Actualizar adquisición -> compra
    $('#btnUpDataAdqCompra').on('click', function () {
        if ($('#datUpFecAdqCompra').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Fecha de aquisición no puede ser Vacía!');
        } else if ($('#numTotalContrato').val() == '' || parseInt($('#numTotalContrato').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡El valor total del contrato debe ser mayor o igual a cero!');
        } else if ($('#txtObjeto').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Objeto no puede ser Vacío!');
        } else {
            datos = $('#formuPAdqCompra').serialize();
            $.ajax({
                type: 'POST',
                url: 'up_datos_adq_compra.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        $('#divModalDone a').attr('data-dismiss', '');
                        $('#divModalDone a').attr('href', 'javascript:location.reload()');
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Actualización realizada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Actualizar detalles aquisicion -> compra
    $('#btnUpDetalAdqCompra').on('click', function () {
        let b = 1;
        $('input[type=checkbox]:checked').each(function () {
            let idcheck = $(this).val();
            let idCant = 'bnsv_' + idcheck;
            let idval = 'val_bnsv_' + idcheck;
            if ($('#' + idCant).val() === '' || parseInt($('#' + idCant).val()) <= 0) {
                showError(idCant);
                bordeError(idCant);
                b = 0
                return false;;
            }
            if ($('#' + idval).val() === '' || parseInt($('#' + idval).val()) <= 0) {
                showError(idval);
                bordeError(idval);
                b = 0
                return false;;
            }

        });
        if (b === 1) {
            let datos = $('#formDetallesAdq').serialize();
            $.ajax({
                type: 'POST',
                url: 'up_datos_detalles_compra.php',
                data: datos,
                success: function (r) {
                    if (r === 0) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html("No se agregó ningún bien o servicio");
                    } else if (r > 0) {
                        $('#divModalDone a').attr('data-dismiss', '');
                        $('#divModalDone a').attr('href', 'javascript:location.reload()');
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Se actualizaron bien(es) o servicio(s) de la compra actual');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
            return false;
        }
    });
    //Borrar modalidad confirmar
    $('#modificarAdquisiciones').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Adquisicion';
        confdel(id, tip);
    });
    //Eliminar adquisicion
    $("#divBtnsModalDel").on('click', '#btnConfirDelAdquisicion', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_adquisicion.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableAdquisiciones';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Orden de compra eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('.listOrdenes').on('click', function () {
        let tipo = $(this).attr('text');
        $.post("datos/listar/ordenes_almacen_activos.php", { tipo: tipo }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Slc tercero cotizacion
    $('#modificarAdquisiciones').on('click', '.enviar', function () {
        let id = $(this).attr('value');
        $.post("datos/listar/list_terceros.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Bajar cotizacion
    $('#modificarAdquisiciones').on('click', '.bajar', function () {
        let id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'datos/actualizar/bajar_cotizacion.php',
            data: { id: id },
            success: function (r) {
                if (r == 1) {
                    let id = 'tableAdquisiciones';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Cotización bajada correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    $('#modificarAdquisiciones').on('click', '.anular', function () {
        let id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'datos/actualizar/anula_adq.php',
            data: { id: id },
            success: function (r) {
                if (r == 1) {
                    let id = 'tableAdquisiciones';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Adquisición anulada correctamente');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    //Agregar tipo de contrato
    $('#divForms').on('click', '#x', function () {
        if ($('#slcTipoCompra').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Tipo de compra no puede ser Vacía!');
        } else if ($('#txtTipoContrato').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Tipo de contrato no puede ser Vacío!');
        } else {
            datos = $('#formAddTipoContrato').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_tipo_contrato.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableTipoContrato';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Tipo de Contrato Agregado Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Actualizar tipo de contrato -> formulario
    $('#x').on('click', '.editar', function () {
        let idtc = $(this).attr('value');
        $.post("datos/actualizar/up_tipo_contrato.php", { idtc: idtc }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar datos tipo de contrato
    $('#divForms').on('click', '#x', function () {
        let id;
        if ($('#txtTipoContrato').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Tipo de contrato no puede ser Vacío!');
        } else {
            let datos = $('#formActualizaTipoContrato').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_datos_tipo_contrato.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        id = 'tableTipoContrato';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Datos Actualizados Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Borrar tipo de contrato confirmar
    $('#x').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'TipoContrato';
        confdel(id, tip);
    });
    //Eliminar tipo de contrato
    $("#divBtnsModalDel").on('click', '#x', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_tipo_contrato.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableTipoContrato';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Tipo de contrato eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Agregar tipo de bien o servicio
    $('#divForms').on('click', '#x', function () {
        if ($('#slcTipoContrato').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe selecionar tipo de contrato!');
        } else if ($('#txtTipoBnSv').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Tipo de contrato no puede ser Vacío!');
        } else {
            datos = $('#formAddTipoBnSv').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_tipo_bn_sv.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableTipoBnSv';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Tipo de bien o servicio Agregado Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Actualizar tipo de bien o servicio -> formulario
    $('#x').on('click', '.editar', function () {
        let idtbs = $(this).attr('value');
        $.post("datos/actualizar/up_tipo_bn_sv.php", { idtbs: idtbs }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar datos tipo de bien o servicio
    $('#divForms').on('click', '#x', function () {
        let id;
        if ($('#txtTipoContrato').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Tipo de bien o servicio no puede ser Vacío!');
        } else {
            let datos = $('#formActualizaBnSv').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_datos_tipo_bn_sv.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        id = 'tableTipoBnSv';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Datos Actualizados Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Borrar tipo de bien o servicio
    $('#x').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'TipoBnSv';
        confdel(id, tip);
    });
    //Eliminar tipo de bien o servicio
    $("#divBtnsModalDel").on('click', '#x', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_tipo_bn_sv.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableTipoBnSv';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Tipo de bien o servicio eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Agregar bien o servicio
    $('#divForms').on('click', '#x', function () {
        if ($('#slcTipoBnSv').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe selecionar tipo de bien o servicio!');
        } else if ($('#txtBnSv').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Bien o servicio no puede ser Vacío!');
        } else {
            datos = $('#formAddBnSv').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_bn_sv.php',
                data: datos,
                success: function (r) {
                    if (r === 0) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('No ingresó nigún nuevo registro');
                    } else if (r > 0) {
                        let id = 'tableBnSv';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Se agregaron <b>' + r + '</b> bien(es) y/o servicio(s) correctamente');
                        return false;
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Actualizar bien o servicio -> formulario
    $('#x').on('click', '.editar', function () {
        let idbs = $(this).attr('value');
        $.post("datos/actualizar/up_bn_sv.php", { idbs: idbs }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar datos de bien o servicio
    $('#divForms').on('click', '#x', function () {
        let id;
        if ($('#txtBnSv').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Bien o servicio no puede ser Vacío!');
        } else {
            let datos = $('#formActualizaBnSv').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_datos_bn_sv.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        id = 'tableBnSv';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Datos Actualizados Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Borrar bien o servicio
    $('#x').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'BnSv';
        confdel(id, tip);
    });
    //Eliminar bien o servicio
    $("#divBtnsModalDel").on('click', '#x', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_bn_sv.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableBnSv';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Tipo de bien o servicio eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Enviar cotizacion
    $('#divForms').on('click', '#btnEnviarCotizacion', function () {
        let datos = $('#formListTerc').serialize();
        $.ajax({
            type: 'POST',
            url: 'enviar/enviar_cotizacion.php',
            data: datos,
            success: function (r) {
                if (r === '1') {
                    let id = 'tableAdquisiciones';
                    reloadtable(id);
                    $('#divModalForms').modal('hide');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Cotizaciones enviadas correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('.btnSlcCot').on('click', function () {
        let datos = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'datos/actualizar/elegir_cotizacion.php',
            data: { datos: datos },
            success: function (r) {
                if (r == 1) {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Cotizaciones elegida correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    $("#divModalForms").on('click', '#btnDuplicaAdq', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#datFecAdq').val() == '') {
            $('#datFecAdq').focus();
            $('#datFecAdq').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha de aquisición no puede ser vacía');
        } else if (Number($('#numTotalContrato').val()) <= 0) {
            $('#numTotalContrato').focus();
            $('#numTotalContrato').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor estimado del contrato debe ser mayor a cero');
        } else if (Number($('#txtObjeto').val()) <= 0) {
            $('#txtObjeto').focus();
            $('#txtObjeto').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Objeto del contrato no puede ser vacío');
        } else if ($('#datFecIniEjec').val() == '') {
            $('#datFecIniEjec').focus();
            $('#datFecIniEjec').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede ser vacía');
        } else if ($('#datFecFinEjec').val() == '') {
            $('#datFecFinEjec').focus();
            $('#datFecFinEjec').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Final no puede ser vacía');
        } else if (Number($('#numValContrata').val()) <= 0) {
            $('#numValContrata').focus();
            $('#numValContrata').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor total del contrato debe ser mayor a cero');
        } else if ($('#numDS').val() == '') {
            $('#numDS').focus();
            $('#numDS').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Número DC no puede ser vacío');
        } else {
            let validar = false;
            $('.slcCentroCosto').each(function () {
                if ($(this).val() == '0') {
                    $(this).focus();
                    $(this).addClass('is-invalid');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Selecccionar centro de costo');
                    validar = true;
                    return false;
                }

            });
            if (validar) {
                return false;
            }
            validar = false;
            $('input[name="numHorasMes[]"]').each(function () {
                if ($(this).val() == '' || Number($(this).val()) <= 0) {
                    $(this).focus();
                    $(this).addClass('is-invalid');
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Cantidad debe ser mayor a cero');
                    validar = true;
                    return false;
                }
            });
            if (validar) {
                return false;
            }
            var datos = $('#formDuplicaAdq').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_duplica_adq.php',
                data: datos,
                success: function (r) {
                    if (r.trim() === 'ok') {
                        let id = "tableAdquisiciones";
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Adquisición duplicada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $("#divModalForms").on('input', '#SeaTercer', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + "/terceros/gestion/datos/listar/buscar_terceros.php",
                    dataType: "json",
                    type: 'POST',
                    data: {
                        term: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                $('#id_tercero').val(ui.item.id);
            }
        });
    });

    $("#divModalForms").on('input', '#txtBuscarTipoBnSv', function () {
        let area = $('#slcAreaSolicita').val();
        if (Number(area) != 0) {
            $(this).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "datos/listar/list_tipo_servicio.php",
                        dataType: "json",
                        type: 'POST',
                        data: { term: request.term, area: area },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    $('#slcTipoBnSv').val(ui.item.id);
                    $('#txtObjeto').val(ui.item.objeto);
                }
            });
        } else {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un área solicitante');
        }
    });
    $('#btnAddEstudioPrevio').on('click', function () {
        let id = $('#id_compra').val();
        $.post("datos/registrar/formadd_estudio_previo.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnAddNewEstudioPrevio', function () {
        if ($('#datFecIniEjec').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede ser vacío');
        } else if ($('#datFecFinEjec').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha final no puede ser vacío');
        } else {
            let fecini = new Date($('#datFecIniEjec').val());
            let fecfin = new Date($('#datFecFinEjec').val());
            if (fecfin <= fecini) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
            } else if ($('#numValContrata').val() == '' || parseInt($('#numValContrata').val()) <= 0) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
            } else if ($('#slcFormPago').val() == 0) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe selecionar una forma de pago');
            } else if ($('#slcSupervisor').val() == 0) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe selecionar un supervisor o elegir "PENDIENTE"');
            } else if ($('#numDS').val() == '' || parseInt($('#numDS').val()) <= 0) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Debe ingresar un número DS');
            } else if ($('#slcFormPago').val() == 3 && $('#check_3').prop("checked") == false) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para Pago Anticipado debe selecionar Póliza de manejo de anticipo');
            } else {
                let datos = $('#formAddEstudioPrevio').serialize();
                let necesidad = $('#txtDescNec').val().replace(/(\r\n|\n|\r)/gm, "||");
                let actividades = $('#txtActEspecificas').val().replace(/(\r\n|\n|\r)/gm, "||");
                let productos = $('#txtProdEntrega').val().replace(/(\r\n|\n|\r)/gm, "||");
                let obligaciones = $('#txtObligContratista').val().replace(/(\r\n|\n|\r)/gm, "||");
                let pago = $('#txtFormPago').val().replace(/(\r\n|\n|\r)/gm, "|");
                let reqMinHab = $('#txtReqMinHab').val().replace(/(\r\n|\n|\r)/gm, "|");
                let garant = $('#txtGarantias').val().replace(/(\r\n|\n|\r)/gm, "|");
                let descVal = $('#txtDescValor').val().replace(/(\r\n|\n|\r)/gm, "|");
                datos = datos + "&necesidad=" + necesidad + "&actividades=" + actividades + "&productos=" + productos + "&obligaciones=" + obligaciones + "&pago=" + pago + "&reqMinHab=" + reqMinHab + "&garant=" + garant + "&descVal=" + descVal;
                $.ajax({
                    type: 'POST',
                    url: 'registrar/new_estudio_previo.php',
                    data: datos,
                    success: function (r) {
                        if (r == 1) {
                            $('#divModalDone a').attr('data-dismiss', '');
                            $('#divModalDone a').attr('href', 'javascript:location.reload()');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Datos registrados correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    $('#modificarEstPrev').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/formup_estudio_previo.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    $('#divModalForms').on('click', '#btnUpEstudioPrevio', function () {
        if ($('#datFecIniEjec').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede ser vacío');
        } else if ($('#datFecFinEjec').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha final no puede ser vacío');
        } else if ($('#numValContrata').val() == '' || parseInt($('#numValContrata').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor de contratación debe ser mayor a cero');
        } else if ($('#numDS').val() == '' || parseInt($('#numDS').val()) <= 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Número DS debe ser mayor a cero');
        } else if ($('#slcSupervisor').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un supervisor');
        } else {
            let fecini = new Date($('#datFecIniEjec').val());
            let fecfin = new Date($('#datFecFinEjec').val());
            if (fecfin <= fecini) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
            } else if ($('#slcFormPago').val() == 3 && $('#check_3').prop("checked") == false) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para Pago Anticipado debe selecionar Póliza de manejo de anticipo');
            } else {
                let datos = $('#formUpEstudioPrevio').serialize();
                let necesidad = $('#txtDescNec').val().replace(/(\r\n|\n|\r)/gm, "||");
                let actividades = $('#txtActEspecificas').val().replace(/(\r\n|\n|\r)/gm, "||");
                let productos = $('#txtProdEntrega').val().replace(/(\r\n|\n|\r)/gm, "||");
                let obligaciones = $('#txtObligContratista').val().replace(/(\r\n|\n|\r)/gm, "||");
                let pago = $('#txtFormPago').val().replace(/(\r\n|\n|\r)/gm, "|");
                let reqMinHab = $('#txtReqMinHab').val().replace(/(\r\n|\n|\r)/gm, "|");
                let garant = $('#txtGarantias').val().replace(/(\r\n|\n|\r)/gm, "|");
                let descVal = $('#txtDescValor').val().replace(/(\r\n|\n|\r)/gm, "|");
                datos = datos + "&necesidad=" + necesidad + "&actividades=" + actividades + "&productos=" + productos + "&obligaciones=" + obligaciones + "&pago=" + pago + "&reqMinHab=" + reqMinHab + "&garant=" + garant + "&descVal=" + descVal;
                $.ajax({
                    type: 'POST',
                    url: 'actualizar/up_datos_estudio_previo.php',
                    data: datos,
                    success: function (r) {
                        if (r == 1) {
                            $('#divModalDone a').attr('data-dismiss', '');
                            $('#divModalDone a').attr('href', 'javascript:location.reload()');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Datos registrados correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    $('#btnFormatoEstudioPrevio').on('click', function () {
        let id = $('#id_compra').val();
        $('<form action="soportes/estudios_previos.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#btnMatrizRiesgo').on('click', function () {
        let id = $('#id_compra').val();
        $('<form action="soportes/matriz_riesgos.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#btnAnexos').on('click', function () {
        let id = $('#id_compra').val();
        $('<form action="soportes/anexos.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#modificarEstPrev').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'EstudPrevio';
        confdel(id, tip);
    });
    $("#divBtnsModalDel").on('click', '#btnConfirDelEstudPrevio', function () {
        $('#divModalConfDel').modal('hide');
        let id_c = $('#id_compra').val();
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_estudio_previo.php',
            data: { id_c: id_c },
            success: function (r) {
                if (r === '1') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Estudio Previo eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#btnAddContrato').on('click', function () {
        let id = $('#id_compra').val();
        $.post("datos/registrar/formadd_contrato_compra.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('change', '#datFecIniEjec', function () {
        let i = $('#datFecIniEjec').val();
        let f = $('#datFecFinEjec').val();
        if (i == '' || f == '') {
            $('#divDuraContrato').css('color', 'red');
            $('#divDuraContrato').html('No válido');
        } else {
            let fecini = new Date(i);
            let fecfin = new Date(f);
            if (fecfin > fecini) {
                $.post("registrar/calc_fecha.php", { i: i, f: f }, function (r) {
                    $('#divDuraContrato').css('color', 'black');
                    $('#divDuraContrato').html(r);
                });
            } else {
                $('#divDuraContrato').css('color', 'red');
                $('#divDuraContrato').html('No válido');
            }
        }
        return false;
    });
    $('#divModalForms').on('change', '#datFecFinEjec', function () {
        let i = $('#datFecIniEjec').val();
        let f = $('#datFecFinEjec').val();
        if (i == '' || f == '') {
            $('#divDuraContrato').css('color', 'red');
            $('#divDuraContrato').html('No válido');
        } else {
            let fecini = new Date(i);
            let fecfin = new Date(f);
            if (fecfin > fecini) {
                $.post("registrar/calc_fecha.php", { i: i, f: f }, function (r) {
                    $('#divDuraContrato').css('color', 'black');
                    $('#divDuraContrato').html(r);
                });
            } else {
                $('#divDuraContrato').css('color', 'red');
                $('#divDuraContrato').html('No válido');
            }
        }
        return false;
    });
    $('#divModalForms').on('click', '#btnAddContratoCompra', function () {
        if ($('#datFecIniEjec').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede ser vacío');
        } else if ($('#datFecFinEjec').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha final no puede ser vacío');
        } else if ($('#txtCodIntern').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número  para el contrato');
        } else if ($('#id_tercero').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tercero');
        } else if ($('#txtCodSecop').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar el código SECOP II para el contrato');
        } else if ($('#numValContrata').val() == '' || $('#numValContrata').val() == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor de contrato debe ser mayor a cero');
        } else if ($('#slcSupervisor').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe selecionar un Supervisor para el contrato');
        } else {
            let fecini = new Date($('#datFecIniEjec').val());
            let fecfin = new Date($('#datFecFinEjec').val());
            if (fecfin <= fecini) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
            } else if ($('#slcFormPago').val() == 3 && $('#check_3').prop("checked") == false) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para Pago Anticipado debe selecionar Póliza de manejo de anticipo');
            } else {
                let datos = $('#formAddcontratoCompra').serialize();
                $.ajax({
                    type: 'POST',
                    url: 'registrar/new_contrato_compra.php',
                    data: datos,
                    success: function (r) {
                        if (r == '1') {
                            $('#divModalDone a').attr('data-dismiss', '');
                            $('#divModalDone a').attr('href', 'javascript:location.reload()');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Datos registrados correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    $('#modificarContraCompra').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/formup_contrato_compra.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    $('#divModalForms').on('click', '#btnUpContratoCompra', function () {
        if ($('#datFecIniEjec').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede ser vacío');
        } else if ($('#datFecFinEjec').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha final no puede ser vacío');
        } else if ($('#id_tercero').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un tercero');
        } else {
            let fecini = new Date($('#datFecIniEjec').val());
            let fecfin = new Date($('#datFecFinEjec').val());
            if (fecfin <= fecini) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
            } else if ($('#slcFormPago').val() == 3 && $('#check_3').prop("checked") == false) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Para Pago Anticipado debe selecionar Póliza de manejo de anticipo');
            } else {
                let datos = $('#formUpContraCompra').serialize() + "&id_compra=" + $('#id_compra').val();
                $.ajax({
                    type: 'POST',
                    url: 'actualizar/up_datos_contrato_compra.php',
                    data: datos,
                    success: function (r) {
                        if (r == 1) {
                            $('#divModalDone a').attr('data-dismiss', '');
                            $('#divModalDone a').attr('href', 'javascript:location.reload()');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Datos registrados correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    $('#modificarContraCompra').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'ContraCompra';
        confdel(id, tip);
    });
    $("#divBtnsModalDel").on('click', '#btnConfirDelContraCompra', function () {
        $('#divModalConfDel').modal('hide');
        let id_c = $('#id_compra').val();
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_contrato_compra.php',
            data: { id_c: id_c },
            success: function (r) {
                if (r === '1') {
                    $('#divModalDone a').attr('data-dismiss', '');
                    $('#divModalDone a').attr('href', 'javascript:location.reload()');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Contrato de Compra eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#btnFormatoCompraVenta').on('click', function () {
        let id = $('#id_compra').val();
        $('<form action="soportes/compraventa.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#btnFormatoServicios').on('click', function () {
        let id = $('#id_compra').val();
        $('<form action="soportes/prestacion_servicios.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#btnFormatoDesigSuper').on('click', function () {
        let id = $('#id_compra').val();
        $('<form action="soportes/designacion_supervisor.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#btnFormActaInicio').on('click', function () {
        let id = $('#id_compra').val();
        $('<form action="soportes/acta_inicio.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#btnCerrarContrato').on('click', function () {
        var id_adq = $('#id_compra').val();
        Swal.fire({
            title: "¿Confirma cierre de Contrato?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'actualizar/up_cerrar_contrato.php',
                    data: { id_adq: id_adq },
                    success: function (r) {
                        if (r == 'ok') {
                            location.reload();
                            mje('Contrato cerrado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    /*
    $('#btnEnviarContrato').on('click', function () {
        let id = $('#id_compra').val();
        $.post("datos/registrar/form_subir_contrato.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });*/
    $('#divForms').on('click', '#btnSubirContrato', function () {
        if ($('#fileContrato').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe elegir un archivo!');
        } else {
            let archivo = $('#fileContrato').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext !== '.pdf') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Solo se permite documentos .pdf!');
            } else if ($('#fileContrato')[0].files[0].size > 10485760) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Documento debe tener un tamaño menor a 10Mb!');
            } else {
                let datos = new FormData();
                datos.append('id_contrato_s', $('#id_contrato_s').val());
                datos.append('id_compra_s', $('#id_compra_s').val());
                datos.append('nit_empresa_s', $('#nit_empresa_s').val());
                datos.append('doc_tercero_s', $('#doc_tercero_s').val());
                datos.append('val_contrato_s', $('#val_contrato_s').val());
                datos.append('fileContrato', $('#fileContrato')[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: 'datos/actualizar/enviar_contrato.php',
                    contentType: false,
                    data: datos,
                    processData: false,
                    cache: false,
                    success: function (r) {
                        if (r == 1) {
                            $('#divModalDone a').attr('data-dismiss', '');
                            $('#divModalDone a').attr('href', 'javascript:location.reload()');
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Contrato enviado Correctamente');
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    $('#DocsSoportContrato ').on('click', '.descargar', function () {
        let data = $(this).attr('value').split('|');
        let ruta = data[0];
        let tipo = data[1];
        $.ajax({
            type: 'POST',
            url: 'datos/descargar/descarga_docs_soporte_contrato.php',
            dataType: 'json',
            data: { ruta: ruta },
            success: function (r) {
                if (r == 0) {
                    alert('Archivo no disponible');
                } else {
                    let a = document.createElement("a");
                    a.href = "data:application/pdf;base64," + r['file'];
                    a.download = tipo + ".pdf";
                    a.click();
                }

            }
        });
        return false;
    });
    $('#modificarContraCompra').on('click', '.supervisor', function () {
        if (!($('#id_sup_desig').length)) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Acción no permitida');
        } /*else if (!($('#id_c_final').length)) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Aun no se ha recibido el contrato por parte del tercero');
        } */else {
            let id_c = $("#id_c_final").val();
            let tercero = $('#id_sup_desig').val();
            let id_adquisicion = $('#id_compra').val();
            $.post("datos/registrar/formadd_designar_supervisor.php", { id_c: id_c, tercero: tercero, id_adquisicion: id_adquisicion }, function (he) {
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').removeClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(he);
            });
        }
        return false;
    });
    $('#divModalForms').on('click', '#btnDesigSupervisor', function () {
        if ($('#datFecDesigSup').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Designación de supervisor no puede ser vacío');
        } else if ($('#numMemorando').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Número de memorando no puede ser vacío');
        } else {
            let datos = $('#formDesingSupervisor').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_designacion_supervisor.php',
                data: datos,
                success: function (r) {
                    if (r == 1) {
                        $('#divModalDone a').attr('data-dismiss', '');
                        $('#divModalDone a').attr('href', 'javascript:location.reload()');
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Desiganacion de supervisor agregada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
        return false;
    });
    $('#btnEnviarActaSupervision').on('click', function () {
        let id = $(this).attr('value');
        $.post("datos/registrar/form_subir_acta_supervisor.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '#btnSubirDesigSuperv', function () {
        if ($('#fileSup').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe elegir un archivo!');
        } else {
            let archivo = $('#fileSup').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext != '.pdf') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Solo se permite documentos .pdf!');
            } else if ($('#fileSup')[0].files[0].size > 2097152) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Documento debe tener un tamaño menor a 2Mb!');
            } else {
                let datos = new FormData();
                datos.append('id_compra', $('#id_compra').val());
                datos.append('id_supervision', $('#id_supervision').val());
                datos.append('fileSup', $('#fileSup')[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: 'datos/actualizar/enviar_designa_supervisor.php',
                    contentType: false,
                    data: datos,
                    processData: false,
                    cache: false,
                    success: function (r) {
                        if (r == 1) {
                            $('#divModalDone a').attr('data-dismiss', '');
                            $('#divModalDone a').attr('href', 'javascript:location.reload()');
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Acta de supervision enviada correctamente');
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    $('.novedadC').on('click', function () {
        let opcion = $(this).attr('value');
        let id = $('#id_contrato_compra').val();
        if (Number(id) > 0) {
            $.post("datos/registrar/formadd_novedad_contrato.php", { opcion: opcion, id: id }, function (he) {
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(he);
            });
            return false;
        } else {
            mjeError('El proceso actual no tiene contrato');
        }
    });
    $('#modificarAdquisiciones').on('click', '.duplicar', function () {
        let id = $(this).attr('value');
        $.post("datos/registrar/form_duplica_est_prev.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    $('#divModalForms').on('click', '#btnNovContrato', function () {
        let op = $(this).attr('value');
        let correcto = 0;
        switch (op) {
            case '1':
                if ($('#slcTipoNovedad').val() == '0') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe selecionar un tipo de novedad');
                } else {
                    switch ($('#slcTipoNovedad').val()) {
                        case '1':
                            if (parseInt($('#numValAdicion').val()) <= 0 || $('#numValAdicion').val() == '') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('El valor de adición debe ser mayor a cero');
                            } else if ($('#datFecAdicion').val() == '') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('Fecha de adición no puede ser vacío');
                            } else if ($('#slcCDP').val() == '0') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('Debe elegir un CDP');
                            } else {
                                correcto = 1
                            }
                            break;
                        case '2':
                            if ($('#datFecIniProrroga').val() == '') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('Debe Ingresar Fecha Inicial de la Prorroga');
                            } else if ($('#datFecFinProrroga').val() == '') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('Debe Ingresar Fecha Final de la Prorroga');
                            } else {
                                let fecini = new Date($('#datFecIniProrroga').val());
                                let fecfin = new Date($('#datFecFinProrroga').val());
                                if (fecfin <= fecini) {
                                    $('#divModalError').modal('show');
                                    $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
                                } else {
                                    correcto = 1
                                }
                            }
                            break;
                        case '3':
                            if (parseInt($('#numValAdicion').val()) <= 0 || $('#numValAdicion').val() == '') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('El valor de adición debe ser mayor a cero');
                            } else if ($('#datFecAdicion').val() == '') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('Fecha de adición no puede ser vacío');
                            } else if ($('#slcCDP').val() == '0') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('Debe elegir un CDP');
                            } else if ($('#datFecIniProrroga').val() == '') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('Debe Ingresar Fecha Inicial de la Prorroga');
                            } else if ($('#datFecFinProrroga').val() == '') {
                                $('#divModalError').modal('show');
                                $('#divMsgError').html('Debe Ingresar Fecha Final de la Prorroga');
                            } else {
                                let fecini = new Date($('#datFecIniProrroga').val());
                                let fecfin = new Date($('#datFecFinProrroga').val());
                                if (fecfin <= fecini) {
                                    $('#divModalError').modal('show');
                                    $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
                                } else {
                                    correcto = 1
                                }
                            }
                            break;

                    }
                };
                break;
            case '2':
                if ($('#datFecCesion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha cesión');
                } else {
                    correcto = 1;
                }
                break;
            case '3':
                if ($('#datFecIniSuspencion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha Inicial de suspensión');
                } else if ($('#datFecFinSuspencion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha Final de suspensión');
                } else {
                    let fecini = new Date($('#datFecIniSuspencion').val());
                    let fecfin = new Date($('#datFecFinSuspencion').val());
                    if (fecfin <= fecini) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
                    } else {
                        correcto = 1
                    }
                }
                break;
            case '4':
                if ($('#datFecReinicio').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha de reinicio');
                } else {
                    let fecini = new Date($('#fecIniSus').val());
                    let fecfin = new Date($('#fecFinSus').val());
                    let fecrei = new Date($('#datFecReinicio').val());
                    if (fecrei > fecini && fecrei <= fecfin) {
                        correcto = 1;
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Fecha de reinicio debe estar dentro de rango de fechas de la última suspensión');
                    }
                }
                break;
            case '5':
                if ($('#slcTipTerminacion').val() == '0') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe seleccionar un tipo de terminación de contrato');
                } else {
                    correcto = 1;
                }
                break;
            case '6':
                if ($('#datFecLiq').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Fecha de liquidación no puede ser vacío');
                } else if ($('#slcTipTerminacion').val() == '0') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe seleccionar un tipo de liquidación de contrato');
                } else if ($('#numValFavorCtrate').val() == '' || parseInt($('#numValFavorCtrate').val()) < 0) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Valor a favor de contratante debe ser mayor o igual a cero');
                } else if ($('#numValFavorCtrista').val() == '' || parseInt($('#numValFavorCtrista').val()) < 0) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Valor a favor de contratista debe ser mayor o igual a cero');
                } else {
                    correcto = 1;
                }
                break;
        }
        if (correcto == 1) {
            let data = $('#formAddNovContrato').serialize();
            data = data + '&opcion=' + op;
            $.ajax({
                type: 'POST',
                url: 'registrar/new_novedad_contrato.php',
                data: data,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableNovedadesContrato';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Novedad agregada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
        return false;
    });
    $('#divModalForms').on('change', '#slcTipoNovedad', function () {
        let val = $(this).val();
        switch (val) {
            case '1':
                $('#divAdicion').show();
                $('#divCDPadicion').show();
                $('#divProrroga').hide();
                $('#divObservaNov').show();
                break;
            case '2':
                $('#divAdicion').hide();
                $('#divCDPadicion').hide();
                $('#divProrroga').show();
                $('#divObservaNov').show();
                break;
            case '3':
                $('#divAdicion').show();
                $('#divCDPadicion').show();
                $('#divProrroga').show();
                $('#divObservaNov').show();
                break;
        }
        return false;
    });
    var popdel = function (i, t) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'eliminar/confirdel.php',
            data: { id: i, tip: t }
        }).done(function (res) {
            $('#divModalConfDel').modal('show');
            $('#divMsgConfdel').html(res.msg);
            $('#divBtnsModalDel').html(res.btns);
        });
        return false;
    };
    //confirmar eliminar novedades de conrtato
    $('#tableNovedadesContrato').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'NovContrato';
        popdel(id, tip);
    });
    $("#divBtnsModalDel").on('click', '#btnConfirDelNovContrato', function () {
        $('#divModalConfDel').modal('hide');
        let id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_novedad_contrato.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableNovedadesContrato';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Novedad eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#tableNovedadesContrato').on('click', '.editar', function () {
        let datos = $(this).attr('value');
        $.post("datos/actualizar/formup_novedad_contrato.php", { datos: datos }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    $('#divModalForms').on('click', '#btnUpNovContrato', function () {
        let correcto = 0;
        $noved = $('#slcTipoNovedad').val();
        switch ($noved) {
            case '1':
                if (parseInt($('#numValAdicion').val()) <= 0 || $('#numValAdicion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('El valor de adición debe ser mayor a cero');
                } else if ($('#datFecAdicion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Fecha de adición no puede ser vacío');
                } else if ($('#slcCDP').val() == '0') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe elegir un CDP');
                } else {
                    correcto = 1
                }
                break;
            case '2':
                if ($('#datFecIniProrroga').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha Inicial de la Prorroga');
                } else if ($('#datFecFinProrroga').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha Final de la Prorroga');
                } else {
                    let fecini = new Date($('#datFecIniProrroga').val());
                    let fecfin = new Date($('#datFecFinProrroga').val());
                    if (fecfin <= fecini) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
                    } else {
                        correcto = 1
                    }
                }
                break;
            case '3':
                if (parseInt($('#numValAdicion').val()) <= 0 || $('#numValAdicion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('El valor de adición debe ser mayor a cero');
                } else if ($('#datFecAdicion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Fecha de adición no puede ser vacío');
                } else if ($('#slcCDP').val() == '0') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe elegir un CDP');
                } else if ($('#datFecIniProrroga').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha Inicial de la Prorroga');
                } else if ($('#datFecFinProrroga').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha Final de la Prorroga');
                } else {
                    let fecini = new Date($('#datFecIniProrroga').val());
                    let fecfin = new Date($('#datFecFinProrroga').val());
                    if (fecfin <= fecini) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
                    } else {
                        correcto = 1
                    }
                }
                break;
            case '4':
                if ($('#datFecCesion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha cesión');
                } else if ($('#id_tercero').val() == '0') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe seleccionar un tercero cesionario nuevo');
                } else {
                    correcto = 1;
                }
                break;
            case '5':
                if ($('#datFecIniSuspencion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha Inicial de suspensión');
                } else if ($('#datFecFinSuspencion').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha Final de suspensión');
                } else {
                    let fecini = new Date($('#datFecIniSuspencion').val());
                    let fecfin = new Date($('#datFecFinSuspencion').val());
                    if (fecfin <= fecini) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Fecha final debe ser mayor a Fecha Inicial');
                    } else {
                        correcto = 1
                    }
                }
                break;
            case '6':
                if ($('#datFecReinicio').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe Ingresar Fecha de reinicio');
                } else {
                    let fecini = new Date($('#fecIniSus').val());
                    let fecfin = new Date($('#fecFinSus').val());
                    let fecrei = new Date($('#datFecReinicio').val());
                    if (fecrei > fecini && fecrei <= fecfin) {
                        correcto = 1;
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Fecha de reinicio debe estar dentro de rango de fechas de la suspensión');
                    }
                }
                break;
            case '7':
                if ($('#slcTipTerminacion').val() == '0') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Debe seleccionar un tipo de terminación de contrato');
                } else {
                    correcto = 1;
                }
                break;
            case '8':
                if ($('#datFecLiq').val() == '') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Fecha de liquidación no puede ser vacío');
                } else if ($('#numValFavorCtrate').val() == '' || parseInt($('#numValFavorCtrate').val()) < 0) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Valor a favor de contratante debe ser mayor o igual a cero');
                } else if ($('#numValFavorCtrista').val() == '' || parseInt($('#numValFavorCtrista').val()) < 0) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html('Valor a favor de contratista debe ser mayor o igual a cero');
                } else {
                    correcto = 1;
                }
                break;

        }
        if (correcto == 1) {
            let data = $('#formUpNovContrato').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_novedad_contrato.php',
                data: data,
                success: function (r) {
                    if (r == 1) {
                        let id_t = 'tableNovedadesContrato';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Novedad actualizada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }

                }
            });
        }
        return false;
    });
    $('#detallesXEntrega').on('click', '.details', function () {
        let ids = $(this).attr('value');
        $.post("datos/listar/datos_porentrega.php", { ids: ids }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
        return false;
    });
    $('#contenedor').on('input', '.slcSedeAC', function () {
        let id_sede = $(this).val();
        let fila = $(this).parent().parent();
        if (id_sede == '0') {
            fila.find('.slcCentroCosto').html('<option value="0">--Seleccionar Sede--</option>');
        } else {
            $.post("datos/listar/datos_centros_costo.php", { id_sede: id_sede }, function (he) {
                fila.find('.slcCentroCosto').html(he);
            });
        }
        return false;
    });
    $('#divModalForms').on('input', '.slcSedeAC', function () {
        let id_sede = $(this).val();
        let fila = $(this).parent().parent();
        if (id_sede == '0') {
            fila.find('.slcCentroCosto').html('<option value="0">--Seleccionar Sede--</option>');
        } else {
            $.post("datos/listar/datos_centros_costo.php", { id_sede: id_sede }, function (he) {
                fila.find('.slcCentroCosto').html(he);
            });
        }
        return false;
    });
    $('#addRowSedes').on('click', function () {
        $.post("datos/listar/new_fila.php", function (he) {
            $('#contenedor').append(he);
        });
        return false;
    });
    $('#divModalForms').on('click', '#addRowSedes', function () {
        $.post("datos/listar/new_fila.php", function (he) {
            $('#contenedor').append(he);
        });
        return false;
    });
    $('#contenedor').on('click', '.delRowSedes', function () {
        let fila = $(this).parent().parent().parent().parent();
        fila.remove();
    });
    $('#divModalForms').on('click', '.delRowSedes', function () {
        let fila = $(this).parent().parent().parent().parent();
        fila.remove();
    });
    $('#guardarOrden').on('click', function () {
        var next = true;
        $('.is-invalid').removeClass('is-invalid');

        $('.aprobado').each(function () {
            var fila = $(this).closest('tr');

            if (fila.find('input[type="checkbox"]').is(':checked')) {
                fila.find('input[type="number"]').each(function () {
                    var $input = $(this);
                    var inputValue = Number($input.val());
                    var maxValue = $input.attr('max') ? Number($input.attr('max')) : null;
                    if (inputValue <= 0 || (maxValue !== null && inputValue > maxValue)) {
                        $input.addClass('is-invalid');
                        mjeError(inputValue <= 0 ? 'El valor debe ser mayor a cero' : `El valor no debe ser mayor a ${maxValue}`);
                        next = false;
                        return false;
                    }
                });
                if (!next) {
                    return false;
                }
            }
        });
        if (next) {
            let data = $('#formOrdenCompra').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_orden_compra.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        location.reload();
                        mje('Orden actualizada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
    $('#divModalForms').on('click', '#btnGuardarOrden', function () {
        var next = true;
        var c = 0;
        $('.is-invalid').removeClass('is-invalid');

        $('.aprobado').each(function () {
            var fila = $(this).closest('tr');

            if (fila.find('input[type="checkbox"]').is(':checked')) {
                fila.find('input[type="number"]').each(function () {
                    var $input = $(this);
                    var inputValue = Number($input.val());
                    if (inputValue <= 0) {
                        $input.addClass('is-invalid');
                        mjeError('El valor debe ser mayor a cero');
                        next = false;
                        return false;
                    }
                });
                if (!next) {
                    return false;
                }
                c++;
            }
        });
        if (next && c > 0) {
            let data = $('#formDetallesAdq').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_orden_servicio.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        location.reload();
                        mje('Orden guardada correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
    $('.modificarCotizaciones').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/formup_detalle_orden.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('.modificarCotizaciones').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        Swal.fire({
            title: "¿Confirma eliminar detalle de orden?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: 'eliminar/del_detalle_orden.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            location.reload();
                            mje('Detalle eliminado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#divModalForms').on('click', '#btnUpDetalleOrdnen', function () {
        $('.is-invalid').removeClass('is-invalid');
        if (Number($('#numCantidad').val()) <= 0) {
            $('#numCantidad').addClass('is-invalid');
            mjeError('La cantidad debe ser mayor a cero');
        } else if (Number($('#numValUnid').val()) <= 0) {
            $('#numValUnid').addClass('is-invalid');
            mjeError('El valor unitario debe ser mayor a cero');
        } else {
            let data = $('#formUpDetalleOrden').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_detalle_orden.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        location.reload();
                        mje('Detalle actualizado correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
    $('#cerrarOrden').on('click', function () {
        Swal.fire({
            title: "¿Confirma cierre de orden?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                var id_orden = $('#id_orden').val();
                var id_adq = $('#id_compra').val();
                $.ajax({
                    type: 'POST',
                    url: 'actualizar/up_cerrar_orden.php',
                    data: { id_orden: id_orden, id_adq: id_adq },
                    success: function (r) {
                        if (r == 'ok') {
                            location.reload();
                            mje('Orden cerrada correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#cerrarOrdenServicio').on('click', function () {
        Swal.fire({
            title: "¿Confirma cierre de orden?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                //sumar todas las inputs de clase sumTotal para saber si es mayor a cero
                let suma = 0;
                if ($('.sumTotal').length) {
                    $('.sumTotal').each(function () {
                        suma += Number($(this).val());
                    });
                }
                if (suma > 0) {
                    var id_adq = $('#id_compra').val();
                    $.ajax({
                        type: 'POST',
                        url: 'actualizar/up_cerrar_orden_sv.php',
                        data: { id_adq: id_adq, suma: suma },
                        success: function (r) {
                            if (r == 'ok') {
                                location.reload();
                                mje('Orden cerrada correctamente');
                            } else {
                                mjeError(r);
                            }
                        }
                    });
                } else {
                    mjeError('Debe ingresar al menos un detalle de orden');
                }
            }
        });
    });
})(jQuery);

function AsociarOrden(id_orden) {
    Swal.fire({
        title: "¿Confirma asignación de orden a adquisición?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#00994C",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si!",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {
            var ruta = "actualizar/up_adq_orden_compra.php";
            $.ajax({
                type: "POST",
                url: ruta,
                data: { id_orden: id_orden, id_adq: $('#id_compra').val() },
                success: function (r) {
                    if (r == 'ok') {
                        location.reload();
                        mje("Orden asignada correctamente");
                    } else {
                        mjeError("Error: " + r);
                    }
                },
            });
        }
    });

};