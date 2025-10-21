const FormInfFinanciero = (tipo) => {
    let url = '';
    switch (tipo) {
        case 1:
            url = 'form_sia.php';
            break;
        case 2:
            url = 'formulario_informe_financiero.php?tipo=2';
            break;
        case 3:
            url = 'formulario_informe_financiero.php?tipo=3';
            break;
        case 4:
            url = 'formulario_informe_financiero.php?tipo=4';
            break;
        case 5:
            url = 'form_ejec_pptal.php';
            break;
        case 6:
            url = 'form_ft004.php?tipo=6';
            break;
        default:
            console.error('Tipo de informe no válido');
            return;
    }

    $.ajax({
        type: 'POST',
        url: url,
        data: { tipo: tipo },
        success: function (response) {
            $('#areaReporte').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar el formulario:', error);
        }
    });
}

const InformeFinanciero = (boton) => {
    var tipo = $(boton).val();
    var periodo = $('#periodo').val();
    var url = '';
    if ($('#tp_presupuesto').length && $('#tp_presupuesto').val() == '0') {
        mjeError('Debe seleccionar un tipo de presupuesto');
        return false;
    }
    if (periodo === '0') {
        mjeError('Debe seleccionar un periodo');
        return false;
    }
    switch (tipo) {
        case '1':
            url = 'formatos_sia.php';
            break;
        case '2':
            url = 'informe_cuipo.php';
            break;
        case '3':
            url = 'informe_ejecucion.php';
            break;
        case '4':
            if ($('#tp_presupuesto').val() == '1') {
                url = 'inf_siho_ingresos.php';
            } else {
                url = 'inf_siho_gastos.php';
            }
            break;
        case '5':
            url = 'inf_ft004.php';
            break;
    }
    InactivaBoton(boton);
    $.ajax({
        type: 'POST',
        url: url,
        data: { periodo: periodo },
        success: function (response) {
            $('#areaImprimir').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar el informe:', error);
        }
    }).always(function () {
        ActivaBoton(boton);
    });
}

const LoadInforme = (id) => {
    switch (id) {
        case 1:
            url = 'inf_bancos.php';
            break;
        case 2:
            url = 'inf_traslados.php';
            break;
        case 3:
            url = 'inf_epingresos.php';
            break;
        case 4:
            url = 'inf_relingresos.php';
            break;
        case 5:
            url = 'inf_epgastos.php';
            break;
        case 6:
            url = 'inf_relcompromisos.php';
            break;
        case 7:
            url = 'inf_modingresos.php';
            break;
        case 8:
            url = 'inf_modgastos.php';
            break;
        case 9:
            url = 'inf_ctasxpagar.php';
            break;
        case 10:
            url = 'inf_relpagos.php';
            break;
        case 11:
            url = 'inf_relpagossinpto.php';
            break;
        default:
            console.log('ID de informe no válido');
            return;
    }
    if ($('#periodo').val() == '0') {
        mjeError('Debe seleccionar un periodo');
        return false;
    }
    //redireccion por post
    $('<form>', {
        method: 'POST',
        action: url
    }).append($('<input>', {
        type: 'hidden',
        name: 'periodo',
        value: $('#periodo').val()
    })).appendTo('body').submit();
}