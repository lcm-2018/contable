(function ($) {
    var showError = function (id) {
        $('#e' + id).show();
        setTimeout(function () {
            $('#e' + id).fadeOut(600);
        }, 800);
    };
    var bordeError = function (p) {
        $('#' + p).css("border", "2px solid #F5B7B1");
        $('#' + p).css('box-shadow', '0 0 4px 3px pink');
    };
    $("#btnCerrarModalupEmpH").click(function () {
        $('#divModalupEmpHecho').modal('hide');
        window.location = '../listempleados.php';
    });
    $("#divModalConfDel").on('click', '#btnConfirDelEmpleado', function () { //del empleado confirmado
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delempleado.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    rowdel();
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Empleado eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    //Nuevo empleado
    //Validar solo numeros
    var numeros = function (i) {
        $("#" + i).on({
            "focus": function (event) {
                $(event.target).select();
            },
            "keyup": function (event) {
                $(event.target).val(function (index, value) {
                    return value.replace(/\D/g, "");
                });
            }
        });
    };
    $('#slcTipoContratoEmp').on('change', function () {
        let id = $(this).val();
        if (id == 2 || id == 7) {
            $('#datFecRetEps').val('');
            $('#datFecRetArl').val('');
            $('#datFecRetAfp').val('');
        }
    });
    $("#txtCCempleado").keyup(function () {
        let id = 'txtCCempleado';
        numeros(id);
    });
    $("#txtTelEmp").keyup(function () {
        let id = 'txtTelEmp';
        numeros(id);
    });
    $("#txtCuentaBanc").keyup(function () {
        let id = 'txtCuentaBanc';
        numeros(id);
    });
    $("#txtNitEmpresa").keyup(function () {
        let id = 'txtNitEmpresa';
        numeros(id);
    });
    $("#txtUpTel").keyup(function () {
        let id = 'txtUpTel';
        numeros(id);
    });
    $("#numDiasLab").keyup(function () {
        let id = 'numDiasLab';
        numeros(id);
    });
    $("#numDiasIncap").keyup(function () {
        let id = 'numDiasIncap';
        numeros(id);
    });
    $("#numDiasVac").keyup(function () {
        let id = 'numDiasVac';
        numeros(id);
    });
    $("#numDiasLic").keyup(function () {
        let id = 'numDiasLic';
        numeros(id);
    });
    $("#divModalForms").on('keyup', '#txtNitAfp', function () {
        let id = 'txtNitAfp';
        numeros(id);
    });
    $("#txtTelAfp").keyup(function () {
        let id = 'txtTelAfp';
        numeros(id);
    });
    $("#divModalForms").on('keyup', '#txtNitUpAfp', function () {
        let id = 'txtNitUpAfp';
        numeros(id);
    });
    $("#txtTelUpAfp").keyup(function () {
        let id = 'txtTelUpAfp';
        numeros(id);
    });
    $("#divModalForms").on('keyup', '#txtNitArl', function () {
        let id = 'txtNitArl';
        numeros(id);
    });
    $("#txtTelArl").keyup(function () {
        let id = 'txtTelArl';
        numeros(id);
    });
    $("#txtNitUpArl").keyup(function () {
        let id = 'txtNitUpArl';
        numeros(id);
    });
    $("#txtTelUpArl").keyup(function () {
        let id = 'txtTelUpArl';
        numeros(id);
    });
    $("#txtNitEps").keyup(function () {
        let id = 'txtNitEps';
        numeros(id);
    });
    $("#txtTelEps").keyup(function () {
        let id = 'txtTelEps';
        numeros(id);
    });
    $("#txtNitUpEps").keyup(function () {
        let id = 'txtNitUpEps';
        numeros(id);
    });
    $("#txtTelUpEps").keyup(function () {
        let id = 'txtTelUpEps';
        numeros(id);
    });
    $("#txtCCuser").keyup(function () {
        let id = 'txtCCuser';
        numeros(id);
    });
    $("#txtccUpUser").keyup(function () {
        let id = 'txtccUpUser';
        numeros(id);
    });
    //Separadores de mil
    var miles = function (i) {
        $("#" + i).on({
            "focus": function (e) {
                $(e.target).select();
            },
            "keyup": function (e) {
                $(e.target).val(function (index, value) {
                    return value.replace(/\D/g, "")
                        .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                        .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                });
            }
        });
    };
    $("#numSalarioEmp").keyup(function () {
        let id = 'numSalarioEmp';
        miles(id);
    });
    $("#divModalForms").on('keyup', '#numValTotal', function () {
        let id = 'numValTotal';
        miles(id);
    });
    $("#divModalForms").on('keyup', '#numUpValTotal', function () {
        let id = 'numUpValTotal';
        miles(id);
    });
    $("#divModalForms").on('keyup', '#numTotEmbargo', function () {
        let id = 'numTotEmbargo';
        miles(id);
    });
    $("#divModalForms").on('keyup', '#numUpTotEmbargo', function () {
        let id = 'numUpTotEmbargo';
        miles(id);
    });
    $("#numAuxTransp").keyup(function () {
        let id = 'numAuxTransp';
        miles(id);
    });
    $("#numAportSalud").keyup(function () {
        let id = 'numAportSalud';
        miles(id);
    });
    $("#numAportPension").keyup(function () {
        let id = 'numAportPension';
        miles(id);
    });
    $("#numAportPenSolid").keyup(function () {
        let id = 'numAportPenSolid';
        miles(id);
    });
    $("#numValIncap").keyup(function () {
        let id = 'numValIncap';
        miles(id);
    });
    $("#numValVac").keyup(function () {
        let id = 'numValVac';
        miles(id);
    });
    $("#numValLica").keyup(function () {
        let id = 'numValLica';
        miles(id);
    });
    $("#numDeduccionesEmb").keyup(function () {
        let id = 'numDeduccionesEmb';
        miles(id);
    });
    $("#numDeduccionesLib").keyup(function () {
        let id = 'numDeduccionesLib';
        miles(id);
    });
    $("#numDeduccionesSind").keyup(function () {
        let id = 'numDeduccionesSind';
        miles(id);
    });
    $("#numValDiasLab").keyup(function () {
        let id = 'numValDiasLab';
        miles(id);
    });
    $("#numSalNeto").keyup(function () {
        let id = 'numSalNeto';
        miles(id);
    });
    $("#numProvSalud").keyup(function () {
        let id = 'numProvSalud';
        miles(id);
    });
    $("#numProvPension").keyup(function () {
        let id = 'numProvPension';
        miles(id);
    });
    $("#numProvARL").keyup(function () {
        let id = 'numProvARL';
        miles(id);
    });
    $("#numProvSENA").keyup(function () {
        let id = 'numProvSENA';
        miles(id);
    });
    $("#numProvICBF").keyup(function () {
        let id = 'numProvICBF';
        miles(id);
    });
    $("#numProvCOMFAM").keyup(function () {
        let id = 'numProvCOMFAM';
        miles(id);
    });
    $("#numProvCesan").keyup(function () {
        let id = 'numProvCesan';
        miles(id);
    });
    $("#numProvIntCesan").keyup(function () {
        let id = 'numProvIntCesan';
        miles(id);
    });
    $("#numProvVac").keyup(function () {
        let id = 'numProvVac';
        miles(id);
    });
    $("#numProvPrima").keyup(function () {
        let id = 'numProvPrima';
        miles(id);
    });
    $("#numValIncapEmpresa").keyup(function () {
        let id = 'numValIncapEmpresa';
        miles(id);
    });
    $("#numValIncapEPS").keyup(function () {
        let id = 'numValIncapEPS';
        miles(id);
    });
    $("#numValIncapARL").keyup(function () {
        let id = 'numValIncapARL';
        miles(id);
    });
    $("#btnNuevoEmpleado").click(function () {
        let ced = $("#txtCCempleado").val();
        let eps = $("#slcEps").val();
        let arl = $("#slcArl").val();
        let afp = $("#slcAfp").val();
        let rl = $("#slcRiesLab").val();
        let cesan = $("#slcFc").val();
        let par;
        $('.form-control').removeClass('border-danger');
        if ($("#slcSedeEmp").val() === '0') {
            par = "slcSedeEmp";
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcTipoEmp").val() === '0') {
            par = "slcTipoEmp";
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcSubTipoEmp").val() === '0') {
            par = 'slcSubTipoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if (!($('input[name=slcAltoRiesgo]:checked').length)) {
            par = 'slcAltoRiesgo';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#slcAltoRiesgo1").focus();
        } else if ($("#slcTipoContratoEmp").val() === '0') {
            par = 'slcTipoContratoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcTipoDocEmp").val() === '0') {
            par = 'slcTipoDocEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if (!($('input[name=slcGenero]:checked').length)) {
            par = 'slcGenero';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#slcGeneroM").focus();
        } else if ($("#slcPaisExp").val() === '0') {
            par = 'slcPaisExp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcDptoExp").val() === '0') {
            par = 'slcDptoExp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcMunicipioExp").val() === '0') {
            par = 'slcMunicipioExp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#datFecExp").val() === '') {
            par = 'datFecExp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcPaisNac").val() === '0') {
            par = 'slcPaisNac';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcDptoNac").val() === '0') {
            par = 'slcDptoNac';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcMunicipioNac").val() === '0') {
            par = 'slcMunicipioNac';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#datFecNac").val() === '') {
            par = 'datFecNac';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#datInicio").val() === '') {
            par = 'datInicio';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus()
        } else if (!($('input[name=slcSalIntegral]:checked').length)) {
            par = 'slcSalIntegral';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#slcSalIntegral1").focus();
        } else if ($("#numSalarioEmp").val() < '1') {
            par = 'numSalarioEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcPaisEmp").val() === '0') {
            par = 'slcPaisEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcDptoEmp").val() === '0') {
            par = 'slcDptoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcMunicipioEmp").val() === '0') {
            par = 'slcMunicipioEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#txtDireccion").val() === '') {
            par = 'txtDireccion';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcCargoEmp").val() === '0') {
            par = 'slcCargoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus()
        } else if (!($('input[name=slcTipoCargo]:checked').length)) {
            par = 'slcTipoCargo';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#slcTipoCargo1").focus();
        } else if ($("#slcBancoEmp").val() === '0') {
            par = 'slcBancoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if (!($('input[name=selTipoCta]:checked').length)) {
            par = 'selTipoCta';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#selTipoCta1").focus();
        } else if ($("#txtCuentaBanc").val() === '') {
            par = 'txtCuentaBanc';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcCCostoEmp").val() === '0') {
            par = 'slcCCostoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else {
            if (ced == "") {
                $('#divModalError').modal('show');
                $('#divMsgError').html("Ingresar numero de documento");
                return false;
            } else {
                if (eps === '0' || arl === '0' || rl === '0' || afp === '0' || cesan === '0') {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html("Debe selecionar EPS, AFP, ARL, Riesgo laboral y fondo de cesantias");
                    return false;
                } else {
                    $('#btnNuevoEmpleado').attr('disabled', true);
                    $('#btnNuevoEmpleado').html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
                    let datos = $("#formNuevoEmpleado").serialize() + '&pasT=' + hex_sha512(ced);
                    $.ajax({
                        type: 'POST',
                        url: 'newempleado.php',
                        data: datos,
                        success: function (r) {
                            $('#btnNuevoEmpleado').attr('disabled', false);
                            $('#btnNuevoEmpleado').html('Registrar');
                            switch (r) {
                                case '0':
                                    $('#divModalError').modal('show');
                                    $('#divMsgError').html("Empleado ya está registrado");
                                    break;
                                case '1':
                                    $("#formNuevoEmpleado")[0].reset();
                                    $('#divModalDone').modal('show');
                                    $('#divMsgDone').html("Empleado registrado correctamente");
                                    break;
                                default:
                                    $('#divModalError').modal('show');
                                    $('#divMsgError').html(r);
                                    break;
                            }
                        }
                    });
                    return false;
                }
            }
        }
        return false;
    });
    //Actualizar empleado
    $("#btnUpEmpleado").click(function () {
        if ($('#txtCCempleado').val() === '') {
            $('#divModalupError').modal('show');
            $('#divcontenido').html('Debe ingresar un número de documento');
        } else $('.form-control').removeClass('border-danger');
        if ($("#slcSedeEmp").val() === '0') {
            par = "slcSedeEmp";
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcTipoEmp").val() === '0') {
            par = "slcTipoEmp";
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcSubTipoEmp").val() === '0') {
            par = 'slcSubTipoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if (!($('input[name=slcAltoRiesgo]:checked').length)) {
            par = 'slcAltoRiesgo';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#slcAltoRiesgo1").focus();
        } else if ($("#slcTipoContratoEmp").val() === '0') {
            par = 'slcTipoContratoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcTipoDocEmp").val() === '0') {
            par = 'slcTipoDocEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if (!($('input[name=slcGenero]:checked').length)) {
            par = 'slcGenero';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#slcGeneroM").focus();
        } else if ($("#slcPaisExp").val() === '0') {
            par = 'slcPaisExp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcDptoExp").val() === '0') {
            par = 'slcDptoExp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcMunicipioExp").val() === '0') {
            par = 'slcMunicipioExp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#datFecExp").val() === '') {
            par = 'datFecExp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcPaisNac").val() === '0') {
            par = 'slcPaisNac';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcDptoNac").val() === '0') {
            par = 'slcDptoNac';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcMunicipioNac").val() === '0') {
            par = 'slcMunicipioNac';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#datFecNac").val() === '') {
            par = 'datFecNac';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#datInicio").val() === '') {
            par = 'datInicio';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus()
        } else if (!($('input[name=slcSalIntegral]:checked').length)) {
            par = 'slcSalIntegral';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#slcSalIntegral1").focus();
        } else if ($("#numSalarioEmp").val() < '1') {
            par = 'numSalarioEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcPaisEmp").val() === '0') {
            par = 'slcPaisEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcDptoEmp").val() === '0') {
            par = 'slcDptoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcMunicipioEmp").val() === '0') {
            par = 'slcMunicipioEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#txtDireccion").val() === '') {
            par = 'txtDireccion';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if ($("#slcCargoEmp").val() === '0') {
            par = 'slcCargoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus()
        } else if (!($('input[name=slcTipoCargo]:checked').length)) {
            par = 'slcTipoCargo';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#slcTipoCargo1").focus();
        } else if ($("#slcBancoEmp").val() === '0') {
            par = 'slcBancoEmp';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else if (!($('input[name=selTipoCta]:checked').length)) {
            par = 'selTipoCta';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#selTipoCta1").focus();
        } else if ($("#txtCuentaBanc").val() === '') {
            par = 'txtCuentaBanc';
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#" + par).addClass('border-danger');
            $("#" + par).focus();
        } else {
            var datos = $("#formUpEmpleado").serialize();
            $.ajax({
                type: 'POST',
                url: 'upempleado.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Empleado actualizado correctamente");
                        setTimeout(function () {
                            location.reload();
                        }, 500);
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Registrar novedad ARL
    $("#divModalForms").on('click', '#btnAddNovedadArl', function () {
        let inicio = $("#datFecAfilArlNovedad").val();
        let fin = $("#datFecRetArlNovedad").val();
        if ($("#slcArlNovedad").val() === "0") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir una ARL ");
        } else if ($("#slcRiesLabNov").val() === "0") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir Riesgo laboral");
        } else if ($("#datFecAfilArlNovedad").val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicial no puede estar vacia");
        } else if (fin !== '' && inicio > fin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicial debe ser menor");
        } else {
            let dnovarl = $("#formAddArlNovedad").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newnovedadarl.php',
                data: dnovarl,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableArl';
                        $("#divModalForms").modal('hide');
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $("#divModalForms").on('click', '#btnAddCCostoEmp', function () {
        var tp = $(this).attr('text');
        if ($("#slcCcostoEmpl").val() === "0") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir un centro de costo");
        } else {
            let novccosto = $("#formNovCCosto").serialize();
            var url = tp === '1' ? 'registrar/newnovccosto.php' : 'actualizar/upnovccosto.php';
            $.ajax({
                type: 'POST',
                url: url,
                data: novccosto,
                success: function (r) {
                    if (r == 'ok') {
                        let id = 'tableCCostoEmp';
                        $("#divModalForms").modal('hide');
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad realizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //UP novedad ARL
    $('#modificarArls').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novedadarl.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#modificarCCostoEmp').on('click', '.editar', function () {
        let id_cc = $(this).attr('value');
        $.post("datos/actualizar/up_nov_ccosto.php", { id_cc: id_cc }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#modificarCCostoEmp').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        Swal.fire({
            title: "¿Confirma eliminar el centro de costo?",
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
                    url: 'eliminar/delnovccosto.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            let id = 'tableCCostoEmp';
                            reloadtable(id);
                            mje("Novedad eliminada correctamente");
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    //Actualizar novedad ARL
    $("#divModalForms").on('click', '.actualizarArl', function () {
        let inicio = $("#datFecAfilUpNovArl").val();
        let fin = $("#datFecRetUpNovArl").val();
        if (inicio === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicial no puede estar vacia");
        } else if (fin !== '' && inicio > fin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicial debe ser menor");
        } else {
            let dupnovarl = $('#formUpNovArl').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/upnovedadarl.php',
                data: dupnovarl,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableArl';
                        reloadtable(id);
                        $("#divModalForms").modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Novedad ARL
    $("#divBtnsModalDel").on('click', '#btnConfirDelArl', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delnovedadarl.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableArl';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("ARL eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    //Registrar novedad EPS
    $("#divForms").on('click', '#btnAddNovedadEps', function () {
        let inicio = $('#datFecAfilEpsNovedad').val();
        let fin = $('#datFecRetEpsNovedad').val();
        if ($("#slcEpsNovedad").val() === "0") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir una EPS");
        } else if ($('#datFecAfilEpsNovedad').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede estar vacia');
        } else if (fin !== '' && inicio > fin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial debe ser menor');
        } else {
            let dnoveps = $("#formAddEpsNovedad").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newnovedadeps.php',
                data: dnoveps,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableEps';
                        $('#divModalForms').modal('hide');
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //UP novedad EPS
    $('#modificarEpss').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novedadeps.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar novedad EPS
    $("#divModalForms").on('click', '.actualizarEps', function () {
        let inicio = $('#datFecAfilUpNovEps').val();
        let fin = $('#datFecRetUpNovEps').val();
        if (inicio === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede estar vacia');
        } else if (fin !== '' && inicio > fin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial debe ser menor');
        } else {
            let dupnov = $('#formUpNovEps').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/upnovedadeps.php',
                data: dupnov,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableEps';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Novedad EPS
    $("#divBtnsModalDel").on('click', '#btnConfirDelEps', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delnovedad.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableEps';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("EPS eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    //Registrar novedad AFP
    $("#divModalForms").on('click', '#btnAddNovedadAfp', function () {
        let inicio = $('#datFecAfilAfpNovedad').val();
        let fin = $('#datFecRetAfpNovedad').val();
        if ($("#slcAfpNovedad").val() === "0") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir una AFP");
        } else if (inicio === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede estar vacia');
        } else if (fin !== '' && inicio > fin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial debe ser menor');
        } else {
            let dnovafp = $("#formAddAfpNovedad").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newnovedadafp.php',
                data: dnovafp,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableAfp';
                        $('#divModalForms').modal('hide');
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $("#divModalForms").on('click', '#btnAddNovedadFc', function () {
        let inicio = $('#datFecAfilAfpNovedad').val();
        let fin = $('#datFecRetAfpNovedad').val();
        if ($("#slcAfpNovedad").val() === "0") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir una AFP");
        } else if (inicio === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede estar vacia');
        } else if (fin !== '' && inicio > fin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial debe ser menor');
        } else {
            let dnovafp = $("#formAddFCNovedad").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newnovedadfc.php',
                data: dnovafp,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableFCesan';
                        $('#divModalForms').modal('hide');
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $('#modificarFCesans').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novedadfc.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //UP novedad AFP
    $('#modificarAfps').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novedadafp.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar novedad AFP
    $("#divModalForms").on('click', '.actualizarAfp', function () {
        let inicio = $('#datFecAfilUpNovAfp').val();
        let fin = $('#datFecRetUpNovAfp').val();
        if (inicio === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede estar vacia');
        } else if (fin !== '' && inicio > fin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial debe ser menor');
        } else {
            let dupnovafp = $('#formUpNovAfp').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/upnovedadafp.php',
                data: dupnovafp,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableAfp';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $("#divModalForms").on('click', '.actualizarFc', function () {
        let inicio = $('#datFecAfilUpNovAfp').val();
        let fin = $('#datFecRetUpNovAfp').val();
        if (inicio === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial no puede estar vacia');
        } else if (fin !== '' && inicio > fin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha Inicial debe ser menor');
        } else {
            let dupnovafp = $('#formUpNovFc').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/upnovedadfc.php',
                data: dupnovafp,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableFCesan';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Novedad AFP
    $("#divBtnsModalDel").on('click', '#btnConfirDelAfp', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delnovedadafp.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableAfp';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("AFP eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);;
                }
            }
        });
    });
    //Registrar Libranza
    $("#divModalForms").on('click', '#btnAddLibranza', function () {
        if ($("#slcEntidad").val() === "0") {
            let id = 'slcEntidad';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($("#numValTotal").val() === '' || parseInt($("#numValTotal").val()) <= 0) {
            let id = 'numValTotal';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('#numTotCuotasLib').val() === '' || parseInt($('#numTotCuotasLib').val()) <= 0) {
            let id = 'numTotCuotasLib';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('#txtDescripLib').val() === '') {
            let id = 'txtDescripLib';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($("#txtValLibMes").val() === '' || parseInt($("#txtValLibMes").val()) <= 0) {
            let id = 'txtValLibMes';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('datFecInicioLib').val() === '') {
            let id = 'datFecInicioLib';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if (parseInt($('#txtPorcLibMes').val()) >= 100) {
            $('#txtPorcLibMes').val(0);
            $('#txtValLibMes').val(0);
            $('#divModalError').modal('show');
            $('#divMsgError').html('Porcentaje dede ser menor al 100%');
        } else {
            let dlibranza = $("#formAddLibranza").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newlibranza.php',
                data: dlibranza,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableLibranza';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#btnShowAddLibranza').show();
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Libranza registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //UP Libranza
    $('#modificarLibranzas').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novlibranza.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });

    });
    //Actualizar Libranza
    $("#divModalForms").on('click', '.actualizarLib', function () {
        if ($("#numUpValTotal").val() === '' || parseInt($("#numUpValTotal").val()) <= 0) {
            let id = 'numUpValTotal';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('#numUpTotCuotasLib').val() === '' || parseInt($('#numUpTotCuotasLib').val()) <= 0) {
            let id = 'numUpTotCuotasLib';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('#txtUpDescripLib').val() === '') {
            let id = 'txtUpDescripLib';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($("#txtUpValLibMes").val() === '' || parseInt($("#txtUpValLibMes").val()) <= 0) {
            let id = 'txtUpValLibMes';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('datUpFecInicioLib').val() === '') {
            let id = 'datUpFecInicioLib';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if (parseInt($('#txtUpPorcLibMes').val()) >= 100) {
            $('#txtUpPorcLibMes').val(0);
            $('#txtUpValLibMes').val(0);
            $('#divModalError').modal('show');
            $('#divMsgError').html('Porcentaje dede ser menor al 100%');
        } else {
            let duplibranza = $('#formUpLibranza').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/uplibranza.php',
                data: duplibranza,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableLibranza';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Libranza Actualizada correctamente");
                    } else {

                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Libranza
    $("#divBtnsModalDel").on('click', '#btnConfirDelLib', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/dellibranza.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableLibranza';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Libranza eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);;
                }
            }
        });
        return false;
    });
    //listar detalles de libranza
    $('#modificarLibranzas').on('click', '.detalles', function () {
        let idlib = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'listar/listlibranza.php',
            data: { idlib: idlib },
            success: function (r) {
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(r);
            }
        });
    });
    //Registrar Embargo 
    var showError = function (id) {
        $('#e' + id).show();
        setTimeout(function () {
            $('#e' + id).fadeOut(600);
        }, 800);
    };
    $("#divModalForms").on('click', '#btnAddEmbargo', function () {
        if ($("#slcJuzgado").val() === "0") {
            let id = 'slcJuzgado';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($("#slcTipoEmbargo").val() === "0") {
            let id = 'slcTipoEmbargo';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($("#numTotEmbargo").val() === "") {
            let id = 'numTotEmbargo';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($("#txtValEmbargoMes").val() === "") {
            let id = 'txtValEmbargoMes';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('#datFecInicioEmb').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicial no puede estar vacia");
        } else if (parseInt($('#txtValEmbargoMes').val()) > parseInt($('#numDctoAprox').val())) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Embargo mensual no debe ser mayor que el máximo descuento");
        } else {
            let dembargo = $("#formAddEmbargo").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newembargo.php',
                data: dembargo,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableEmbargo';
                        $("#divModalForms").modal('hide');
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Embargo registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Validad fechas todos
    var validafec = function (tip, ex, up) {
        let inicio = $('#dat' + up + 'FecInicio' + tip).val();
        let fin = $('#dat' + up + 'FecFin' + tip).val();
        if (inicio > fin) {
            $('#dat' + up + 'Fec' + ex + tip).focus();
            $('#dat' + up + 'Fec' + ex + tip).val('');
            $('#edat' + up + 'Fec' + ex + tip).show();
            setTimeout(function () {
                $('#edat' + up + 'Fec' + ex + tip).fadeOut(600);
            }, 800);
            return false;
        }
        return false;
    };
    //Calcular dias incapacidad
    var caldiasincap = function (ini, fin, u, t) {
        $.ajax({
            type: 'POST',
            url: 'registrar/calcfec.php',
            data: { inicio: ini, fin: fin, up: u, tip: t },
            success: function (r) {
                if (parseInt(r) > 180) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html("Máximo 180 días");
                    $('#dat' + u + 'FecFin' + t).val('');
                } else {
                    $('#div' + u + 'CantDias' + t).html(r);
                }

            }
        });
        return false;
    };
    $('#datFecInicioInc').on('blur', function () {
        let t = 'Inc';
        let e = 'Fin';
        let u = '';
        validafec(t, e, u);
    });
    $('#datFecFinInc').on('blur', function () {
        let t = 'Inc';
        let e = 'Inicio';
        let u = '';
        validafec(t, e, u);
    });
    $('#datFecInicioVacs').on('blur', function () {
        let t = 'Vacs';
        let e = 'Fin';
        let u = '';
        validafec(t, e, u);
    });
    $('#datFecFinVacs').on('blur', function () {
        let t = 'Vacs';
        let e = 'Inicio';
        let u = '';
        validafec(t, e, u);
    });
    $('#datFecInicioLics').on('blur', function () {
        let t = 'Lics';
        let e = 'Fin';
        let u = '';
        validafec(t, e, u);
    });
    $('#datFecFinLics').on('blur', function () {
        let t = 'Lics';
        let e = 'Inicio';
        let u = '';
        validafec(t, e, u);
    });
    //Embargo
    $('#divModalForms').on('blur', '#datFecFinEmb', function () {
        let t = 'Emb';
        let e = 'Inicio';
        let u = '';
        validafec(t, e, u);
    });
    $('#divModalForms').on('blur', '#datFecInicioEmb', function () {
        let t = 'Emb';
        let e = 'Fin';
        let u = '';
        validafec(t, e, u);
    });
    $('#divModalForms').on('blur', '#datUpFecInicioEmb', function () {
        let t = 'Emb';
        let e = 'Fin';
        let u = 'Up';
        validafec(t, e, u);
    });
    $('#divModalForms').on('blur', '#datUpFecFinEmb', function () {
        let t = 'Emb';
        let e = 'Inicio';
        let u = 'Up';
        validafec(t, e, u);
    });
    //libranza
    $('#divModalForms').on('blur', '#datFecFinLib', function () {
        let t = 'Lib';
        let e = 'Inicio';
        let u = '';
        validafec(t, e, u);
    });
    $('#divModalForms').on('blur', '#datFecInicioLib', function () {
        let t = 'Lib';
        let e = 'Fin';
        let u = '';
        validafec(t, e, u);
    });
    $('#divModalForms').on('blur', '#datUpFecFinLib', function () {
        let t = 'Lib';
        let e = 'Inicio';
        let u = 'Up';
        validafec(t, e, u);
    });
    $('#divModalForms').on('blur', '#datUpFecInicioLib', function () {
        let t = 'Lib';
        let e = 'Fin';
        let u = 'Up';
        validafec(t, e, u);
    });
    //Sindicato
    $('#datFecFinSind').on('blur', function () {
        let t = 'Sind';
        let e = 'Inicio';
        let u = '';
        validafec(t, e, u);
    });
    $('#datFecInicioSind').on('blur', function () {
        let t = 'Sind';
        let e = 'Fin';
        let u = '';
        validafec(t, e, u);
    });
    $('#divUpNovSindicato').on('blur', '#datUpFecFinSind', function () {
        let t = 'Sind';
        let e = 'Inicio';
        let u = 'Up';
        validafec(t, e, u);
    });
    $('#divUpNovSindicato').on('blur', '#datUpFecInicioSind', function () {
        let t = 'Sind';
        let e = 'Fin';
        let u = 'Up';
        validafec(t, e, u);
    });
    //Incapacidad
    $('#divModalForms').on('blur', '#datFecFinIncap', function () {
        let inincap = $('#datFecInicioIncap').val();
        let finincap = $('#datFecFinIncap').val();
        let u = '';
        let t = 'Incap';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datFecInicioIncap', function () {
        let inincap = $('#datFecInicioIncap').val();
        let finincap = $('#datFecFinIncap').val();
        let u = '';
        let t = 'Incap';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datUpFecFinIncap', function () {
        let inincap = $('#datUpFecInicioIncap').val();
        let finincap = $('#datUpFecFinIncap').val();
        let u = 'Up';
        let t = 'Incap';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datUpFecInicioIncap', function () {
        let inincap = $('#datUpFecInicioIncap').val();
        let finincap = $('#datUpFecFinIncap').val();
        let u = 'Up';
        let t = 'Incap';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    //Vacaciones
    $('#divModalForms').on('blur', '#datFecFinVac', function () {
        let inincap = $('#datFecInicioVac').val();
        let finincap = $('#datFecFinVac').val();
        let u = '';
        let t = 'Vac';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datFecInicioVac', function () {
        let inincap = $('#datFecInicioVac').val();
        let finincap = $('#datFecFinVac').val();
        let u = '';
        let t = 'Vac';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datUpFecFinVac', function () {
        let inincap = $('#datUpFecInicioVac').val();
        let finincap = $('#datUpFecFinVac').val();
        let u = 'Up';
        let t = 'Vac';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datUpFecInicioVac', function () {
        let inincap = $('#datUpFecInicioVac').val();
        let finincap = $('#datUpFecFinVac').val();
        let u = 'Up';
        let t = 'Vac';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    //Licencias
    $('#divModalForms').on('blur', '#datFecFinLic', function () {
        let inincap = $('#datFecInicioLic').val();
        let finincap = $('#datFecFinLic').val();
        let u = '';
        let t = 'Lic';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datFecInicioLic', function () {
        let inincap = $('#datFecInicioLic').val();
        let finincap = $('#datFecFinLic').val();
        let u = '';
        let t = 'Lic';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datUpFecFinLic', function () {
        let inincap = $('#datUpFecInicioLic').val();
        let finincap = $('#datUpFecFinLic').val();
        let u = 'Up';
        let t = 'Lic';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datUpFecInicioLic', function () {
        let inincap = $('#datUpFecInicioLic').val();
        let finincap = $('#datUpFecFinLic').val();
        let u = 'Up';
        let t = 'Lic';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    //licencia X Luto
    $('#divModalForms').on('blur', '#datFecFinLicLuto', function () {
        let inincap = $('#datFecInicioLicLuto').val();
        let finincap = $('#datFecFinLicLuto').val();
        let u = '';
        let t = 'LicLuto';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datFecInicioLicLuto', function () {
        let inincap = $('#datFecInicioLicLuto').val();
        let finincap = $('#datFecFinLicLuto').val();
        let u = '';
        let t = 'LicLuto';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    //licencia no remunerada
    $('#divModalForms').on('blur', '#datFecFinLicNR', function () {
        let inincap = $('#datFecInicioLicNR').val();
        let finincap = $('#datFecFinLicNR').val();
        let u = '';
        let t = 'LicNR';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datFecInicioLicNR', function () {
        let inincap = $('#datFecInicioLicNR').val();
        let finincap = $('#datFecFinLicNR').val();
        let u = '';
        let t = 'LicNR';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datUpFecFinLicNR', function () {
        let inincap = $('#datUpFecInicioLicNR').val();
        let finincap = $('#datUpFecFinLicNR').val();
        let u = 'Up';
        let t = 'LicNR';
        if (inincap > finincap) {
            let e = 'Inicio';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    $('#divModalForms').on('blur', '#datUpFecInicioLicNR', function () {
        let inincap = $('#datUpFecInicioLicNR').val();
        let finincap = $('#datUpFecFinLicNR').val();
        let u = 'Up';
        let t = 'LicNR';
        if (inincap > finincap) {
            let e = 'Fin';
            validafec(t, e, u);
        } else {
            caldiasincap(inincap, finincap, u, t);
        }
    });
    //UP Embargo
    $('#modificarEmbargos').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novembargo.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar Embargo
    $("#divModalForms").on('click', '.actualizarEmb', function () {
        if ($("#numUpTotEmbargo").val() === "") {
            let id = 'numUpTotEmbargo';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($("#txtUpValEmbargoMes").val() === "" || $("#txtUpValEmbargoMes").val() === "0") {
            let id = 'txtUpValEmbargoMes';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('#datUpFecInicioEmb').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicial no puede estar vacia");
        } else if (parseInt($('#txtUpValEmbargoMes').val()) > parseInt($('#numUpDctoAprox').val())) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Embargo mensual no debe ser mayor que el máximo descuento");
        } else {
            let dupembargo = $('#formUpEmbargo').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/upembargo.php',
                data: dupembargo,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableEmbargo';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Embargo Actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Embargo
    $("#divBtnsModalDel").on('click', '#btnConfirDelEmb', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delembargo.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableEmbargo';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Embargo eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);;
                }
            }
        });
        return false;
    });
    //listar detalles embargo
    $('#modificarEmbargos').on('click', '.detalles', function () {
        let idemb = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'listar/listembargo.php',
            data: { idemb: idemb },
            success: function (r) {
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').removeClass('modal-lg');
                $('#divTamModalForms').addClass('modal-xl');
                $('#divModalForms').modal('show');
                $("#divForms").html(r);
            }
        });
    });
    //Registrar Sindicato
    $("#divModalForms").on('click', '#btnAddSindicato', function () {
        if ($("#slcSindicato").val() === "0") {
            let id = 'slcSindicato';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if ($('#txtPorcentajeSind').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Porcentaje no puede estar vacio");
        } else if ($('#datFecInicioSind').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicio no puede estar vacia");
        } else if ($('#numValSindicalizar').val() == '' || parseInt($('#numValSindicalizar').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Valor de sindicalización debe ser mayor o igual a cero");
        } else {
            let dsind = $("#formAddSindicato").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newsindicato.php',
                data: dsind,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableSindicato';
                        $('#divModalForms').modal('hide');
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Sindicato registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //UP Sindicato
    $('#modificarSindicatos').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novsindicato.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar Sindicato
    $("#divModalForms").on('click', '.actualizarSind', function () {
        let porc = parseInt($('#txtUpPorcentajeSind').val());
        if ($('#datUpFecInicioSind').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicio no puede estar vacia");
        } else if (porc <= 0 || porc >= 100) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Porcenaje de cuota sindical incorrecto");
        } else if ($('#numValSindicalizar').val() == '' || parseInt($('#numValSindicalizar').val()) < 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Valor de sindicalización debe ser mayor o igual a cero");
        } else {
            let dupsind = $('#formUpSindicato').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/upsindicato.php',
                data: dupsind,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableSindicato';
                        reloadtable(id);
                        $("#divModalForms").modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Novedad Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Sindicato
    $("#divBtnsModalDel").on('click', '#btnConfirDelSind', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delsindicato.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableSindicato';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Sindicato eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //listar detalles Sindicato
    $('#modificarSindicatos').on('click', '.detalles', function () {
        let idaporte = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'listar/listaportsind.php',
            data: { idaporte: idaporte },
            success: function (r) {
                $('#divTamModalForms').removeClass('modal-sm');
                $('#divTamModalForms').removeClass('modal-xl');
                $('#divTamModalForms').addClass('modal-lg');
                $('#divModalForms').modal('show');
                $("#divForms").html(r);
            }
        });
    });
    //Registrar Incapacidad
    $("#divModalForms").on('click', '#btnAddIncapacidad', function () {
        $('.form-control').removeClass('border-danger');
        if (!($('input[name=categoria]:checked').length)) {
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#categoria").addClass('border-danger');
            $("#categoria1").focus();
        } else if (!($('input[name=slcTipIncapacidad]:checked').length)) {
            $("#divModalError").modal('show');
            $("#divMsgError").html('Campo obligatorio');
            $("#slcTipIncapacidad").addClass('border-danger');
            $("#slcTipIncapacidad1").focus();
        } else if ($('#datFecInicioIncap').val() === '' || $('#datFecFinIncap').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
        } else {
            var dincap = $("#formAddIncapacidad").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newincapacidad.php',
                data: dincap,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableIncapacidad';
                        $('#divModalForms').modal('hide');
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Incapacidad registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //UP Incapacidad
    $('#modificarIncapacidades').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novincapacidad.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar Incapacidad
    $("#divModalForms").on('click', '.actualizarIncap', function () {
        if ($('#datUpFecInicioIncap').val() === '' || $('#datUpFecFinIncap').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
        } else {
            let dupincap = $('#formUpIncapacidad').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/upincapacidad.php',
                data: dupincap,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableIncapacidad';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Incapacidad Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Incapacidad
    $("#divBtnsModalDel").on('click', '#btnConfirDelIncap', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delincapacidad.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableIncapacidad';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Incapacidad eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Registrar Vacaciones
    $("#divModalForms").on('click', '#btnAddVacacion', function () {
        let diainac = $('#numCantDiasVac').val();
        let diahab = $('#numCantDiasHabVac').val();
        let min = parseInt($('#numDiasToCalc').attr('min'));
        if ($('#fecCorteVac').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha de corte no puede estar vacia");
        } else
            if ($("#numDiasToCalc").val() == '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html("Total días para calcular no puede ser vacio");
            } else if ($("#slcVacAnticip").val() == "0") {
                let id = 'slcVacAnticip';
                $('#' + id).focus();
                showError(id);
                bordeError(id);
            } else if ($('#datFecInicioVac').val() === '' || $('#datFecFinVac').val() === '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html("Fechas no pueden estar vacias");
            } else if (parseInt(diahab) > parseInt(diainac)) {
                let id = 'numCantDiasHabVac';
                $('#' + id).focus();
                showError(id);
                bordeError(id);
            } else if (diahab === '' || parseInt(diahab) === 0) {
                let id = 'numCantDiasHabVac';
                $('#' + id).focus();
                showError(id);
                bordeError(id);
            } else {
                let dvac = $("#formAddVacaciones").serialize();
                $.ajax({
                    type: 'POST',
                    url: 'registrar/newvacacion.php',
                    data: dvac,
                    success: function (r) {
                        if (r.trim() === 'ok') {
                            let id = 'tableVacaciones';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Vacaciones registradas correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        return false;
    });
    //UP Vacaciones
    $('#modificarVacaciones').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novvacaciones.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar Vacaciones
    $("#divModalForms").on('click', '.actualizarVac', function () {
        let diainac = $('#numCantDiasVac').val();
        let diahab = $('#numCantDiasHabVac').val();
        let min = parseInt($('#numDiasToCalc').attr('min'));
        let fmin = $('#fecCorteVac').attr('min');
        if ($('#fecCorteVac').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha de corte no puede estar vacia");
        } else /*if ($('#fecCorteVac').val() < fmin) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha debe ser mayor o igual a " + fmin);
        } else */if ($("#numDiasToCalc").val() == '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html("Total días para calcular no puede ser vacio o menor a " + min);
            } else if ($('#datFecInicioVac').val() === '' || $('#datFecFinVac').val() === '') {
                $('#divModalError').modal('show');
                $('#divMsgError').html("Fechas no pueden estar vacias");
            } else if (parseInt(diahab) > parseInt(diainac)) {
                let id = 'numCantDiasHabVac';
                $('#' + id).focus();
                showError(id);
                bordeError(id);
            } else if (diahab === '' || parseInt(diahab) === 0) {
                let id = 'numCantDiasHabVac';
                $('#' + id).focus();
                showError(id);
                bordeError(id);
            } else {
                let dupvac = $('#formUpVacaciones').serialize();
                $.ajax({
                    type: 'POST',
                    url: 'actualizar/upvacacion.php',
                    data: dupvac,
                    success: function (r) {
                        if (r.trim() === 'ok') {
                            let id = 'tableVacaciones';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Vacaciones Actualizadas correctamente");
                        } else {
                            $('#divModalError').modal('show');
                            $('#divMsgError').html(r);
                        }
                    }
                });
            }
        return false;
    });
    //Eliminar Vacaciones
    $("#divBtnsModalDel").on('click', '#btnConfirDelVac', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delvacacion.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableVacaciones';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Vacaciones eliminadas correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Registrar Licencia MP
    $("#divModalForms").on('click', '#btnAddLicencia', function () {
        let diainac = $('#numCantDiasLic').val();
        let diahab = $('#numCantDiasHabLic').val();
        if ($('#datFecInicioLic').val() === '' || $('#datFecFinLic').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
            return false;
        } else if (parseInt(diahab) > parseInt(diainac)) {
            let id = 'numCantDiasHabLic';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if (diahab === '' || parseInt(diahab) === 0) {
            let id = 'numCantDiasHabLic';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else {
            let dvac = $("#formAddLicencia").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newlicencia.php',
                data: dvac,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableLicencia';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Licencia MP registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //UP Licencia
    $('#modificarLicencias').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novlicencia.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar Licencia
    $("#divModalForms").on('click', '.actualizarLic', function () {
        let diainac = $('#numUpCantDiasLic').val();
        let diahab = $('#numUpCantDiasHabLic').val();
        if ($('#datUpFecInicioLic').val() === '' || $('#datUpFecFinLic').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
        } else if (parseInt(diahab) > parseInt(diainac)) {
            let id = 'numUpCantDiasHabLic';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if (diahab === '' || parseInt(diahab) === 0) {
            let id = 'numUpCantDiasHabLic';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else {
            let duplic = $('#formUpLicencia').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/uplicencia.php',
                data: duplic,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableLicencia';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Licencia Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Licencia
    $("#divBtnsModalDel").on('click', '#btnConfirDelLic', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/dellicencia.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableLicencia';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Licencia eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Registrar Licencia no remumerada 
    $("#divModalForms").on('click', '#btnAddLicNR', function () {
        let diainac = $('#numCantDiasLicNR').val();
        let diahab = $('#numCantDiasHabLicNR').val();
        if ($('#datFecInicioLicNR').val() === '' || $('#datFecFinLicNR').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
            return false;
        } else if (parseInt(diahab) > parseInt(diainac)) {
            let id = 'numCantDiasHabLicNR';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if (diahab === '' || parseInt(diahab) === 0) {
            let id = 'numCantDiasHabLicNR';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else {
            let dvac = $("#formAddLicenciaNR").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newlicencianr.php',
                data: dvac,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableLicenciaNR';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Licencia no remunerada registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $("#divModalForms").on('click', '#btnAddLicLuto', function () {
        let diainac = $('#numCantDiasLicLuto').val();
        let diahab = $('#numCantDiasHabLicLuto').val();
        if ($('#datFecInicioLicLuto').val() === '' || $('#datFecFinLicLuto').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
            return false;
        } else if (parseInt(diahab) > parseInt(diainac)) {
            let id = 'numCantDiasHabLicLuto';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if (diahab === '' || parseInt(diahab) === 0) {
            let id = 'numCantDiasHabLicLuto';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else {
            let dlic = $("#formAddLicLuto").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newlicluto.php',
                data: dlic,
                success: function (r) {
                    if (r === 'ok') {
                        let id = 'tableLuto';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Licencia por Luto registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $("#divModalForms").on('click', '#btnUpLicLuto', function () {
        let diainac = $('#numCantDiasLicLuto').val();
        let diahab = $('#numCantDiasHabLicLuto').val();
        if ($('#datFecInicioLicLuto').val() === '' || $('#datFecFinLicLuto').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
            return false;
        } else if (parseInt(diahab) > parseInt(diainac)) {
            let id = 'numCantDiasHabLicLuto';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if (diahab === '' || parseInt(diahab) === 0) {
            let id = 'numCantDiasHabLicLuto';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else {
            let dlic = $("#formUpLicLuto").serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/uplicluto.php',
                data: dlic,
                success: function (r) {
                    if (r === 'ok') {
                        let id = 'tableLuto';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Licencia por Luto registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $("#divModalForms").on('click', '#btnAddIndemVac', function () {
        if ($('#datFecInicioLicNR').val() === '' || $('#datFecFinLicNR').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
            return false;
        } else if ($('#numCantDiasLicNR').val() === '' || parseInt($('#numCantDiasLicNR').val()) === 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Cantidad de dias debe ser mayor a 0");
            return false;
        } else {
            let dvac = $("#formAddIndemVac").serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/newindemvac.php',
                data: dvac,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableIndemnizaVac';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Indemnización de vacaciones registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $('#modificaIndemnVac').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novindemvac.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#divModalForms").on('click', '.actualizarIndemVac', function () {
        if ($('#datUpFecInicioLicNR').val() === '' || $('#datUpFecFinLicNR').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
        } else {
            let duplic = $('#formUpIndemVacac').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/upindemnizavacac.php',
                data: duplic,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableIndemnizaVac';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Indemnización Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //UP Licencia no remunerada
    $('#modificarLicenciasNR').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novlicenciannr.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#modificarLuto').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/up_novlicluto.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //Actualizar Licencia no remunerada
    $("#divModalForms").on('click', '.actualizarLicNR', function () {
        let diainac = $('#numUpCantDiasLic').val();
        let diahab = $('#numUpCantDiasHabLic').val();
        if ($('#datUpFecInicioLicNR').val() === '' || $('#datUpFecFinLicNR').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fechas no pueden estar vacias");
        } else if (parseInt(diahab) > parseInt(diainac)) {
            let id = 'numUpCantDiasHabLicNR';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else if (diahab === '' || parseInt(diahab) === 0) {
            let id = 'numUpCantDiasHabLicNR';
            $('#' + id).focus();
            showError(id);
            bordeError(id);
        } else {
            let duplic = $('#formUpLicenciaNR').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/uplicencianr.php',
                data: duplic,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableLicenciaNR';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Licencia Actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Eliminar Licencia no remunerada 
    $("#divBtnsModalDel").on('click', '#btnConfirDelLicNR', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/dellicencianr.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableLicenciaNR';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Licencia eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Eliminar Licencia por Luto
    $("#divBtnsModalDel").on('click', '#btnConfirDelLicLuto', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/dellicencialuto.php',
            data: { id: id },
            success: function (r) {
                if (r === 'ok') {
                    let id = 'tableLuto';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Licencia eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Confirmar eliminar
    var confdel = function (i, t) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: window.urlin + '/nomina/empleados/eliminar/confirdel.php',
            data: { id: i, tip: t }
        }).done(function (res) {
            $('#divModalConfDel').modal('show');
            $('#divMsgConfdel').html(res.msg);
            $('#divBtnsModalDel').html(res.btns);
        });
        return false;
    };
    //Borrar EPS confirmar
    $('#modificarEpss').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Eps';
        confdel(id, tip);
    });
    //Borrar novedad ARL confirmar
    $('#modificarArls').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Arl';
        confdel(id, tip);
    });
    //Borrar novedad AFP confirmar
    $('#modificarAfps').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Afp';
        confdel(id, tip);
    });
    //Borrar Libranza confirmar
    $('#modificarLibranzas').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Lib';
        confdel(id, tip);
    });
    //Borrar Embargo confirmar
    $('#modificarEmbargos').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Emb';
        confdel(id, tip);
    });
    //Borrar Sindicato confirmar
    $('#modificarSindicatos').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Sind';
        confdel(id, tip);
    });
    //Borrar Incapacidad confirmar
    $('#modificarIncapacidades').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Incap';
        confdel(id, tip);
    });
    //Borrar Vacaciones confirmar
    $('#modificarVacaciones').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Vac';
        confdel(id, tip);
    });
    //Borrar Licencia confirmar
    $('#modificarLicencias').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Lic';
        confdel(id, tip);
    });
    //Borrar Licencia No Remunerada confirmar
    $('#modificarLicenciasNR').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'LicNR';
        confdel(id, tip);
    });
    //Borrar Licencia por Luto confirmar
    $('#modificarLuto').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'LicLuto';
        confdel(id, tip);
    });
    //Confirmar eliminar empleado
    $("#modificarEmpleados").on('click', '.eliminar', function () {
        let idempleado = $(this).attr('value');
        window.rowdel = $(this).closest("tr").get(0);
        $.ajax({
            type: 'POST',
            url: 'eliminar/busempleado.php',
            data: { idempleado: idempleado },
            success: function (r) {
                $('#divModalConfDel').modal('show');
                $('#divMsgConfdel').html(r);
                $('#divBtnsModalDel').html('<button type="button" class="btn btn-danger btn-sm" id="btnConfirDelEmpleado">Eliminar</button><button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>');
            }
        });
        return false;
    });
    //cambiar estado empleado
    $("#tdEstado button").click(function () {
        let idemp = $(this).val();
        let divestM = "divIconoshow" + idemp;
        let divestO = "divIcono" + idemp;
        $('#' + divestM).hide();
        $.ajax({
            type: 'POST',
            url: 'actualizar/upestado.php',
            data: { idemp: idemp },
            success: function (r) {
                $('#' + divestO).html(r);
                $('#' + divestO).show();
            }
        });
        return false;
    });
    var municipios = function (dpto, t) {
        $.ajax({
            type: 'POST',
            url: window.urlin + '/nomina/empleados/registrar/slcmunicipio.php',
            data: { dpto: dpto },
            success: function (r) {
                $('#slcMunicipio' + t).html(r);
            }
        });
    };
    //Cambiar Municipios por departamento
    $('#slcDptoEmp').on('change', function () {
        let dpto = $(this).val();
        municipios(dpto, 'Emp');
        return false;
    });
    $('#slcDptoExp').on('change', function () {
        let dpto = $(this).val();
        municipios(dpto, 'Exp');
        return false;
    });
    $('#slcDptoNac').on('change', function () {
        let dpto = $(this).val();
        municipios(dpto, 'Nac');
        return false;
    });
    //Calcular valor máximo embargo mensual
    var calmaxemb = function (p, up) {
        let dat = p + '&up=' + up;
        $.ajax({
            type: 'POST',
            url: 'registrar/slctipemb.php',
            data: dat,
            success: function (r) {
                $('#div' + up + 'DctoAprox').html(r);
            }
        });
    };
    $('#divModalForms').on('change', '#slcTipoEmbargo', function () {
        let tipemb = $(this).val();
        let u = '';
        calmaxemb(tipemb, u);
        return false;
    });
    //up
    $('#divModalForms').on('change', '#slcUpTipoEmbargo', function () {
        let tipemb = $(this).val();
        let o = 'Up';
        calmaxemb(tipemb, o);
        return false;
    });
    //Calcular % embargo - libranza
    var calporcemb = function (v, s, u, t) {
        let dat = 'val=' + v + '&sal=' + s;
        $.ajax({
            type: 'POST',
            url: 'registrar/cal_porcent.php',
            data: dat,
            success: function (r) {
                $('#txt' + u + 'Porc' + t + 'Mes').val(r);
            }
        });
    };
    $("#divModalForms").on('keyup', '#txtValEmbargoMes', function () {
        let val = $(this).val();
        let sal = $('#txtSalBas').val();
        let up = '';
        let tip = 'Emb';
        calporcemb(val, sal, up, tip);
        return false;
    });
    $("#divModalForms").on('keyup', '#txtUpValEmbargoMes', function () {
        let val = $(this).val();
        let sal = $('#txtSalBas').val();
        let up = 'Up';
        let tip = 'Emb';
        calporcemb(val, sal, up, tip);
        return false;
    });
    $("#divModalForms").on('keyup', '#txtValLibMes', function () {
        let val = $(this).val();
        let sal = $('#txtSalBas').val();
        let up = '';
        let tip = 'Lib';
        calporcemb(val, sal, up, tip);
        return false;
    });
    $("#divModalForms").on('keyup', '#txtUpValLibMes', function () {
        let val = $(this).val();
        let sal = $('#txtSalBas').val();
        let up = 'Up';
        let tip = 'Lib';
        calporcemb(val, sal, up, tip);
        return false;
    });
    //Calcular val mensual
    var calvalmesemb = function (v, s, u, t) {
        let dat = 'val=' + v + '&sal=' + s;
        $.ajax({
            type: 'POST',
            url: 'registrar/cal_valembar.php',
            data: dat,
            success: function (r) {
                $('#txt' + u + 'Val' + t + 'Mes').val(r);
            }
        });
    };
    $("#divModalForms").on('keyup', '#txtPorcEmbMes', function () {
        let val = $(this).val();
        let sal = $('#txtSalBas').val();
        let up = '';
        let tip = 'Embargo';
        calvalmesemb(val, sal, up, tip);
        return false;
    });
    $("#divModalForms").on('keyup', '#txtUpPorcEmbMes', function () {
        let val = $(this).val();
        let sal = $('#txtSalBas').val();
        let up = 'Up';
        let tip = 'Embargo';
        calvalmesemb(val, sal, up, tip);
        return false;
    });
    $("#divModalForms").on('keyup', '#txtPorcLibMes', function () {
        let val = $(this).val();
        let sal = $('#txtSalBas').val();
        let up = '';
        let tip = 'Lib';
        calvalmesemb(val, sal, up, tip);
        return false;
    });
    $("#divModalForms").on('keyup', '#txtUpPorcLibMes', function () {
        let val = $(this).val();
        let sal = $('#txtSalBas').val();
        let up = 'Up';
        let tip = 'Lib';
        calvalmesemb(val, sal, up, tip);
        return false;
    });
    var rowdel = function () {
        $(document).ready(function () {
            var table = $('.table').DataTable();
            table.row(window.rowdel).remove().draw();
        });
    };
    var reloadtable = function (nom) {
        $(document).ready(function () {
            var table = $('#' + nom).DataTable();
            table.ajax.reload();
        });
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
        var id = $('#idEmpNovEps').val();
        $('#dataTable').DataTable({
            'pageLength': 100,
            language: setIdioma,
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, 'TODO'],
            ],
        });
        //dataTable lista de empleados
        $('#tableListEmpleados').DataTable({
            dom: setdom,
            buttons: [{
                action: function () {
                    window.location = 'registrar/formaddempleado.php';
                }
            }],
            language: setIdioma,
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableListEmpleados').wrap('<div class="overflow" />');
        //dataTable lista de empleados
        $('.dataTableMes').DataTable({
            dom: setdom,
            language: setIdioma,
            "order": [
                [0, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('.dataTableMes').wrap('<div class="overflow" />');
        //dataTable Contratos de empleados
        $('#tableListContratosEmp').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_contrato_emp.php", function (he) {
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
                url: 'datos/listar/datos_list_contratos.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'contrato' },
                { 'data': 'tipo' },
                { 'data': 'no_doc' },
                { 'data': 'nombre' },
                { 'data': 'fec_ini' },
                { 'data': 'fec_fin' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableListContratosEmp').wrap('<div class="overflow" />');
        //dataTable EPS
        $('#tableEps').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_nov_eps.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_eps.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': "nombre_eps" },
                { 'data': "nit" },
                { 'data': "fec_afiliacion" },
                { 'data': 'fec_retiro' },
                { 'data': 'botones' },
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
        $('#tableEps').wrap('<div class="overflow" />');
        //dataTable ARL
        $('#tableArl').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_nov_arl.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_arl.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': "nombre_arl" },
                { 'data': "nitarl" },
                { 'data': "riesgo" },
                { 'data': "fec_afiliacion" },
                { 'data': 'fec_retiro' },
                { 'data': 'botones' },
            ],
            "order": [
                [3, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableArl').wrap('<div class="overflow" />');
        $('#tableCCostoEmp').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_ccosto.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_ccosto.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': "id" },
                { 'data': "nombre" },
                { 'data': "fecha" },
                { 'data': "botones" },
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
        $('#tableCCostoEmp').wrap('<div class="overflow" />');
        //dataTable AFP
        $('#tableAfp').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_nov_afp.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_afp.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': "nombre_afp" },
                { 'data': "nitafp" },
                { 'data': "fec_afiliacion" },
                { 'data': 'fec_retiro' },
                { 'data': 'botones' },
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
        $('#tableAfp').wrap('<div class="overflow" />');
        $('#tableFCesan').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_nov_fc.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_fc.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': "nombre_fc" },
                { 'data': "nitfc" },
                { 'data': "fec_afiliacion" },
                { 'data': 'fec_retiro' },
                { 'data': 'botones' },
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
        $('#tableFCesan').wrap('<div class="overflow" />');
        //dataTable Libranza
        $('#tableLibranza').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_libranza.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_libranza.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'nom_banco' },
                { 'data': 'valor_total' },
                { 'data': 'cuotas' },
                { 'data': 'val_mes' },
                { 'data': 'val_pagado' },
                { 'data': 'cuotas_pag' },
                { 'data': 'fecha_inicio' },
                { 'data': 'fecha_fin' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [7, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableLibranza').wrap('<div class="overflow" />');
        //dataTable Embargo
        $('#tableEmbargo').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_embargo.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_embargo.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'juzgado' },
                { 'data': 'valor_total' },
                { 'data': 'val_mes' },
                { 'data': 'val_pagado' },
                { 'data': 'fecha_inicio' },
                { 'data': 'fecha_fin' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [5, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableEmbargo').wrap('<div class="overflow" />');
        //dataTable Sindicato
        $('#tableSindicato').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_sindicato.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_sindicato.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'sindicato' },
                { 'data': 'porcentaje' },
                { 'data': 'cantidad_aportes' },
                { 'data': 'total_aportes' },
                { 'data': 'fec_inicio' },
                { 'data': 'fec_fin' },
                { 'data': 'val_sind' },
                { 'data': 'botones' },
            ],
            "order": [
                [5, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableSindicato').wrap('<div class="overflow" />');
        //dataTable Incapacidad
        $('#tableIncapacidad').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_incapacidad.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_incapacidad.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'tipo' },
                { 'data': 'fec_inicio' },
                { 'data': 'fec_fin' },
                { 'data': 'dias' },
                { 'data': 'valor' },
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
        $('#tableIncapacidad').wrap('<div class="overflow" />');
        //dataTable Vacaciones
        $('#tableVacaciones').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_vacaciones.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_vacaciones.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'anticipada' },
                { 'data': 'fec_inicio' },
                { 'data': 'fec_fin' },
                { 'data': 'dias_inactivo' },
                { 'data': 'dias_hab' },
                { 'data': 'corte' },
                { 'data': 'dias_liq' },
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
        $('#tableVacaciones').wrap('<div class="overflow" />');
        //dataTable Licencia
        $('#tableLicencia').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_licenciamp.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_licencia.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'fec_inicio' },
                { 'data': 'fec_fin' },
                { 'data': 'dias_inactivo' },
                { 'data': 'dias_hab' },
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
        $('#tableLicencia').wrap('<div class="overflow" />');
        //dataTable Licencia no remunerada
        $('#tableLuto').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_lic_luto.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_lic_luto.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'fec_inicio' },
                { 'data': 'fec_fin' },
                { 'data': 'dias_inactivo' },
                { 'data': 'dias_hab' },
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
        $('#tableLuto').wrap('<div class="overflow" />');
        $('#tableLicenciaNR').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_licencianr.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_licenciaNR.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'fec_inicio' },
                { 'data': 'fec_fin' },
                { 'data': 'dias_inactivo' },
                { 'data': 'dias_hab' },
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
        $('#tableLicenciaNR').wrap('<div class="overflow" />');
        $('#tableIndemnizaVac').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_indemnizavac.php", { id: id }, function (he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_indemnzavac.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'fec_inicio' },
                { 'data': 'fec_fin' },
                { 'data': 'dias_indemniza' },
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
            "pageLength": -1
        });
        $('#tableIndemnizaVac').wrap('<div class="overflow" />');
        $('#tableOtroDcto').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/form_add_descuento.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/otros_descuentos.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id_dcto' },
                { 'data': 'fecha' },
                { 'data': 'tipo' },
                { 'data': 'concepto' },
                { 'data': 'valor' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "desc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableOtroDcto').wrap('<div class="overflow" />');
    });
    //contratacion empleados
    //Nuevo contrato
    $('#divForms').on('click', '#btnAddContratoEmp', function () {
        if ($('#slcEmpleado').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir un empleado");
        } else if ($('#datFecInicio').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicio no puede ser vacía");
        } else if ($('#datFecFin').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Fin no puede ser vacía");
        } else if (Date.parse($('#datFecInicio').val()) > Date.parse($('#datFecFin').val())) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Fin no puede ser menor que Fecha Inicio");
        } else {
            let datos = $('#formAddContratoEmpleado').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_contrato_empleado.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableListContratosEmp';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Contrato registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //actualizar contrato empleado
    $('#modificarListContratosEmps').on('click', '.editar', function () {
        let idupce = $(this).attr('value');
        $.post("datos/actualizar/up_contrato_empleado.php", { idupce: idupce }, function (he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divForms').on('click', '#btnActContratoEmpleado', function () {
        if ($('#datFecInicio').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Inicio no puede ser vacía");
        } else if ($('#datFecFin').val() === '' && $('#tip_ce').val() != '2') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Fin no puede ser vacía");
        } else if (Date.parse($('#datFecInicio').val()) > Date.parse($('#datFecFin').val())) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Fecha Fin no puede ser menor que Fecha Inicio");
        } else {
            let datos = $('#formActContratoEmpleado').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_contrato_empleado.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableListContratosEmp';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Contrato registrado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Borrar contrato confirmar
    $('#modificarListContratosEmps').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'ContraEmpl';
        confdel(id, tip);
    });
    //eliminar contrato empleado
    $("#divBtnsModalDel").on('click', '#btnConfirDelContraEmpl', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_contrato_empleado.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableListContratosEmp';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Contrato de empleado eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Soporte nomina electronica
    $('.dataTableMes').on('click', '.soporteNE', function () {
        let idsoporte = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'exportar/pdf.php',
            data: { idsoporte: idsoporte },
            success: function (r) {
                if (r == 0) {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html("No se puede generar Soporte");
                } else {
                    window.open(r, '_blank');
                }
            }
        });
        return false;
    });
    //botones empleados
    $('#modificarEmpleados').on('click', '.editar', function () {
        let id_det = $(this).attr('value');
        $('<form action="actualizar/formupempleado.php" method="post"><input type="hidden" name="idUpEmpl" value="' + id_det + '" /></form>')
            .appendTo('body').submit();
    });
    $('#modificarEmpleados').on('click', '.horas', function () {
        let id = $(this).attr('value');
        $('<form action="../extras/horas/registrar/registrohe.php" method="post"><input type="hidden" name="idEmHe" value="' + id + '" /></form>')
            .appendTo('body').submit();
    });
    $('#modificarEmpleados').on('click', '.viaticos', function () {
        let id = $(this).attr('value');
        $('<form action="../extras/viaticos/registrar/registroviatico.php" method="post"><input type="hidden" name="idEmViat" value="' + id + '" /></form>')
            .appendTo('body').submit();
    });
    $('#modificarEmpleados').on('click', '.detalles', function () {
        let id = $(this).attr('value');
        $('<form action="detallesempleado.php" method="post"><input type="hidden" name="idDetalEmpl" value="' + id + '" /></form>')
            .appendTo('body').submit();
    });
    $('#modificarVacaciones').on('click', '.imprimir', function () {
        let id = $(this).attr('value');
        $.post('../soportes/exportar/imp_vacaciones.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnAddOtroDcto', function () {
        var opcion = $(this).attr('text');
        $('.form-control').removeClass('is-invalid');
        if ($('#datFecDcto').val() == '') {
            $('#datFecDcto').addClass('is-invalid');
            $('#datFecDcto').focus();
            mjeError("Debe ingresar una fecha válida");
        } else if ($('#sclTipoDcto').val() == '0') {
            $('#sclTipoDcto').addClass('is-invalid');
            $('#sclTipoDcto').focus();
            mjeError("Debe seleccionar un tipo de descuento");
        } else if (Number($('#numValDcto').val()) <= 0) {
            $('#numValDcto').addClass('is-invalid');
            $('#numValDcto').focus();
            mjeError("Debe ingresar el valor a descontar");
        } else if (Number($('#numValDcto').val()) <= 0) {
            $('#numValDcto').addClass('is-invalid');
            $('#numValDcto').focus();
            mjeError("Debe ingresar el valor a descontar");
        } else {
            var datos = $('#formAddOtroDcto').serialize() + '&idEmpl=' + $('#idEmpNovEps').val();
            if (opcion == '1') {
                var url = 'registrar/newdescuento.php';
            } else {
                var url = 'actualizar/updescuento.php'
            }
            $.ajax({
                type: 'POST',
                url: url,
                data: datos,
                success: function (r) {
                    if (r.trim() === 'ok') {
                        $('#divModalForms').modal('hide');
                        $('#tableOtroDcto').DataTable().ajax.reload();
                        mje("Proceso realizado correctamente");
                    } else {
                        mjeError(r);
                    }
                }
            });
            return false;
        }
    });

    $('#modificaOtroDcto').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post('datos/actualizar/up_descuento.php', { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnUpOtroDcto', function () {
        $('.form-control').removeClass('border-danger');
        if ($('#datFecDcto').val() == '') {
            $('#datFecDcto').addClass('border-danger');
            $('#datFecDcto').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe ingresar una fecha válida");
        } else if ($('#numValDcto').val() == '' || Number($('#numValDcto').val()) == 0) {
            $('#numValDcto').addClass('border-danger');
            $('#numValDcto').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe ingresar el valor a descontar");
        } else if ($('#txtConDcto').val() == '') {
            $('#txtConDcto').addClass('border-danger');
            $('#txtConDcto').focus();
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe ingresar el concepto de descuento");
        } else {
            var datos = $('#formUpOtroDcto').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/updescuento.php',
                data: datos,
                success: function (r) {
                    if (r.trim() === 'ok') {
                        let id = 'tableOtroDcto';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Descuento actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
            return false;
        }
    });
    $('#modificaOtroDcto').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'OtroDcto';
        confdel(id, tip);
    });
    $("#divBtnsModalDel").on('click', '#btnConfirDelOtroDcto', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/deldescuento.php',
            data: { id: id },
            success: function (r) {
                if (r.trim() === 'ok') {
                    let id = 'tableOtroDcto';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Desceunto a empleado eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#modificarLibranzas').on('click', '.estado', function () {
        var id = $(this).attr('value');
        var est = $(this).attr('estado');
        $.ajax({
            type: 'POST',
            url: 'actualizar/upestado_lib.php',
            data: { id: id, est: est },
            success: function (r) {
                if (r.trim() === 'ok') {
                    let id = 'tableLibranza';
                    reloadtable(id);
                }
                else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $("#modificaOtroDcto").on('click', '.estado', function () {
        var id = $(this).attr('value');
        var est = $(this).attr('estado');
        $.ajax({
            type: 'POST',
            url: 'actualizar/upestado_dcto.php',
            data: { id: id, est: est },
            success: function (r) {
                if (r.trim() === 'ok') {
                    $('#tableOtroDcto').DataTable().ajax.reload();
                }
                else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });

    $('#modificarEmbargos').on('click', '.estado', function () {
        var id = $(this).attr('value');
        var est = $(this).attr('estado');
        $.ajax({
            type: 'POST',
            url: 'actualizar/upestado_emb.php',
            data: { id: id, est: est },
            success: function (r) {
                if (r.trim() === 'ok') {
                    let id = 'tableEmbargo';
                    reloadtable(id);
                }
                else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
})(jQuery);