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
        default:
            console.error('Tipo de informe no válido');
            return;
    }
    //ajax por post 
    $.ajax({
        type: 'POST',
        url: url,
        data: { tipo: tipo },
        success: function (response) {
            $('#form_financiero').html(response);
        },
        error: function (xhr, status, error) {
            console.error('Error al cargar el formulario:', error);
        }
    });
}

const InformeFinanciero = (boton) => {
    var tipo = $(boton).val();
    var periodo = $('#periodo').val();
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
    }
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