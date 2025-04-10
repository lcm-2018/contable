(function ($) {
    function hideModalEspera() {
        $('#divModalEspera').modal('hide');
        $('.modal-backdrop').remove();
    }
    $("#btnLiqNom").click(function () {
        let mes = $("#slcMesLiqNom").val();
        if (mes == '00') {
            $('#divModalErrorliqnom').modal('show');
            $('#divMsgErrorliqnom').html("Debe elegir MES");
            return false;
        }
        let dliqnom = $("#formLiqNomina").serialize();
        var url = window.urlin + '/nomina/liquidar_nomina/liq_nom_public.php';
        $('#divModalEspera').modal('show');
        $.ajax({
            type: 'POST',
            url: url,
            data: dliqnom,
            success: function (r) {
                setTimeout(function () {
                    hideModalEspera();
                    if (r === '0') {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html("No hay mas empleados para liquidar en este mes");
                    } else {
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html(r);
                    }
                }, 500);
            }
        });
        return false;
    });
    $('#slcMesLiqNom').on('input', function () {
        let mes = $(this).val();
        window.location = window.urlin + '/nomina/liquidar_nomina/listempliquidar.php?mes=' + mes;
        return false;
    });
    $('#slcMesLiqNomEmp').on('input', function () {
        let mes = $(this).val();
        window.location = window.urlin + '/nomina/liquidar_nomina/liqxempleado.php?mes=' + mes;
        return false;
    });
    $('#slcLiqEmpleado').on('input', function () {
        let mes = $('#slcMesLiqNomEmp').val();
        let emp = $(this).val();
        window.location = window.urlin + '/nomina/liquidar_nomina/liqxempleado.php?mes=' + mes + '&emp=' + emp;
        return false;
    });
    $("#btnLiqPrima").click(function () {
        var valida = 0;
        let p = Number($("#tipo").val());
        $('input[type="checkbox"]').each(function () {
            if ($(this).is(":checked")) {
                valida++;
            }
        });
        if (valida == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir al menos un empleado");
        } else {
            let datas = $("#formLiqNomina").serialize();
            if (parseInt($('#caracter_empresa').val()) == 2) {
                var url = window.urlin + '/nomina/liquidar_nomina/liq_prima_public.php';
            } else {
                var url = window.urlin + '/nomina/liquidar_nomina/liq_prima_public.php';
            }
            if (p == 2 && parseInt($('#caracter_empresa').val()) == 2) {
                var url = window.urlin + '/nomina/liquidar_nomina/liq_prima_navidad_public.php';
            }
            $('#divModalEspera').modal('show');
            $.ajax({
                type: 'POST',
                url: url,
                data: datas,
                success: function (r) {
                    setTimeout(function () {
                        hideModalEspera();
                        if (r.trim() === 'ok') {
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Proceso realizado con éxito");
                            setTimeout(function () { }, 1000);
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }, 500);
                }
            });
        }
        return false;
    });
    $("#btnLiqCesantias").click(function () {
        var valida = 0;
        $('input[type="checkbox"]').each(function () {
            if ($(this).is(":checked")) {
                valida++;
            }
        });
        if (valida == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir al menos un empleado");
        } else {
            let datas = $("#formLiqCesantias").serialize();
            var url = window.urlin + '/nomina/liquidar_nomina/liq_cesantias_public.php';
            $('#divModalEspera').modal('show');
            $.ajax({
                type: 'POST',
                url: url,
                data: datas,
                success: function (r) {
                    setTimeout(function () {
                        hideModalEspera();
                        if (r.trim() === 'ok') {
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Proceso realizado con éxito");
                            setTimeout(function () { }, 1000);
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }, 500);
                }
            });
        }
        return false;
    });
    $("#btnLiqCesant").click(function () {
        let dliqnom = $("#formLiqNomina").serialize();
        $.ajax({
            type: 'POST',
            url: window.urlin + '/nomina/liquidar_nomina/liq_cesantias.php',
            data: dliqnom,
            success: function (r) {
                if (r == '0') {
                    $('#divModalError').modal('show');
                    $('#btnDetallesLiq').attr('href', 'detalles_cesantias.php');
                } else {
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html(r);
                }
            }
        });
        return false;
    });
    $("#btnReporNomElec").click(function () {
        fec = $('#fecLiqNomElec').val();
        mesne = $('#mesNomElec').val();
        $('#divModalEspera').modal('show');
        $.ajax({
            type: 'POST',
            url: window.urlin + '/nomina/enviar/soportenomelec.php',
            dataType: 'json',
            data: { fec: fec, mesne: mesne },
            success: function (r) {
                setTimeout(function () {
                    hideModalEspera();
                    if (r.msg == '1') {
                        $('#divModalConfDel').modal('show');
                        $('#divMsgConfdel').html("PROCESADO:<br>" + r.procesados + "<br><br>SIN PROCESAR Y/O ERRORES:" + r.incorrec + "<br>" + r.error);
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgDone').html(r);
                    }
                }, 500);
            }
        });
        return false;
    });
    var showError = function (id) {
        $('#e' + id).show();
        $('#' + id).focus();
        setTimeout(function () {
            $('#e' + id).fadeOut(600);
        }, 800);
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
    $("#btnLiqNomXempleado").click(function () {
        let empleado = $('#slcLiqEmpleado').val();
        let dlab = parseInt($('#numDiasLab').val());
        let dincap = 0;
        let dvac = 0;
        let dlic = 0;
        let pSalud = $('#numProvSalud').val();
        let ppension = $('#numProvPension').val();
        let parl = $('#numProvARL').val();
        let psena = $('#numProvSENA').val();
        let picbf = $('#numProvICBF').val();
        let pcomfam = $('#numProvCOMFAM').val();
        let pcesan = $('#numProvCesan').val();
        let picesan = $('#numProvIntCesan').val();
        let pvac = $('#numProvVac').val();
        if (empleado === '0') {
            let id = 'slcLiqEmpleado';
            showError(id);
            return false;
        } else if (dlab < 0 || $('#numDiasLab').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Dias laborados debe ser mayor o igual a  0');
            return false;
        } else if ($('#numAportSalud').val() === '' || $('#numAportPension').val() === '') {
            $('#divModalError').modal('show');
            $('#numAportSalud').focus();
            $('#divMsgError').html('Salud y/o Pensión no pueden estar vacios');
            return false;
        } else if (parseInt($('#numValDiasLab').val()) < 0 || $('#numValDiasLab').val() === '') {
            $('#numValDiasLab').focus();
            let id = 'numValDiasLab';
            showError(id);
            return false;
        } else if (parseInt($('#numSalNeto').val()) <= 0 || $('#numSalNeto').val() === '') {
            $('#numSalNeto').focus();
            let id = 'numSalNeto';
            showError(id);
            return false;
        }
        if ($('#divEmbargos').length > 0) {
            if ($('#slcEmbargos').val() === '0') {
                let id = 'slcEmbargos';
                showError(id);
                return false;
            } else if ($('#numDeduccionesEmb').val() === '' || parseInt($('#numDeduccionesEmb').val()) <= 0) {
                let id = 'numDeduccionesEmb';
                showError(id);
                return false;
            }
        }
        if ($('#divLibranzas').length > 0) {
            if ($('#slcLibranzas').val() === '0') {
                let id = 'slcLibranzas';
                showError(id);
                return false;
            } else if ($('#numDeduccionesLib').val() === '' || parseInt($('#numDeduccionesLib').val()) <= 0) {
                let id = 'numDeduccionesLib';
                showError(id);
                return false;
            }
        }
        if ($('#divSindicatos').length > 0) {
            if ($('#slcSindicato').val() === '0') {
                let id = 'slcSindicato';
                showError(id);
                return false;
            } else if ($('#numDeduccionesSind').val() === '' || parseInt($('#numDeduccionesSind').val()) <= 0) {
                let id = 'numDeduccionesSind';
                showError(id);
                return false;
            }
        }
        if ($('#divIncapacidad').length > 0) {
            let vemp = parseInt($('#numValIncapEmpresa').val());
            let veps = parseInt($('#numValIncapEPS').val());
            let varl = parseInt($('#numValIncapARL').val());
            let tincap = vemp + veps + varl;
            if ($('#slcIncapacidad').val() === '0') {
                let id = 'slcIncapacidad';
                showError(id);
                return false;
            } else if ($('#numDiasIncap').val() === '0' || $('#numDiasIncap').val() === '') {
                let id = 'numDiasIncap';
                showError(id);
                return false;
            } else if ($('#numValIncapEmpresa').val() === '' || $('#numValIncapEPS').val() === '' || $('#numValIncapARL').val() === '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Los valores de Incapacidad deben ser mayor o igual a cero');
                return false;
            } else if (tincap <= 0) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Valor total de Incapacidad debe ser mayor a cero');
                return false;
            } else if ($('#datFecInicioInc').val() === '' || $('#datFecFinInc').val() === '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fechas de Incapacidad no pueden estar vacias');
                return false;
            }
            dincap = parseInt($('#numDiasIncap').val());
        }
        if ($('#divVacaciones').length > 0) {
            if ($('#slcVacaciones').val() === '0') {
                let id = 'slcVacaciones';
                showError(id);
                return false;
            } else if ($('#numDiasVac').val() === '0' || $('#numDiasVac').val() === '') {
                let id = 'numDiasVac';
                showError(id);
                return false;
            } else if ($('#numValVac').val() === '' || $('#numValVac').val() === '0') {
                let id = 'numValVac';
                showError(id);
                return false;
            } else if ($('#datFecInicioVacs').val() === '' || $('#datFecFinVacs').val() === '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fechas de Vacaciones no pueden estar vacias');
                return false;
            }
            dvac = parseInt($('#numDiasVac').val());
        }
        if ($('#divLicencias').length > 0) {
            if ($('#slcLicencias').val() === '0') {
                let id = 'slcLicencias';
                showError(id);
                return false;
            } else if ($('#numDiasLic').val() === '0' || $('#numDiasLic').val() === '') {
                let id = 'numDiasLic';
                showError(id);
                return false;
            } else if ($('#numValLica').val() === '' || $('#numValLica').val() === '0') {
                let id = 'numValLica';
                showError(id);
                return false;
            } else if ($('#datFecInicioLics').val() === '' || $('#datFecInicioLics').val() === '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fechas de Licencia no pueden estar vacias');
                return false;
            }
            dlic = parseInt($('#numDiasLic').val());
        }
        let tdias = dincap + dvac + dlic + dlab;
        if (tdias > 30) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Dias liquidados debe ser menor o igual a 30');
        } else if (pSalud === '' || ppension === '' || parl === '' || psena === '' || picbf === '' || pcomfam === '' || pcesan === '' || picesan === '' || pvac === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe diligenciar todos los Provisionamientos');
        } else {
            let dliqind = $("#formLiqNominaEmpleado").serialize();
            $.ajax({
                type: 'POST',
                url: 'liqnomindividual.php',
                data: dliqind,
                success: function (r) {
                    if (r === '1') {
                        $("#formLiqNominaEmpleado")[0].reset();
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Empleado liquidado correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });

    $('#selectAll').change(function () {
        if ($(this).prop('checked')) {
            $('#selectAll').attr('title', 'Desmarcar todos');
        } else {
            $('#selectAll').attr('title', 'Marcar todos');
        }

        $('input[type=checkbox]').prop('checked', $(this).is(':checked'));
    });
    $('.diaslab > input[type=number]').keyup(function () {
        let dato = $(this).attr('name').split('_');
        let id = dato[1];
        let dlab = parseInt($(this).val()) + parseInt($('#dayIncap_' + id).val()) + parseInt($('#dayLic_' + id).val()) + parseInt($('#dayLicNR_' + id).val()) + parseInt($('#dayVac_' + id).val());
        if (dlab > 30 || parseInt($(this).val()) < 0) {
            $(this).focus();
            $(this).val('');
        }
        if ($(this).val() === '') {
            $(this).val('0');
        }
    });
    $('.mesliquidado a').on('click', function () {
        let mes = $(this).attr('value');
        window.location = window.urlin + '/nomina/liquidar_nomina/detalles_nomina.php?mes=' + mes;
    });
    $('.periodospri a').on('click', function () {
        let per = $(this).attr('value');
        window.location = window.urlin + '/nomina/liquidar_nomina/detalles_prima.php?per=' + per;
    });
    $('.periodospri button').on('click', function () {
        window.location = window.urlin + '/nomina/liquidar_nomina/detalles_prima_navidad.php?per=2';
    });
    var reloadtable = function (nom) {
        $(document).ready(function () {
            var table = $('#' + nom).DataTable();
            table.ajax.reload();
        });
    };

    $(document).ready(function () {
        //dataTable Liquidar contratos
        $('#tableLiqContrato').DataTable({
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_lista_contratos.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'check' },
                { 'data': 'no_contrato' },
                { 'data': 'no_doc' },
                { 'data': 'nombre' },
                { 'data': 'fec_inicio' },
                { 'data': 'fec_termina' },
            ],
            "order": [
                [2, "desc"]
            ]
        });
        $('#tableLiqContrato').wrap('<div class="overflow" />');
        $('#tableNominas').DataTable({
            language: setIdioma,
            "ajax": {
                url: '../datos/listar/datos_lista_nomina.php',
                type: 'POST',
                dataType: 'json',
                data: function (d) {
                    d.anulados = $('#verAnulados').is(':checked') ? 1 : 0;
                    return d;
                },
            },
            "columns": [
                { 'data': 'id_nomina' },
                { 'data': 'descripcion' },
                { 'data': 'mes' },
                { 'data': 'tipo' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ]
        });
        $('#tableNominas').wrap('<div class="overflow" />');
        //dataTable Liquidación de vacaciones
        $('#tableLiqVacaciones').DataTable({
            language: setIdioma,
            "ajax": {
                url: '../datos/listar/liq_vacaciones.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'no_doc' },
                { 'data': 'nombre' },
                { 'data': 'fec_inicia' },
                { 'data': 'fec_fin' },
                { 'data': 'dias_liq' },
                { 'data': 'val_vac' },
                { 'data': 'val_pri_vac' },
                { 'data': 'val_bsp' },
                { 'data': 'val_brecrea' },
                { 'data': 'corte' },
                { 'data': 'anticipo' },
                { 'data': 'dias_hab' },
                { 'data': 'total' },
            ],
            "order": [
                [0, "desc"]
            ]
        });
        $('#tableLiqVacaciones').wrap('<div class="overflow" />');
        //table liquidar contratos 
        $('#dataTableDetallLiqContratos').DataTable({
            language: setIdioma,
        });
        $('#dataTableDetallLiqContratos').wrap('<div class="overflow" />');
        //dataTable retroactivos nomina 
        $('#tableRetroactivosNomina').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_retroactivo.php", function (he) {
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
                url: 'datos/listar/listar_retroactivos.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'inicia' },
                { 'data': 'termina' },
                { 'data': 'meses' },
                { 'data': 'incremento' },
                { 'data': 'observa' },
                { 'data': 'botones' },
            ],
            "order": [
                [2, "desc"]
            ]
        });
        $('#tableRetroactivosNomina').wrap('<div class="overflow" />');
        //dataTable retroactivos nomina liquidado
        $('#tableRetroactivosLiquidados').DataTable({
            language: setIdioma,
            "ajax": {
                url: '../datos/listar/listar_retroactivos.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'inicia' },
                { 'data': 'termina' },
                { 'data': 'meses' },
                { 'data': 'incremento' },
                { 'data': 'observa' },
                { 'data': 'botones' },
            ],
            "order": [
                [2, "desc"]
            ]
        });
        $('#tableRetroactivosLiquidados').wrap('<div class="overflow" />');
        //dataTable empleados retroactivos nomina 
        let id_reac = $('#id_retroactivo').val();
        $('#tableEmpleadosRetroactivo').DataTable({
            dom: setdom,
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/empleados_retroactivos.php',
                type: 'POST',
                dataType: 'json',
                data: { id_reac: id_reac },
            },
            "columns": [
                { 'data': 'check' },
                { 'data': 'doc' },
                { 'data': 'nombre' },
                { 'data': 'estado' },
                { 'data': 'sindicato' },
            ],
            "order": [
                [1, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableEmpleadosRetroactivo').wrap('<div class="overflow" />');
        $('#tableLiqVacs').DataTable({
            dom: setdom,
            language: setIdioma,
            "order": [
                [1, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableLiqVacs').wrap('<div class="overflow" />');
        let corte = $('#datFecCorte').val();
        $('#tableLiqPresSociales').DataTable({
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/empleados_inactivos.php',
                type: 'POST',
                dataType: 'json',
                data: { corte: corte },
            },
            "columns": [
                { 'data': 'check' },
                { 'data': 'no_doc' },
                { 'data': 'nombre' },
                { 'data': 'fec_termina' },
                { 'data': 'compensatorio' }
            ],
            "order": [
                [3, "desc"]
            ]
        });
        $('#tableLiqPresSociales').wrap('<div class="overflow" />');
        $('#tableLiqPrimaSv').DataTable({
            dom: setdom,
            language: setIdioma,
            "order": [
                [1, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableLiqPrimaSv').wrap('<div class="overflow" />');
        $('#tableParamLiq').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("liquidar_nomina/datos/registrar/form_reg_concepto.php", function (he) {
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
                url: 'liquidar_nomina/datos/listar/conceptos.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'concepto' },
                { 'data': 'valor' },
                { 'data': 'botones' },
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
        $('#tableParamLiq').wrap('<div class="overflow" />');
        $('#tableIncremento').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("liquidar_nomina/datos/registrar/form_reg_incremento.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: 'liquidar_nomina/datos/listar/incrementos.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                //{ 'data': 'id' },
                { 'data': 'porcentaje' },
                { 'data': 'fecha' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [1, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableIncremento').wrap('<div class="overflow" />');
        $('#tableTerceroNomina').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("liquidar_nomina/datos/registrar/form_reg_tercero.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],

            language: setIdioma,
            "ajax": {
                url: 'liquidar_nomina/datos/listar/terceros_nomina.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                { 'data': 'codigo' },
                { 'data': 'descripcion' },
                { 'data': 'nombre' },
                { 'data': 'nit' },
                //{ 'data': 'categoria' },
            ],
            "order": [
                [1, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableTerceroNomina').wrap('<div class="overflow" />');
        $('#tableCargosNomina').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("liquidar_nomina/datos/registrar/formadd_cargo.php", { id_cargo: 0 }, function (he) {
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
                url: 'liquidar_nomina/datos/listar/cargos_nomina.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                { 'data': 'id_cargo' },
                { 'data': 'codigo' },
                { 'data': 'cargo' },
                { 'data': 'grado' },
                { 'data': 'perfil_siho' },
                { 'data': 'nombramiento' },
                { 'data': 'acciones' },
            ],
            "columnDefs": [
                { "targets": op_caracter == '2' ? [] : [1, 3, 4, 5], "visible": false }
            ],
            "order": [
                [2, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableCargosNomina').wrap('<div class="overflow" />');
        $('#tableRubrosNomina').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("liquidar_nomina/datos/registrar/formadd_rubro.php", { id_relacion: 0 }, function (he) {
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
                url: 'liquidar_nomina/datos/listar/rubros_nomina.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                { 'data': 'id_relacion' },
                { 'data': 'nombre' },
                { 'data': 'cod_admin' },
                { 'data': 'nom_admin' },
                { 'data': 'cod_opera' },
                { 'data': 'nom_opera' },
                { 'data': 'acciones' },
            ],
            "order": [
                [1, "asc"]
            ], columnDefs: [{
                class: 'text-wrap',
                targets: [1, 3, 5]
            }],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableRubrosNomina').wrap('<div class="overflow" />');
        $('#tableCtaCtbNomina').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("liquidar_nomina/datos/registrar/formadd_cuenta.php", { id_causacion: 0 }, function (he) {
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
                url: 'liquidar_nomina/datos/listar/cuentas_nomina.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                { 'data': 'id_causacion' },
                { 'data': 'ccosto' },
                { 'data': 'tipo' },
                { 'data': 'nom_tipo' },
                { 'data': 'cuenta' },
                { 'data': 'nom_cta' },
                { 'data': 'acciones' },
            ],
            "order": [
                [1, 2, 3, "asc"]
            ], columnDefs: [{
                class: 'text-wrap',
                targets: [1, 3, 5]
            }],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableCtaCtbNomina').wrap('<div class="overflow" />');
        $('#tableVigencia').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                }
            }],

            language: setIdioma,
            "ajax": {
                url: 'liquidar_nomina/datos/listar/vigencias.php',
                type: 'POST',
                dataType: 'json'
            },
            "columns": [
                { 'data': 'vigencia' },
                { 'data': 'botones' },
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
        $('#tableVigencia').wrap('<div class="overflow" />');
    });
    $('#dataTableDetallLiqContratos tbody').on('dblclick', 'tr', function () {
        let table = $('#dataTableDetallLiqContratos').DataTable();
        if ($(this).hasClass('selecionada')) {
            $(this).removeClass('selecionada');
        } else {
            table.$('tr.selecionada').removeClass('selecionada');
            $(this).addClass('selecionada');
        }
    });
    $('#btnLiqContratos').on('click', function () {
        let data = $('#form_liq_contrato').serialize();
        $.ajax({
            type: 'POST',
            url: 'registrar/new_liq_contratos.php',
            data: data,
            success: function (r) {
                if (r === '1') {
                    let id = 'tableLiqContrato';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html('Liquidado correctamente <br><a class="btn btn-link" href="detalles_contratos_liq.php">Detalles Contratos liquidados</a>');
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r + '<br><a class="btn btn-link" href="detalles_contratos_liq.php">Detalles Contratos liquidados</a>');
                }
            }
        });
    });
    $('#dataTableDetallLiqContratos').on('click', '.reporte', function () {
        let id_lc = $(this).attr('value')
        $('<form action="exportar/pdf.php" method="post"><input type="hidden" name="id_lc" value="' + id_lc + '" /></form>')
            .appendTo('body').submit();
    });
    $('#dataTableDetallLiqContratos').on('click', '.reporte', function () {
        let id_lc = $(this).attr('value')
        $('<form action="' + window.urlin + '/nomina/liquidar_nomina/exportar/pdf.php" method="post"><input type="hidden" name="id_lc" value="' + id_lc + '" /></form>')
            .appendTo('body').submit();
    });
    $('#btnExportaExcelNE').on('click', function () {
        let mes = $(this).attr('value')
        $('<form action="../liquidar_nomina/exportar/excel.php" method="post"><input type="hidden" name="mesNomElec" value="' + mes + '" /></form>')
            .appendTo('body').submit();
    });
    //registrar retroactivo de empleados
    $('#divModalForms').on('click', '#btnAddRetroactivo', function () {
        if ($('#fecIniciaRetroactivo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha de inicio');
        } else if ($('#fecTerminaRetroactivo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha final');
        } else if ($('#fecIniciaRetroactivo').val() > $('#fecTerminaRetroactivo').val()) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La fecha inicial debe ser mayor a la fecha final');
        } else {
            var aprobar = 1;
            $('input[type=number]').each(function () {
                var min = parseInt($(this).attr('min'));
                var max = parseInt($(this).attr('max'));
                var val = $(this).val() == '' ? -1 : parseInt($(this).val());
                $(this).removeClass('border-danger');
                if (val < min || val > max) {
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
            if (aprobar == 1) {
                let datos = $('#formAddRetroactivo').serialize();
                $.ajax({
                    type: 'POST',
                    url: 'registrar/new_retroactivo.php',
                    data: datos,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableRetroactivosNomina';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Retroactivo registrado correctamente');
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    //actualizar retroactivo de empleados
    $('#modificarRetroactivoNomina').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post('datos/actualizar/form_up_retroactivo.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnUpRetroactivo', function () {
        if ($('#fecIniciaRetroactivo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha de inicio');
        } else if ($('#fecTerminaRetroactivo').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar la fecha final');
        } else if ($('#fecIniciaRetroactivo').val() > $('#fecTerminaRetroactivo').val()) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('La fecha inicial debe ser mayor a la fecha final');
        } else {
            var aprobar = 1;
            $('input[type=number]').each(function () {
                var min = parseInt($(this).attr('min'));
                var max = parseInt($(this).attr('max'));
                var val = $(this).val() == '' ? -1 : parseInt($(this).val());
                $(this).removeClass('border-danger');
                if (val < min || val > max) {
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
            if (aprobar == 1) {
                let datos = $('#formUpRetroactivo').serialize();
                $.ajax({
                    type: 'POST',
                    url: 'actualizar/up_retroactivo.php',
                    data: datos,
                    success: function (r) {
                        if (r == '1') {
                            let id = 'tableRetroactivosNomina';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html('Retroactivo actualizado correctamente');
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        }
    });
    //eliminar retroactivo de empleados
    $('#modificarRetroactivoNomina').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'RetroActivo';
        confdel(id, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelRetroActivo', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_retroactivo.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableRetroactivosNomina';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Retroactivo eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //efectuar incremento de salario de empleados
    $('#modificarRetroactivoNomina').on('click', '.incrementar', function () {
        let id = $(this).attr('value');
        $('<form action="lista_empleados_retroactivo.php" method="post"><input type="hidden" name="id_retroactivo" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#btnLiquidarRetroactivo').on('click', function () {
        let datos = $('#formListaEmpleadosRetroactivo').serialize();
        $('#divModalEspera').modal('show');
        $.ajax({
            type: 'POST',
            url: '../liq_nom_public_retroactivo.php',
            data: datos,
            success: function (r) {
                setTimeout(function () {
                    hideModalEspera();
                    if (r.trim() === 'ok') {
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Nomina liquidadada correctamente");
                        setTimeout(function () { $('#divModalEspera').modal('hide'); }, 1000);
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }, 500);
            }
        });
        return false;
    });
    //detalles meses liquidado retroactivo
    $('#tableRetroactivosLiquidados').on('click', '.detalles', function () {
        let id = $(this).attr('value');
        $.post('../datos/listar/meses_liq_retact.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '.mesliquidadoreact a', function () {
        let mes = $(this).attr('value');
        if (mes == '00') {
            window.location = window.urlin + '/nomina/liquidar_nomina/detalles_total_retroactivo.php?mes=' + mes + '&id=' + $('#id_retro_all').val();
        } else {
            window.location = window.urlin + '/nomina/liquidar_nomina/detalles_nomina_retroactivo.php?mes=' + mes;
        }
    });
    $('#LiqPresSocial').on('click', '.liqPresSoc', function () {
        let id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'liq_pres_sociales.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableLiqPresSociales';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Liquidación realizada correctamente");
                    //$('<form action="datos/soporte/resolucion_prestaciones.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#LiqPresSocial').on('click', '.genResol', function () {
        let datos = $(this).attr('value');
        $('<form action="datos/soporte/resolucion_prestaciones.php" method="post"><input type="hidden" name="datos" value="' + datos + '" /></form>').appendTo('body').submit();

    });
    $("#btnFiltraEmpleados").on('click', function () {
        let corte = $('#datFecCorte').val();
        $('<form action="liquidar_pres_soc.php" method="post"><input type="hidden" name="corte" value="' + corte + '" /></form>').appendTo('body').submit();
    });
    $('#btnConfirmaNomina').on('click', function () {
        Swal.fire({
            title: "¿Confirma?, Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                let id = $('#id_nomina').val();
                $('#divModalConfDel').modal('hide');
                $('#divModalEspera').modal('show');
                $.ajax({
                    type: 'POST',
                    url: 'procesar/definitiva.php',
                    data: { id: id },
                    success: function (r) {
                        setTimeout(function () {
                            hideModalEspera();
                            if (r.trim() === 'ok') {
                                mje('Nomina definitiva tramitada correctamente');
                                setTimeout(function () { location.reload() }, 1000);
                            } else {
                                mjeError(r);
                            }
                        }, 500);
                    }
                });
            }
        });
    });
    $('#btnLiqPlanilla').on('click', function () {
        $('#divModalConfDel').modal('show');
        $('#divMsgConfdel').html("Liquidar <b>Planilla</b>, esta acción no se puede deshacer. <b>¿Desea continuar?</b>");
        $('#divBtnsModalDel').html('<a href="#" class="btn btn-success btn-sm w-25" id="btnLiqPlanilla">SI</a><a href="#" class="btn btn-secondary btn-sm w-25" data-dismiss="modal">NO</a>');
    });
    $('#divBtnsModalDel').on('click', "#btnLiqPlanilla", function () {
        let id = $('#id_nomina').val();
        let mes = $('#mesNomElec').val();
        $('#divModalConfDel').modal('hide');
        $('#divModalEspera').modal('show');
        $.ajax({
            type: 'POST',
            url: 'procesar/causacion_planilla.php',
            data: { id: id, mes: mes },
            success: function (r) {
                setTimeout(function () {
                    hideModalEspera();
                    if (r.trim() === 'ok') {
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Nomina definitiva tramitada correctamente");
                        setTimeout(function () { location.reload() }, 1000);
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }, 500);
            }
        });
    });
    $('#btnLiqNomina').on('click', function () {
        $('#divModalConfDel').modal('show');
        $('#divMsgConfdel').html("Liquidar <b>Planilla</b>, esta acción no se puede deshacer. <b>¿Desea continuar?</b>");
        $('#divBtnsModalDel').html('<a href="#" class="btn btn-success btn-sm w-25" id="btnLiqNomina">SI</a><a href="#" class="btn btn-secondary btn-sm w-25" data-dismiss="modal">NO</a>');
    });
    $('#divBtnsModalDel').on('click', "#btnLiqNomina", function () {
        let id = $('#id_nomina').val();
        let mes = $('#mesNomElec').val();
        $('#divModalConfDel').modal('hide');
        $('#divModalEspera').modal('show');
        $.ajax({
            type: 'POST',
            url: 'procesar/causacion_nomina.php',
            data: { id: id, mes: mes },
            success: function (r) {
                setTimeout(function () {
                    hideModalEspera();
                    if (r.trim() === 'ok') {
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Nomina definitiva tramitada correctamente");
                        setTimeout(function () { location.reload() }, 1000);
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }, 500);
            }
        });
    });
    $('#btnReversaNomina').on('click', function () {
        let id = $('#id_nomina').val();
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
                    type: 'POST',
                    url: 'procesar/reversar_nomina.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == '1') {
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Nomina reversada correctamente");
                            setTimeout(function () { location = window.urlin + '/nomina/liquidar_nomina/mostrar/liqxmes.php' }, 1000);
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        });

    });
    $('#accionNominas').on('click', '.detalle', function () {
        let id = $(this).attr('value');
        $('<form action="' + window.urlin + '/nomina/liquidar_nomina/detalles_nomina.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>')
            .appendTo('body').submit();
    });
    $('#liqPreSocial').on('click', function () {
        var c = 0;
        var elemento = $(this);
        $(this).attr('disabled', 'disabled');
        $(this).html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
        $('input[type=checkbox]').each(function () {
            if ($(this).prop('checked')) {
                c++;
            }
        });
        if (c > 0) {
            var datos = $("#formLiqPreSoc").serialize();
            $.ajax({
                type: 'POST',
                url: 'liq_pres_sociales.php',
                data: datos,
                success: function (r) {
                    elemento.attr('disabled', false);
                    elemento.html('Liquidar');
                    if (r.trim() === 'ok') {
                        let id = "tableLiqPresSociales";
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Liquidación de Seguridad Social registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        } else {
            elemento.attr('disabled', false);
            elemento.html('Liquidar');
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se ha seleccionado ningún empleado');
        }
        //alert(datos);
    });
    $('#divModalForms').on('click', '#btnRegConceptoXvig', function () {
        $('.form-control').removeClass('is-invalid');
        if ($('#concepto').val() == '0') {
            $('#concepto').focus();
            $('#concepto').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se ha seleccionado un concepto');
        } else if ($('#valor').val() == '' || Number($('#valor').val()) < 0) {
            $('#valor').focus();
            $('#valor').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor debe ser mayor a cero (0)');
        } else {
            let datos = $('#formRegConcepXvig').serialize();
            $.ajax({
                type: 'POST',
                url: 'liquidar_nomina/registrar/conceptoxvig.php',
                data: datos,
                success: function (r) {
                    if (r.trim() === 'ok') {
                        $('#divModalForms').modal('hide');
                        let id = "tableParamLiq";
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Concepto registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#modificaParamLiq').on('click', '.actualizar', function () {
        var id = $(this).attr('value');
        $.post("liquidar_nomina/datos/actualizar/form_up_concepto.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnUpConceptoXvig', function () {
        $('.form-control').removeClass('is-invalid');
        if ($('#valor').val() == '' || Number($('#valor').val()) < 0) {
            $('#valor').focus();
            $('#valor').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Valor debe ser mayor a cero (0)');
        } else {
            let datos = $('#formUpConcepXvig').serialize();
            $.ajax({
                type: 'POST',
                url: 'liquidar_nomina/actualizar/upconceptoxvig.php',
                data: datos,
                success: function (r) {
                    if (r.trim() === 'ok') {
                        $('#divModalForms').modal('hide');
                        let id = "tableParamLiq";
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Concepto Actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#modificaParamLiq').on('click', '.eliminar', function () {
        let id = $(this).attr('value');
        let tip = 'ConcpXvig';
        confdel(id, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelConcpXvig', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'liquidar_nomina/eliminar/del_concepto.php',
            data: { id: id },
            success: function (r) {
                if (r.trim() === 'ok') {
                    let id = "tableParamLiq";
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
    $('#dataTableLiqNom').on('click', '.anular', function () {
        var id_empleado = $(this).attr('value');
        var id_nomina = $('#id_nomina').val();
        $.ajax({
            type: 'POST',
            url: 'procesar/reversar_empleado.php',
            data: { id_empleado: id_empleado, id_nomina: id_nomina },
            success: function (r) {
                if (r.trim() === 'ok') {
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Empleado anulado correctamente");
                    setTimeout(function () { location.reload(); }, 500);
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    $('#divModalForms').on('click', '#btnRegIncrSal', function () {
        $('.form-control').removeClass('is-invalid');
        if (Number($('#valorIncr').val()) < 0) {
            $('#valorIncr').focus();
            $('#valorIncr').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Incremento salarial debe ser mayor a 0');
        } else {
            let datos = $('#formRegIncSla').serialize();
            $.ajax({
                type: 'POST',
                url: 'liquidar_nomina/registrar/incremento_salarial.php',
                data: datos,
                success: function (r) {
                    if (r.trim() === 'ok') {
                        $('#divModalForms').modal('hide');
                        let id = "tableIncremento";
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Concepto registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#modificaIncremento').on('click', '.eliminar', function () {
        let id = $(this).attr('value');
        let tip = 'IncrSalar';
        confdel(id, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelIncrSalar', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'liquidar_nomina/eliminar/del_incremento.php',
            data: { id: id },
            success: function (r) {
                if (r.trim() === 'ok') {
                    let id = "tableIncremento";
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
    $('#divTamModalForms').on('click', '#BuscaTerNom', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "liquidar_nomina/datos/listar/buscar_terceros.php",
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
                $('#idTerceroNom').val(ui.item.id);
            }
        });
    });
    $('#divModalForms').on('click', '.buscaRubro', function () {
        var fila = $(this).closest('.form-group');
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "liquidar_nomina/datos/listar/buscar_rubro.php",
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
                fila.find('.id_rb').val(ui.item.id);
                fila.find('.id_tp').val(ui.item.tipo);

            }
        });
    });
    $('#divModalForms').on('click', '#txtBuscaCuentaCtb', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "liquidar_nomina/datos/listar/buscar_cuenta.php",
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
                $('#idCtaCtb').val(ui.item.id);
                $('#tipoCta').val(ui.item.tipo);
            }
        });
    });
    $('#divModalForms').on('click', '#btnRegTerceroNom', function () {
        $('.form-control').removeClass('is-invalid');
        var valida = true;
        if ($('#slcCategoria').val() == 0) {
            $('#slcCategoria').focus();
            $('#slcCategoria').addClass('is-invalid');
            mjeError('Debe seleccionar una categoria');
        } else if ($('#idTerceroNom').val() == 0) {
            $('#BuscaTerNom').focus();
            $('#BuscaTerNom').addClass('is-invalid');
            mjeError('Debe seleccionar un tercero');
        } else {
            if ($('#slcTipoParaf').length) {
                if ($('#slcTipoParaf').val() == '0') {
                    $('#slcTipoParaf').focus();
                    $('#slcTipoParaf').addClass('is-invalid');
                    mjeError('Debe seleccionar un tipo de parafiscal');
                    valida = false;
                }
            }
            let datos = $('#formRegTerceroNom').serialize();
            if (valida) {
                $.ajax({
                    type: 'POST',
                    url: 'liquidar_nomina/registrar/addtercero_nomina.php',
                    data: datos,
                    success: function (r) {
                        if (r.trim() === 'ok') {
                            $('#divModalForms').modal('hide');
                            $('#tableTerceroNomina').DataTable().ajax.reload(null, false);
                            mje('Tercero registrado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        }
    });
    $('#divModalForms').on('change', '#slcCategoria', function () {
        var cat = $(this).val();
        var html = '';
        if (cat == "PARA") {
            //label para selecionar tipo de parafiscales
            html += '<label for="slcTipoParaf" class="small">Tipo de Parafiscal</label>';
            html += '<select class="form-control form-control-sm" id="slcTipoParaf" name="slcTipoParaf">';
            html += '<option value="0">--Seleccione--</option>';
            html += '<option value="SENA">SERVICIO NACIONAL DE APRENDIZAJE</option>';
            html += '<option value="ICBF">INSTITUTO COLOMBIANO DE BIENESTAR FAMILIAR</option>';
            html += '<option value="CAJA">CAJA DE COMPENSACION FAMILIAR</option>';
            html += '</select>';
        }
        $('#divParaFisc').html(html);
    });
    $('#btnLiqVacaciones').on('click', function () {
        let c = 0;
        $('input[type=checkbox]').each(function () {
            if ($(this).prop('checked')) {
                c++;
            }
        });
        if (c == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('No se ha seleccionado ningún empleado');
        } else {
            let datos = $('#formLiqVacs').serialize();
            $('#divModalEspera').modal('show');
            $.ajax({
                type: 'POST',
                url: 'liq_vacaciones_public.php',
                data: datos,
                success: function (r) {
                    setTimeout(function () {
                        hideModalEspera();
                        if (r === 'ok') {
                            $('#divModalExito a').attr('data-dismiss', '');
                            $('#divModalExito a').attr('href', 'javascript:location.reload()');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Liquidación de vacaciones registrada correctamente");
                            setTimeout(function () { }, 1000);
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }, 500);
                }
            });
        }
    });
    $('#divModalForms').on('click', '#btnGuardaCargo', function () {
        $('.form-control').removeClass('is-invalid');
        if ($('#slcCodigo').val() == '0' && op_caracter == '2') {
            $('#slcCodigo').focus();
            $('#slcCodigo').addClass('is-invalid');
            mjeError('Debe seleccionar un código');
        } else if ($('#txtNomCargo').val() == '') {
            $('#txtNomCargo').focus();
            $('#txtNomCargo').addClass('is-invalid');
            mjeError('Debe ingresar un nombre');
        } else if (Number($('#numGrado').val()) <= 0 && op_caracter == '2') {
            $('#slcGrado').focus();
            $('#slcGrado').addClass('is-invalid');
            mjeError('Grado debe ser mayor a cero');
        } else if ($('#slcNombramiento').val() == '0' && op_caracter == '2') {
            $('#slcNombramiento').focus();
            $('#slcNombramiento').addClass('is-invalid');
            mjeError('Debe seleccionar un nombramiento');
        } else if ($('#txtPerfilSiho').val() == '' && op_caracter == '2') {
            $('#txtPerfilSiho').focus();
            $('#txtPerfilSiho').addClass('is-invalid');
            mjeError('Debe ingresar un perfil');
        } else {
            var datos = $('#formGestCargoNom').serialize();
            $.ajax({
                type: 'POST',
                url: 'liquidar_nomina/registrar/gestion_cargo.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalForms').modal('hide');
                        let id = "tableCargosNomina";
                        reloadtable(id);
                        mje('Proceso realizado correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
    $('#modificaCargoNomina').on('click', '.editar', function () {
        var id_cargo = $(this).attr('value');
        $.post("liquidar_nomina/datos/registrar/formadd_cargo.php", { id_cargo: id_cargo }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#modificaCargoNomina').on('click', '.eliminar', function () {
        let id = $(this).attr('value');
        Swal.fire({
            title: "¿Confirma eliminar este registro?",
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
                    url: 'liquidar_nomina/eliminar/del_cargo.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            let table = 'tableCargosNomina';
                            reloadtable(table);
                            mje('Proceso realizado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#divModalForms').on('click', '#btnGuardaRubroNom', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#slcTipo').val() == '0') {
            $('#slcTipo').addClass('focus');
            $('#slcTipo').addClass('is-invalid');
            mjeError('Debe seleccionar un tipo de rubro');
        } else if ($('#idRubroAdmin').val() == '0') {
            $('#txtRubroAdmin').addClass('focus');
            $('#txtRubroAdmin').addClass('is-invalid');
            mjeError('Debe seleccionar un rubro válido');
        } else if ($('#tp_dato_radm').val() != '1') {
            $('#txtRubroAdmin').addClass('focus');
            $('#txtRubroAdmin').addClass('is-invalid');
            mjeError('El rubro seleccionado no es de tipo detalle');
        } else if ($('#idRubroOpera').val() == '0') {
            $('#txtRubroOpera').addClass('focus');
            $('#txtRubroOpera').addClass('is-invalid');
            mjeError('Debe seleccionar un rubro válido');
        } else if ($('#tp_dato_rope').val() != '1') {
            $('#txtRubroOpera').addClass('focus');
            $('#txtRubroOpera').addClass('is-invalid');
            mjeError('El rubro seleccionado no es de tipo detalle');
        } else {
            var data = $('#formGestRubroNom').serialize();
            $.ajax({
                type: 'POST',
                url: 'liquidar_nomina/registrar/gestion_rubro.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalForms').modal('hide');
                        let id = "tableRubrosNomina";
                        reloadtable(id);
                        mje('Proceso realizado correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    }); $('#divModalForms').on('click', '#btnGuardaCuentaNom', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#slcTipo').val() == '0') {
            $('#slcTipo').addClass('focus');
            $('#slcTipo').addClass('is-invalid');
            mjeError('Debe seleccionar un tipo de rubro');
        } else if ($('#slcCentroCosto').val() == '0') {
            $('#slcCentroCosto').addClass('focus');
            $('#slcCentroCosto').addClass('is-invalid');
            mjeError('Debe seleccionar un centro de costo');
        } else if ($('#idCtaCtb').val() <= '0') {
            $('#txtBuscaCuentaCtb').addClass('focus');
            $('#txtBuscaCuentaCtb').addClass('is-invalid');
            mjeError('Debe seleccionar una cuenta contable');
        } else if ($('#tipoCta').val() == 'M') {
            $('#txtBuscaCuentaCtb').addClass('focus');
            $('#txtBuscaCuentaCtb').addClass('is-invalid');
            mjeError('La cuenta seleccionada no es de tipo detalle');
        } else {
            var data = $('#formGestCtaNom').serialize();
            $.ajax({
                type: 'POST',
                url: 'liquidar_nomina/registrar/gestion_cuenta.php',
                data: data,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalForms').modal('hide');
                        let id = "tableCtaCtbNomina";
                        reloadtable(id);
                        mje('Proceso realizado correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
    });
    $('#modificaRubrosNomina').on('click', '.editar', function () {
        var id_relacion = $(this).attr('value');
        $.post("liquidar_nomina/datos/registrar/formadd_rubro.php", { id_relacion: id_relacion }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#modificaCtaCtbNomina').on('click', '.editar', function () {
        var id_causacion = $(this).attr('value');
        $.post("liquidar_nomina/datos/registrar/formadd_cuenta.php", { id_causacion: id_causacion }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#modificaRubrosNomina').on('click', '.eliminar', function () {
        let id = $(this).attr('value');
        Swal.fire({
            title: "¿Confirma eliminar este registro?",
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
                    url: 'liquidar_nomina/eliminar/del_rubro.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            let table = 'tableRubrosNomina';
                            reloadtable(table);
                            mje('Proceso realizado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#modificaCtaCtbNomina').on('click', '.eliminar', function () {
        let id = $(this).attr('value');
        Swal.fire({
            title: "¿Confirma eliminar este registro?",
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
                    url: 'liquidar_nomina/eliminar/del_cuenta.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            let table = 'tableCtaCtbNomina';
                            reloadtable(table);
                            mje('Proceso realizado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
})(jQuery);