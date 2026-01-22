(function ($) {
    //Superponer modales
    function pesos(amount, decimals) {
        amount += ''; // por si pasan un numero en vez de un string
        amount = parseFloat(amount.replace(/[^0-9\.]/g, ''));
        decimals = decimals || 0;
        if (isNaN(amount) || amount === 0) {
            return parseFloat(0).toFixed(decimals);
        }
        amount = '' + amount.toFixed(decimals);
        var amount_parts = amount.split('.'),
            regexp = /(\d+)(\d{3})/;
        while (regexp.test(amount_parts[0]))
            amount_parts[0] = amount_parts[0].replace(regexp, '$1' + '.' + '$2');
        return '$' + amount_parts.join(',');
    }
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
    function FormDocSoporte(id) {
        $.post("datos/registrar/formadd_factura_no.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    }

    $(document).ready(function () {
        //dataTable facturas no obligados
        $('#tableFacurasNoObligados').DataTable({
            dom: setdom,
            buttons: [{
                //Registar nueva factura no obligado
                action: function (e, dt, node, config) {
                    FormDocSoporte(0);
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_factura_no.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id_facturano' },
                { 'data': 'tipo' },
                { 'data': 'estado' },
                { 'data': 'fec_compra' },
                { 'data': 'fec_vence' },
                { 'data': 'metodo' },
                { 'data': 'forma_pago' },
                { 'data': 'tipo_doc' },
                { 'data': 'no_doc' },
                { 'data': 'nombre' },
                { 'data': 'detalles' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [6, 9, 10] },
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableFacurasNoObligados').wrap('<div class="overflow" />');
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
    });
    //Agregar detalles de factura no obligado
    $('#divForms').on('click', '#btnMasDetalleFactura', function () {
        if ($('#txtDetalleFactura').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Detalle de la factura no puede estar vacío');
        } else {
            let detalle = '<div class="input-group mb-1"><input type="text" name="detalle[]" class="form-control form-control-sm altura" value="' + $('#txtDetalleFactura').val() + '"><div class="input-group-append altura"><button class="btn btn-sm btn-danger bttn-plus-dt delDetalleFNO" title="Quitar detalles factura no obligado"><span class="fas fa-minus-circle"></span></button></div></div>';
            $('#divDetalleFactura').append(detalle);
            $('#validaDetalles').val('1');
            $('#txtDetalleFactura').val('');
        }
        return false;
    });
    $('#divModalForms').on('click', '.delDetalleFNO', function () {
        let cant = parseInt($('.delDetalleFNO').length) - 1;
        if (cant == 0) {
            $('#validaDetalles').val('0');
        }
        $(this).parent().parent().remove();
        return false;
    });
    //Registrar factura no obligado
    $('#divForms').on('click', '#btnFacturaNO', function () {
        var opcion = $(this).attr('value');
        var aprobar = 1;
        var btn = $(this).attr('id');
        InactivaBoton(btn);
        $(".form-control").removeClass('is-invalid');
        if ($('#fecCompraNO').val() == '') {
            $('#fecCompraNO').addClass('is-invalid');
            mjeError('Fecha de compra no puede estar vacía');
        } else if ($('#fecVenceNO').val() == '') {
            $('#fecVenceNO').addClass('is-invalid');
            mjeError('Fecha de Vencimiento no puede estar vacía');
        } else if ($('#fecVenceNO').val() < $('#fecCompraNO').val()) {
            $('#fecVenceNO').addClass('is-invalid');
            mjeError('Fecha de Vencimiento no puede ser menor a la de compra');
        } else if ($('#slcMetPago').val() == '0') {
            $('#slcMetPago').addClass('is-invalid');
            mjeError('Metodo de pago no puede estar vacío');
        } else if ($('#slcFormaPago').val() == '0') {
            $('#slcFormaPago').addClass('is-invalid');
            mjeError('Forma de pago no puede estar vacía');
        } else if ($('#slcProcedencia').val() == '0') {
            $('#slcProcedencia').addClass('is-invalid');
            mjeError('Procedencia no puede estar vacía');
        } else if ($('#slcTipoOrg').val() == '0') {
            $('#slcTipoOrg').addClass('is-invalid');
            mjeError('Tipo de organización no puede estar vacía');
        } else if ($('#slcRegFiscal').val() == '0') {
            $('#slcRegFiscal').addClass('is-invalid');
            mjeError('Regimen fiscal no puede estar vacío');
        } else if ($('#slcRespFiscal').val() == '0') {
            $('#slcRespFiscal').addClass('is-invalid');
            mjeError('Responsabilidad fiscal no puede estar vacío');
        } else if ($('#slcTipoDoc').val() == '0') {
            $('#slcTipoDoc').addClass('is-invalid');
            mjeError('Tipo de documento no puede estar vacío');
        } else if ($('#numNoDoc').val() == '') {
            $('#numNoDoc').addClass('is-invalid');
            mjeError('Número de documento no puede estar vacío');
        } else if ($('#id_tercero_api').val() == '0') {
            $('#numNoDoc').addClass('is-invalid');
            mjeError('El tercero no se encuentra regsitrado, Remitirse al modulos de terceros para registrar el tercero');
        } else if ($('#txtNombreRazonSocial').val() == '') {
            $('#txtNombreRazonSocial').addClass('is-invalid');
            mjeError('Nombre o razón social no puede estar vacío');
        } else if ($('#txtCorreoOrg').val() == '') {
            $('#txtCorreoOrg').addClass('is-invalid');
            mjeError('Correo electrónico no puede estar vacío');
        } else if ($('#txtTelefonoOrg').val() == '') {
            $('#txtTelefonoOrg').addClass('is-invalid');
            mjeError('Teléfono no puede estar vacío');
        } else if ($('#slcPaisEmp').val() == '0') {
            $('#slcPaisEmp').addClass('is-invalid');
            mjeError('País no puede estar vacío');
        } else if ($('#slcDptoEmp').val() == '0') {
            $('#slcDptoEmp').addClass('is-invalid');
            mjeError('Departamento no puede estar vacío');
        } else if ($('#slcMunicipioEmp').val() == '0') {
            $('#slcMunicipioEmp').addClass('is-invalid');
            mjeError('Municipio no puede estar vacío');
        } else if ($('#txtDireccion').val() == '') {
            $('#txtDireccion').addClass('is-invalid');
            mjeError('Dirección no puede estar vacío');
        } else {
            $('input[name="txtDescripcion[]"]').each(function () {
                let val = $(this).val();
                let row = $(this).closest('tr');
                let val_u = row.find('input[name="numValorUnitario[]"]').val();
                let cant = row.find('input[name="numCantidad[]"]').val();
                let val_u1 = parseInt(val_u);
                let cant1 = parseInt(cant);
                $('#formAddFacturaNO input').removeClass('is-invalid')
                if (val == '') {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('is-invalid');
                    mjeError('Detalle de la factura no puede estar vacío');
                } else if (val_u == '' || val_u1 <= 0) {
                    aprobar = 0;
                    $(row.find('input[name="numValorUnitario[]"]')).focus();
                    $(row.find('input[name="numValorUnitario[]"]')).addClass('is-invalid');
                    mjeError('Valor unitario debe ser mayor a cero');
                } else if (cant == '' || cant1 <= 0) {
                    aprobar = 0;
                    $(row.find('input[name="numCantidad[]"]')).focus();
                    $(row.find('input[name="numCantidad[]"]')).addClass('is-invalid');
                    mjeError('Cantidad debe ser mayor a cero');
                }
                if (aprobar == 0) {
                    ActivaBoton(btn);
                    return false;
                }
            });
            if (aprobar == 1) {
                let val_fac = $('input[name="valfac"]').val();
                let val_fac1 = parseInt(val_fac);
                if (val_fac1 <= 0 || val_fac == '') {
                    mjeError('Valor total de la factura debe ser mayor a cero <div class="alert alert-warning scaled w-100"><i>Debe calcular impuestos para  generar el valor total de la factura</i></div>');
                } else {
                    let datos = $('#formAddFacturaNO').serialize() + '&id_fno=' + opcion;
                    let url, estado;
                    url = 'registrar/new_factura_no.php';
                    if (Number(opcion) == 0) {
                        estado = 'Registrada';
                    } else {
                        estado = 'Actualizada';
                    }
                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: datos,
                        success: function (r) {
                            if (r == '1') {
                                let id = 'tableFacurasNoObligados';
                                reloadtable(id);
                                $('#divModalForms').modal('hide');
                                mje('Documento ' + estado + ' correctamente');
                            } else {
                                mjeError(r);
                            }
                        }
                    });
                }
            }
        }
        ActivaBoton(btn);
        return false;
    });
    //actualizar factura no obligado
    $('#divForms').on('click', '#btnUpFacturaNO', function () {
        var aprobar = 1;
        $(".form-control").removeClass('border-danger');
        if ($('#fecCompraNO').val() == '') {
            $('#fecCompraNO').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha de compra no puede estar vacía');
        } else if ($('#slcMetPago').val() == '0') {
            $('#slcMetPago').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Metodo de pago no puede estar vacío');
        } else if ($('#slcFormaPago').val() == '0') {
            $('#slcFormaPago').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Forma de pago no puede estar vacía');
        } else if ($('#slcProcedencia').val() == '0') {
            $('#slcProcedencia').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Procedencia no puede estar vacía');
        } else if ($('#slcTipoOrg').val() == '0') {
            $('#slcTipoOrg').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Tipo de organización no puede estar vacía');
        } else if ($('#slcRegFiscal').val() == '0') {
            $('#slcRegFiscal').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Regimen fiscal no puede estar vacío');
        } else if ($('#slcTipoDoc').val() == '0') {
            $('#slcTipoDoc').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Tipo de documento no puede estar vacío');
        } else if ($('#numNoDoc').val() == '') {
            $('#numNoDoc').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Número de documento no puede estar vacío');
        } else if ($('#slcRespFiscal').val() == '') {
            $('#slcRespFiscal').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Responsabilidad fiscal no puede estar vacío');
        } else if ($('#txtNombreRazonSocial').val() == '') {
            $('#txtNombreRazonSocial').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Nombre o razón social no puede estar vacío');
        } else if ($('#txtCorreoOrg').val() == '') {
            $('#txtCorreoOrg').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Correo electrónico no puede estar vacío');
        } else if ($('#txtTelefonoOrg').val() == '') {
            $('#txtTelefonoOrg').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Teléfono no puede estar vacío');
        } else if ($('#slcPaisEmp').val() == '0') {
            $('#slcPaisEmp').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('País no puede estar vacío');
        } else if ($('#slcDptoEmp').val() == '0') {
            $('#slcDptoEmp').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Departamento no puede estar vacío');
        } else if ($('#slcCiudadEmp').val() == '0') {
            $('#slcCiudadEmp').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ciudad no puede estar vacío');
        } else if ($('#txtDireccion').val() == '') {
            $('#txtDireccion').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Dirección no puede estar vacío');
        } else if ($('#validaDetalles').val() == '0') {
            $('#txtDetalleFactura').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Detalle de la factura no puede estar vacío');
        } else if ($('#numValBase').val() == '' || parseInt($('#numValBase').val()) <= 0) {
            $('#numValBase').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor base debe ser mayor a cero');
        } else if ($('#numValIva').val() == '' || parseInt($('#numValIva').val()) < 0) {
            $('#numValIva').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor IVA debe ser mayor o igual a cero');
        } else {
            if ($('.porimpuesto').length > 0) {
                $('input[type=number]').each(function () {
                    var min = parseInt($(this).attr('min'));
                    var max = parseInt($(this).attr('max'));
                    var val = $(this).val().length ? parseInt($(this).val()) : 'NO';
                    $(this).removeClass('border-danger');
                    if (val == 'NO') {
                        aprobar = 0;
                        $(this).focus();
                        $(this).addClass('border-danger');
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (max) + ' válido');
                    } else if (val <= min || val >= max) {
                        aprobar = 0;
                        $(this).focus();
                        $(this).addClass('border-danger');
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('El valor debe estar entre ' + min + ' y ' + (max));
                    }
                    if (aprobar == 0) {
                        return false;
                    }
                });
            }
            $('input[type=text]').each(function () {
                var val = $(this).val();
                $(this).removeClass('border-danger');
                if (val == '') {
                    aprobar = 0;
                    $(this).focus();
                    $(this).addClass('border-danger');
                    mjeError('Detalle de la factura no puede estar vacío');
                }
                if (aprobar == 0) {
                    return false;
                }
            });
            if (parseInt($('#numValIva').val()) > 0 && ($('input[name=iva]').val() <= 0 || !($('input[name=iva]').length))) {
                $('#numValIva').addClass('border-danger');
                mjeError('El valor IVA debe ser mayor a cero o debe desmarcar el IVA');
                aprobar = 0;
            }
            if (aprobar == 1) {
                let datos = $('#formUpFacturaNO').serialize();
                $.ajax({
                    type: 'POST',
                    url: 'actualizar/up_factura_no.php',
                    data: datos,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableFacurasNoObligados';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            mje('Documento procesado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        }
        return false;
    });
    $('#divModalForms').on('change', '.form-check-input', function () {
        var id = $(this).attr('id');
        if ($(this).prop('checked') == true) {
            let input = '<input type="number" name="' + id + '" class="form-control form-control-sm altura porimpuesto" min="0" max="100" placeholder="% Ej: 4.5">';
            $('#div' + id).html(input);
        } else {
            $('#div' + id).html('');
        }
    });
    $('#tableFacurasNoObligados').on('click', '.modificar', function () {
        var id = $(this).attr('value');
        FormDocSoporte(id);
    });
    $('#tableFacurasNoObligados').on('click', '.verDocumento', function () {
        var id = $(this).attr('value');
        FormDocSoporte(id);
        $('#divModalForms').on('shown.bs.modal', function () {
            $('#btnFacturaNO').remove();

        });
    });
    $('#tableFacurasNoObligados').on('click', '.borrar', function () {
        var id = $(this).attr('value');
        Swal.fire({
            title: "¿Confirma anulación de documento?, Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "eliminar/del_factura_no.php",
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            $('#tableFacurasNoObligados').DataTable().ajax.reload();
                            mje('Documento eliminado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#divModalConfDel').on('click', '#btnConfirDelFacNoOblig', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_factura_no.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableFacurasNoObligados';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Factura No obligado eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    // Agregar fila a la tabla de facturas no obligados
    $('#divModalForms').on('click', '#btnAddRowFNO', function () {
        let id = 'tableFacNoObliga';
        let row = '<tr>' +
            '<td class="border" colspan="1">' +
            '<input type="text" name="txtCod[]" class="form-control form-control-sm bg-plain">' +
            '</td>' +
            '<td class="border" colspan="7">' +
            '<input type="text" name="txtDescripcion[]" class="form-control form-control-sm  bg-plain">' +
            '</td>' +
            '<td class="border" colspan="2">' +
            '<input type="number" name="numValorUnitario[]" class="form-control form-control-sm valfno bg-plain">' +
            '</td>' +
            '<td class="border" colspan="1">' +
            '<input type="number" name="numCantidad[]" class="form-control form-control-sm valfno bg-plain">' +
            '</td>' +
            '<td class="border w-10" colspan="1">' +
            '<select name="numPIVA[]" class="form-control form-control-sm valfno bg-plain">' +
            '<option value="0">0.00</option>' +
            '<option value="5">5.00</option>' +
            '<option value="19">19.00</option>' +
            '</select>' +
            '</td>' +
            '<td class="border" colspan="2">' +
            '<div class="form-control form-control-sm bg-plain valIVA"></div>' +
            '<input type="hidden" name="valIva[]">' +
            '</td>' +
            '<td class="border" colspan="1">' +
            '<input type="number" name="numPDcto[]" class="form-control form-control-sm valfno bg-plain">' +
            '</td>' +
            '<td class="border" colspan="2">' +
            '<div class="form-control form-control-sm bg-plain valDcto"></div>' +
            '<input type="hidden" name="numValDcto[]">' +
            '</td>' +
            '<td class="border" colspan="2">' +
            '<div class="form-control form-control-sm bg-plain valTotal"></div>' +
            '<input type="hidden" name="numValorTotal[]">' +
            '</td>' +
            '<td class="border text-center" colspan="1">' +
            '<button type="button" class="btn btn-sm btn-outline-danger btnDelRowFNO"  title="Eliminar fila de este producto">' +
            '<span class="fas fa-minus-square fa-lg"></span>' +
            '</button>' +
            '</td>' +
            '</tr>';
        $('#' + id + ' tbody').append(row);
    });
    $('#divModalForms').on('click', '.btnDelRowFNO', function () {
        $(this).closest('tr').remove();
        calcSubTotal();
        calcImpuestos();
    });
    //calcular valor total de la factura no obligado
    $('#divModalForms').on('input', '.valfno', function () {
        var row = $(this).closest('tr');
        var cantidad = Number(row.find('input[name="numCantidad[]"]').val());
        var valorUnitario = Number(row.find('input[name="numValorUnitario[]"]').val());
        var porcIva = parseFloat(row.find('select[name="numPIVA[]"]').val());
        var porcDcto = parseFloat(row.find('input[name="numPDcto[]"]').val());
        if (porcDcto > 0) {
            $('#dctoCondicionado').prop('checked', false)
            $('input[name="ifDcto"]').attr('disabled', true);
            $('input[name="ifDcto"]').removeClass('bg-plain');
            $('input[name="ifDcto"]').addClass('div-gris');
            $('input[name="ifDcto"]').val(0);
        }
        if (porcIva > 0) {
            $('select[name="ifIVA"]').val(0);
            $('input[name="valIVAfno"]').val(0);
            $('.valIVAfno').html(pesos('$0.00'));
        }

        cantidad = cantidad > 0 ? cantidad : 0;
        valorUnitario = valorUnitario > 0 ? valorUnitario : 0;
        porcIva = porcIva > 0 ? porcIva : 0;
        porcDcto = porcDcto > 0 ? porcDcto : 0;
        var bruto = cantidad * valorUnitario;
        var valDcto = bruto * (porcDcto / 100);
        var baseImp = bruto - valDcto;
        var valiva = baseImp * (porcIva / 100);
        row.find('input[name="valIva[]"]').val(valiva);
        row.find('.valIVA').html(pesos(valiva, 2));
        row.find('input[name="numValDcto[]"]').val(valDcto);
        row.find('.valDcto').html(pesos(valDcto, 2));
        calcSubTotal();
        calcImpuestos();
        return false;
    });
    $('#divModalForms').on('change', '.pImpToCalc', function () {
        calcSubTotal();
        calcImpuestos();
        return false;
    });
    var calcSubTotal = function () {
        var total = iva = descuento = 0;
        var row, cantidad, valorUnitario, porcIva, porcDcto, baseImp, valIva, valDcto, valTotal, bruto;

        $('input[name="numValorUnitario[]"]').each(function () {
            row = $(this).closest('tr');
            cantidad = Number(row.find('input[name="numCantidad[]"]').val());
            valorUnitario = Number(row.find('input[name="numValorUnitario[]"]').val());
            porcIva = parseFloat(row.find('select[name="numPIVA[]"]').val());
            porcDcto = parseFloat(row.find('input[name="numPDcto[]"]').val());
            cantidad = cantidad > 0 ? cantidad : 0;
            valorUnitario = valorUnitario > 0 ? valorUnitario : 0;
            porcIva = porcIva > 0 ? porcIva : 0;
            porcDcto = porcDcto > 0 ? porcDcto : 0;
            bruto = Number(cantidad) * Number(valorUnitario);
            valDcto = Number(bruto) * (Number(porcDcto) / 100);
            baseImp = Number(bruto) - Number(valDcto);
            valIva = Number(baseImp) * (Number(porcIva) / 100);
            iva = Number(iva) + Number(valIva);
            descuento = Number(descuento) + Number(valDcto);
            total = Number(total) + Number(baseImp);
            row.find('input[name="numValorTotal[]"]').val(baseImp);
            row.find('.valTotal').html(pesos(baseImp, 2));

        });
        $('input[name="valSubTotal"]').val(total);
        $('.valSubTotal').html(pesos(total, 2));
        if (parseInt($('select[name="ifIVA"]').val()) <= 0) {
            $('input[name="valIVAfno"]').val(iva);
            $('.valIVAfno').html(pesos(iva, 2));
        } else {
            let valoriva = total * (parseFloat($('select[name="ifIVA"]').val()) / 100);
            $('input[name="valIVAfno"]').val(valoriva);
            $('.valIVAfno').html(pesos(valoriva, 2));
        }
        if (descuento == 0) {
            descuento = parseFloat($('input[name="valSubTotal"]').val()) * (parseFloat($('input[name="ifDcto"]').val()) / 100);
        }
        $('input[name="valDctofno"]').val(descuento);
        $('.valDctofno').html(descuento > 0 ? '-' + pesos(descuento, 2) : pesos(descuento, 2));
    };
    var calcImpuestos = function () {
        let stotal = $('input[name="valSubTotal"]').val();
        let iva = $('input[name="valIVAfno"]').val();
        let dcto = $('input[name="valDctofno"]').val();
        let prtefte = parseFloat($('select[name="prtefte"]').val());
        let pretiva = parseFloat($('select[name="pretiva"]').val());
        if (parseInt(prtefte) < 0 || parseInt(pretiva) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('El valor del porcentaje de Impuestos debe ser mayor o igual a cero.');
            return false;
        }
        stotal > 0 ? stotal : 0;
        iva > 0 ? iva : 0;
        prtefte > 0 ? prtefte : 0;
        pretiva > 0 ? pretiva : 0;
        let descto;
        if (parseInt($('input[name="ifDcto"]').val()) <= 0 || $('input[name="ifDcto"]').val() == '') {
            descto = dcto;
        } else {
            descto = 0;
        }
        let val_retefuente = Number(stotal) * Number(prtefte) / 100;
        let val_reteiva = Number(iva) * Number(pretiva) / 100;
        let val_fac = Number(stotal) + Number(iva) - Number(val_retefuente) - Number(val_reteiva) - Number(descto);
        $('input[name="valprtefte"]').val(val_retefuente);
        $('input[name="valpretiva"]').val(val_reteiva);
        $('input[name="valfac"]').val(val_fac);
        $('.valprtefte').html(pesos(val_retefuente, 2));
        $('.valpretiva').html(pesos(val_reteiva, 2));
        $('.valfac').html('<b>' + pesos(val_fac, 2) + '</b>');
    };
    $('#divModalForms').on('blur', '#numNoDoc', function () {
        let noDoc = $('#numNoDoc').val();
        $.ajax({
            url: 'datos/listar/tercero_noobligado.php',
            type: 'POST',
            dataType: 'json',
            data: { noDoc: noDoc },
            success: function (r) {
                if (r.status == 1) {
                    $('#slcProcedencia').val(r.procedencia);
                    $('#slcTipoOrg').val(r.tipo_org);
                    $('#slcRegFiscal').val(r.reg_fiscal);
                    $('#slcRespFiscal').val(r.resp_fiscal);
                    $('#slcTipoDoc').val(r.id_tdoc);
                    $('#txtNombreRazonSocial').val(r.nombre);
                    $('#txtCorreoOrg').val(r.correo);
                    $('#txtTelefonoOrg').val(r.telefono);
                    $('#slcPaisEmp').val(r.id_pais);
                    $('#slcDptoEmp').val(r.id_dpto);
                    $('#txtDireccion').val(r.direccion);
                    $('#id_tercero_api').val(r.id_tercero_api);
                    var dpto = r.id_dpto;
                    var city = r.id_municipio;
                    $.ajax({
                        type: 'POST',
                        url: window.urlin + '/nomina/empleados/registrar/slcmunicipio.php',
                        data: { dpto: dpto },
                        success: function (data) {
                            $('#slcMunicipioEmp').html(data);
                            $('#slcMunicipioEmp').val(city);
                        }
                    });
                }
            }
        });
    });
    $('#divModalForms').on('change', '#dctoCondicionado', function () {
        if ($(this).prop('checked') == true) {
            $('input[name="numPDcto[]"]').val(0)
            $('.valDcto').html('$0.00');
            $('.valDctofno').html('$0.00');
            $('input[name="valDctofno"]').val(0);
            $('input[name="ifDcto"]').attr('disabled', false);
            $('input[name="ifDcto"]').removeClass('div-gris');
            $('input[name="ifDcto"]').addClass('bg-plain');
            calcSubTotal();
            calcImpuestos();
        } else {
            $('input[name="ifDcto"]').val(0);
            $('input[name="valDctofno"]').val(0);
            $('.valDctofno').html('$0.00');
            $('input[name="ifDcto"]').attr('disabled', true);
            $('input[name="ifDcto"]').removeClass('bg-plain');
            $('input[name="ifDcto"]').addClass('div-gris');
            calcSubTotal();
            calcImpuestos();
        }
        return false;
    });
    $('#divModalForms').on('input', 'input[name="ifDcto"]', function () {
        calcSubTotal();
        let dcto = parseFloat($(this).val());
        var stotal = parseFloat($('input[name="valSubTotal"]').val());
        var valor = 0;
        valor = Number(stotal) * dcto / 100;
        $('.valDctofno').html(valor > 0 ? '-' + pesos(valor, 2) : '$0.00');
        $('input[name="valDctofno"]').val(valor);
        calcImpuestos();
    });
    $('#divModalForms').on('change', 'select[name="ifIVA"]', function () {
        $('select[name="numPIVA[]"]').val(0);
        $('input[name="valIva[]"]').val(0);
        $('.valIVA').html('$0.00');
        let dcto = parseFloat($('select[name="ifIVA"]').val());
        var stotal = parseFloat($('input[name="valSubTotal"]').val());
        var valor = 0;
        if (dcto > 0) {
            valor = Number(stotal) * dcto / 100;
            $('.valIVAfno').html(valor > 0 ? pesos(valor, 2) : '$0.00');
            $('input[name="valIVAfno"]').val(valor);
        } else {
            $('.valIVAfno').html('$0.00');
            $('input[name="valIVAfno"]').val(0);
        }
        calcImpuestos();
    });
})(jQuery);

function EnviaDocSoporte2(boton, tipo) {
    var id = boton.value;
    boton.disabled = true;
    boton.value = "";
    boton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    if (tipo == '1') {
        url = 'datos/soporte/anula_factura_no.php';
    } else {
        url = 'datos/soporte/enviar_factura_no.php';
    }
    $.ajax({
        type: "POST",
        url: url,
        data: { id: id },
        dataType: "json",
        success: function (response) {
            if (response[0].value == "ok") {
                boton.innerHTML = '<span class="fas fa-thumbs-up fa-lg"></span>';
                $('#tableFacurasNoObligados').DataTable().ajax.reload();
                mje("Documento enviado correctamente");
            } else {
                boton.disabled = false;
                boton.value = id;
                boton.innerHTML = '<span class="fas fa-paper-plane fa-lg"></span>';
                function mjeError(titulo, mensaje) {
                    Swal.fire({
                        title: titulo,
                        html: mensaje, // Renderiza el HTML en el mensaje
                        icon: "error"
                    });
                }
                mjeError('', response[0].msg);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error en la solicitud AJAX:", error);
        }
    });
    ActivaBoton(boton);
    return false
};

const VerSoporteElectronico2 = (id) => {
    fetch("datos/soporte/ver_html.php", {
        method: "POST",
        body: JSON.stringify({ id: id }),
    })
        .then((response) => response.json())
        .then((response) => {
            console.log(response);
            if (response[0].value == "ok") {
                var url = "https://api.taxxa.co/documentGet.dhtml?hash=" + response[0].msg;
                url = url.replace(/["']/g, "");
                var win = window.open(url, "_blank");
                win.focus();
            } else {
                mjeError(response[0].msg);
            }
        })
        .catch((error) => {
            console.log("Error:");
        });
};

const AnulaDocSoporte = (id) => {
    //confimar anulación de documento
    Swal.fire({
        title: "¿Confirma anulación de documento?, Esta acción no se puede deshacer",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#00994C",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si!",
        cancelButtonText: "NO",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: "POST",
                url: "registrar/new_facno_anula.php",
                data: { id: id },
                success: function (r) {
                    if (r == 'ok') {
                        $('#tableFacurasNoObligados').DataTable().ajax.reload();
                        mje('Documento Anulado correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
};