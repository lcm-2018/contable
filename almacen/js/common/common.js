//CONFIRMAIÓN DE BORRAR REGISTROS
var confirmar_del = function(tipo, id) {
    var msg = "Esta seguro de esta Operación?";
    let btns = '<button class="btn btn-primary btn-sm" id="' + tipo + '" value=' + id + '>Aceptar</button><button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>';
    $('#divModalConfDel').modal('show');
    $('#divMsgConfdel').html(msg);
    $('#divBtnsModalDel').html(btns);
    return false;
};

//CONFIRMACIÓN DE CERRAR, ANULAR PARA ORDENES DE INGRESO, ORDENES DE EGRESO, TRASLADOS, ETC.
var confirmar_proceso = function(tipo) {
    var msg = "Esta seguro de esta Operación?, <p style='color:red'>ESTE PROCESO ES IRREVERSIBLE</p>";
    let btns = '<button class="btn btn-primary btn-sm" id="' + tipo + '">Continuar</button><button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>';
    $('#divModalConfDel').modal('show');
    $('#divMsgConfdel').html(msg);
    $('#divBtnsModalDel').html(btns);
    return false;
};

//CONFIRMACIÓN PROCESO SOLICITANDO UN MENSAJE
var confirmar_proceso_msg = function(tipo, msg) {
    let btns = '<button class="btn btn-primary btn-sm" id="' + tipo + '">Continuar</button><button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>';
    $('#divModalConfDel').modal('show');
    $('#divMsgConfdel').html(msg);
    $('#divBtnsModalDel').html(btns);
    return false;
};


var reloadtable = function(nom, pag = 0) {
    $(document).ready(function() {
        var table = $('#' + nom).DataTable();
        table.page(pag).draw(false);
        //table.ajax.reload();
    });
};

//VERIFICA SI UN OBJETO ES VACIÓ Y LO RESALTA. SE PUEDE ENVIAR MESAJE COMO PARAMETRO PARA VISUALIZARSE
var verifica_vacio = function(objeto, msg = "") {
    var error = 0;
    if (objeto.val().trim() == "") {
        objeto.addClass('is-invalid');
        objeto.focus();
        error = 1;
        if (msg != "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html(msg);
        }
    }
    return error;
};

//VERIFICA SI UN OBJETO ES VACIÓ Y RESALTA OTRO OBJETO RELACIONADO. SE PUEDE ENVIAR MESAJE COMO PARAMETRO PARA VISUALIZARSE
var verifica_vacio_2 = function(objeto1, objeto2, msg = "") {
    var error = 0;
    if (objeto1.val().trim() == "") {
        objeto2.addClass('is-invalid');
        objeto2.focus();
        error = 1;
        if (msg != "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html(msg);
        }
    }
    return error;
};

var showError = function(error) {
    $('#divModalError').modal('show');
    $('#divMsgError').html(error);
}

//VERIFICA SI UN OBJETO TIENE UN VALOR MÍNIMO ESPECÍFICO Y RESALTA OTRO OBJETO RELACIONADO
var verifica_valmin_2 = function(objeto1, objeto2, val = 0, msg = "") {
    var error = 0;
    if (parseInt(objeto1.val()) < val) {
        objeto2.addClass('is-invalid');
        objeto2.focus();
        error = 1;
        if (msg != "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html(msg);
        }
    }
    return error;
};

//VERIFICA SI UN OBJETO TIENE UN VALOR MÍNIMO ESPECÍFICO
var verifica_valmin = function(objeto, val = 0, msg = "") {
    var error = 0;
    if (parseInt(objeto.val()) < val) {
        objeto.addClass('is-invalid');
        objeto.focus();
        error = 1;
        if (msg != "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html(msg);
        }
    }
    return error;
};

//VERIFICA SI UN OBJETO TIENE UN VALOR MÁXIMO ESPECÍFICO
var verifica_valmax = function(objeto, val = 500, msg = "") {
    var error = 0;
    if (parseInt(objeto.val()) > val) {
        objeto.addClass('is-invalid');
        objeto.focus();
        error = 1;
        if (msg != "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html(msg);
        }
    }
    return error;
};

$(function() {
    $('#divModalForms').on('shown.bs.modal', function() {
        let $modal = $('#divTamModalReg, #divTamModalBus, #divTamModalImp, #divTamModalAux');
        $modal.css({}).draggable({
            containment: "window"
        });
    });

    //Dato numerico
    $("#divModalForms,#divModalReg,#divModalBus").on("input", ".number", function() {
        var that = $(this);
        that.val(that.val().replace(/[^0-9]/g, ''));
        if (isNaN(that.val())) {
            e.preventDefault();
        }
    });

    //Dato numerico entero >=0
    $("#divModalForms,#divModalReg,#divModalBus").on("input", ".numberint", function() {
        var that = $(this);
        that.val(that.val().replace(/[^0-9]/g, ''));
        if (that.val().substring(0, 1).trim() == '0') {
            that.val('0');
        }
        if (isNaN(that.val())) {
            e.preventDefault();
        }
    });

    //Dato numerico flotante
    $("#divModalForms,#divModalReg,#divModalBus").on("input", ".numberfloat", function() {
        var that = $(this);
        that.val(that.val().replace(/[^0-9\.]/g, ''));
        if (that.val().substring(0, 1).trim() == '0' && that.val().substring(1, 2).trim() != '.') {
            that.val('0');
        }
        if (that.val().split('.').length >= 3) {
            that.val(that.val().substring(0, that.val().length - 1));
        }
        if (isNaN(hat.val())) {
            e.preventDefault();
        }
    });

    //Dato letras, numeros, y -
    $("#divModalForms,#divModalReg,#divModalBus").on("input", ".valcode", function() {
        var that = $(this);
        that.val(that.val().replace(/[^0-9a-zA-Z\-]/g, ''));
        if (isNaN(that.val())) {
            e.preventDefault();
        }
    });

    //Boton de Imprimir de formulario Impresión
    $('#divModalImp').on('click', '#btnImprimir', function() {
        function imprSelec() {
            var div = $('#areaImprimir').html();
            var ventimp = window.open(' ', '');
            ventimp.document.write('<!DOCTYPE html><html><head><title>Imprimir</title></head><body>');
            ventimp.document.write('<div>' + div + '</div>');
            ventimp.document.write('</body></html>');
            ventimp.print();
            ventimp.close();
        }
        $('#divModalForms .collapse').addClass('show');
        imprSelec();
    });

    //Boton de Excel de formulario Impresión
    $('#divModalImp').on('click', '#btnExcelEntrada', function() {
        let xls = ($('#areaImprimir').html());
        var encoded = window.btoa(xls);
        $('<form action="../common/reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encoded + '" /></form>').appendTo('body').submit();
    });
});

//Funcion para validar numero enteros
function number_int(that) {
    that.val(that.val().replace(/[^0-9]/g, ''));
    if (that.val().substring(0, 1).trim() == '0') {
        that.val('0');
    }
    if (isNaN(that.val())) {
        e.preventDefault();
    }
}