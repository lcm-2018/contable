$('#tableParamLiq').DataTable({
    dom: setdom,
    buttons: [{
        text: '<span class="fa fa-plus fa-lg"></span>',
        titleAttr: 'Agregar concepto',
        action: function (e, dt, node, config) {
            HtmlPost('form_conceptos.php', { id: 0 }, modalForms, '');
        }
    }],

    language: setIdioma,
    ajax: {
        url: 'list_conceptos.php',
        type: 'POST',
        dataType: 'json'
    },
    columns: [
        { 'data': 'id' },
        { 'data': 'concepto' },
        { 'data': 'valor' },
        { 'data': 'botones' },
    ],
    order: [
        [1, "asc"]
    ],
    lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, 'TODO'],
    ],
    "pageLength": 50
});
$('#tableParamLiq').wrap('<div class="overflow" />');

document.addEventListener('click', function (event) {
    if (event.target && event.target.id === 'btnGuardaConcxVig') {
        LimpiaInvalid();
        if (ValueInput('concepto') == '0') {
            MuestraError('concepto', 'No se ha seleccionado un concepto');
        } else if (Number(ValueInput('valor')) < 0) {
            MuestraError('valor', 'Valor debe ser mayor a cero o igual a cero (0)');
        } else {
            var data = Serializa('formConcepXvig');
            var oper = ValueInput('id_concepto') == '0' ? 'add' : 'edit';
            data.append('oper', oper);
            SendPost('guarda_concepto.php', data).then(he => {
                if (he.status == 'ok') {
                    $('#tableParamLiq').DataTable().ajax.reload(null, false);
                    mje('Proceso realizado correctamente');
                    modalForms.hide();
                } else {
                    mjeError(he.msg);
                }
            });

        }
    }
});

document.getElementById('tableParamLiq').addEventListener('click', function (event) {
    var button = event.target.closest('.actualizar, .eliminar');

    if (button) {
        var id = button.getAttribute('data-id');
        if (button.classList.contains('actualizar')) {
            HtmlPost('form_conceptos.php', { id: id }, modalForms, '');
        } else if (button.classList.contains('eliminar')) {
            Swal.fire(DelParams).then((result) => {
                if (result.isConfirmed) {
                    let dat = new FormData();
                    dat.append('oper', 'del');
                    dat.append('id_concepto', id);
                    SendPost('guarda_concepto.php', dat).then(he => {
                        if (he.status == 'ok') {
                            $('#tableParamLiq').DataTable().ajax.reload(null, false);
                            mje('Registro eliminado correctamente');
                        } else {
                            mjeError(he.msg);
                        }
                    });
                }
            });
        }
    }
});

