$('#tableParamLiq').DataTable($.extend(true, {}, dataTableDefaults, {
    buttons: [{
        text: btnplus,
        titleAttr: 'Agregar concepto',
        action: function (e, dt, node, config) {
            HtmlPost('form_conceptos.php', { id: 0 }, modalForms, '');
        }
    }],
    ajax: {
        url: 'list_conceptos.php',
        type: 'POST',
        dataType: 'json',
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
    pageLength: 10,
}));
$('#tableCargosNomina').DataTable($.extend(true, {}, dataTableDefaults, {
    buttons: [{
        text: btnplus,
        titleAttr: 'Agregar cargo',
        action: function (e, dt, node, config) {
            HtmlPost('form_cargos.php', { id: 0 }, modalForms, 'lg');
        }
    }],
    ajax: {
        url: 'list_cargos.php',
        type: 'POST',
        dataType: 'json',
    },
    columns: [
        { 'data': 'id_cargo' },
        { 'data': 'codigo' },
        { 'data': 'cargo' },
        { 'data': 'grado' },
        { 'data': 'perfil_siho' },
        { 'data': 'nombramiento' },
        { 'data': 'acciones' },
    ],
    order: [
        [2, "asc"]
    ],
    pageLength: 10,
}));
$('#tableTerceroNomina').DataTable($.extend(true, {}, dataTableDefaults, {
    buttons: [{
        text: btnplus,
        titleAttr: 'Agregar tercero a nomina',
        action: function (e, dt, node, config) {
            HtmlPost('form_terceros.php', { id: 0 }, modalForms, 'lg');
        }
    }],
    ajax: {
        url: 'list_terceros.php',
        type: 'POST',
        dataType: 'json',
    },
    columns: [
        { 'data': 'codigo' },
        { 'data': 'descripcion' },
        { 'data': 'nombre' },
        { 'data': 'nit' },
        //{ 'data': 'categoria' },
    ],
    order: [
        [1, "asc"]
    ],
    pageLength: 10,
}));

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
                    reloadtable('tableParamLiq');
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
                            reloadtable('tableParamLiq');
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

document.addEventListener('click', function (event) {
    if (event.target && event.target.id === 'btnGuardaCargo') {
        LimpiaInvalid();
        if (ValueInput('slcCodigo') == '0') {
            MuestraError('slcCodigo', 'Selecionar un codigo de cargo');
        } else if (ValueInput('txtNomCargo') == '') {
            MuestraError('txtNomCargo', 'Digite el nombre del cargo');
        } else if (Number(ValueInput('numGrado')) <= 0) {
            MuestraError('numGrado', 'Digite el grado del cargo');
        } else if (ValueInput('slcNombramiento') == '0') {
            MuestraError('slcNombramiento', 'Seleccione un nombramiento');
        } else if (ValueInput('txtPerfilSiho') == '') {
            MuestraError('txtPerfilSiho', 'Digite el perfil siho');
        } else {
            var data = Serializa('formGestCargoNom');
            var oper = ValueInput('id_cargo') == '0' ? 'add' : 'edit';
            data.append('oper', oper);
            SendPost('guarda_cargo.php', data).then(he => {
                if (he.status == 'ok') {
                    reloadtable('tableCargosNomina');
                    mje('Proceso realizado correctamente');
                    modalForms.hide();
                } else {
                    mjeError(he.msg);
                }
            });

        }
    }
});

document.getElementById('tableCargosNomina').addEventListener('click', function (event) {
    var button = event.target.closest('.actualizar, .eliminar');
    if (button) {
        var id = button.getAttribute('data-id');
        if (button.classList.contains('actualizar')) {
            HtmlPost('form_cargos.php', { id: id }, modalForms, 'lg');
        } else if (button.classList.contains('eliminar')) {
            Swal.fire(DelParams).then((result) => {
                if (result.isConfirmed) {
                    let dat = new FormData();
                    dat.append('oper', 'del');
                    dat.append('id_cargo', id);
                    SendPost('guarda_cargo.php', dat).then(he => {
                        if (he.status == 'ok') {
                            reloadtable('tableCargosNomina');
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