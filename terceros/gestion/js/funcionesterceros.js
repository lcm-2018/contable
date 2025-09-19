function FormResponsabilidad(id) {
    $.post("../gestion/datos/registrar/form_responsabilidad.php", { id: id }, function (he) {
        $('#divTamModalForms').removeClass('modal-xl');
        $('#divTamModalForms').removeClass('modal-sm');
        $('#divTamModalForms').addClass('modal-lg');
        $('#divModalForms').modal('show');
        $("#divForms").html(he);
        $('#slcRespEcon').focus();
    });
}

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
    };
    var bordeError = function (p) {
        $('#' + p).css("border", "2px solid #F5B7B1");
        $('#' + p).css('box-shadow', '0 0 4px 3px pink');
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
    //Cambiar Municipios por departamento
    $('#divForms').on('change', '#slcDptoEmp', function () {
        let dpto = $(this).val();
        $.ajax({
            type: 'POST',
            url: window.urlin + '/nomina/empleados/registrar/slcmunicipio.php',
            data: { dpto: dpto },
            success: function (r) {
                $('#slcMunicipioEmp').html(r);
            }
        });
        return false;
    });
    $(document).ready(function () {
        let id_t = $('#id_tercero').val();
        //dataTable Terceros
        tbListTerceros = $('#tableTerceros').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    window.location = '../gestion/registrar/formaddtercero.php';
                }
            }],
            language: setIdioma,
            serverSide: true,
            processing: true,
            searching: false,
            ajax: {
                url: 'datos/listar/datos_terceros.php',
                type: 'POST',
                dataType: 'json',
                data: function (d) {
                    //------ datos de filtros
                    d.ccnit = $('#txt_ccnit_filtro').val();
                    d.tercero = $('#txt_tercero_filtro').val();
                    //--------------------------------

                    d.anulados = $('#verAnulados').prop('checked') ? '1' : '0';
                    return d
                }
            },
            columns: [
                { 'data': 'cc_nit' },
                { 'data': 'nombre_tercero' },
                //{ 'data': 'razon_social' },
                { 'data': 'tipo' },
                { 'data': 'municipio' },
                { 'data': 'direccion' },
                { 'data': 'telefono' },
                { 'data': 'correo' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [1, 2, 4] },
                { orderable: false, targets: 8 }
            ],
            order: [
                [0, "desc"]
            ],
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('#tableTerceros').wrap('<div class="overflow" />');
        $('#tableTerceros_filter input').unbind(); // Desvinculamos el evento por defecto
        $('#tableTerceros_filter input').bind('keypress', function (e) {
            if (e.keyCode == 13) { // Si se presiona Enter (código 13)
                tbListTerceros.search(this.value).draw(); // Realiza la búsqueda y actualiza la tabla
            }
        });
        //dataTable Resposabilidad Economica
        let idt = $('#id_tercero').val();
        $('#tableRespEcon').DataTable({
            dom: setdom,
            buttons: [{
                attr: {
                    id: 'btnRegistrarRespEcon', // Asignas un id
                },
                action: function (e, dt, node, config) {
                    //Registar Responsabilidad Economica desde Detalles
                    $.post("datos/registrar/formadd_resp_economica.php", { idt: idt }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#slcRespEcon').focus();
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_resp_econ.php',
                type: 'POST',
                data: { id_t: id_t },
                dataType: 'json',
            },
            "columns": [

                { 'data': 'codigo' },
                { 'data': 'descripcion' },
                { 'data': 'estado' },
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [1] },
            ],
            order: [
                [0, "desc"]
            ],
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('#tableRespEcon').wrap('<div class="overflow" />');
        $('#tableResponsabilidades').DataTable({
            dom: setdom,
            buttons: [{
                attr: {
                    id: 'btnRegistrarRespEcon', // Asignas un id
                },
                action: function (e, dt, node, config) {
                    //Registar Responsabilidad Economica desde Detalles
                    FormResponsabilidad(0);
                }
            }],
            language: setIdioma,
            "ajax": {
                url: '../gestion/datos/listar/lista_responsabilidades.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'codigo' },
                { 'data': 'descripcion' },
                { 'data': 'botones' },
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [1] },
            ],
            order: [
                [0, "desc"]
            ],
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('#tableResponsabilidades').wrap('<div class="overflow" />');
        //dataTable Actividad Economica
        $('#tableActvEcon').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_actv_economica.php", { idt: idt }, function (he) {
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#slcActvEcon').focus();
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_actv_econ.php',
                type: 'POST',
                data: { id_t: id_t },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'codigo' },
                { 'data': 'descripcion' },
                { 'data': 'fec_inicio' },
                { 'data': 'estado' },
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [1] },
            ],
            order: [
                [0, "desc"]
            ],
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],

        });
        $('#tableActvEcon').wrap('<div class="overflow" />');
        $('#tablePerfilTercero').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("../gestion/datos/registrar/form_perfil_tercero.php", { id: 0 }, function (he) {
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            ajax: {
                url: '../gestion/datos/listar/datos_perfil_tercero.php',
                type: 'POST',
                data: { id_t: id_t },
                dataType: 'json',
            },
            columns: [
                { 'data': 'id' },
                { 'data': 'descripcion' },
                { 'data': 'acciones' },
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [1] },
            ],
            order: [
                [0, "desc"]
            ],
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],

        });
        $('#tablePerfilTercero').wrap('<div class="overflow" />');
        //dataTable Documentos tercero
        $('#tableDocumento').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_docs_tercero.php", { idt: idt }, function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-lg');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#slcTipoDocs').focus();
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/datos_docs.php',
                type: 'POST',
                data: { id_t: id_t },
                dataType: 'json',
            },
            "columns": [
                { 'data': 'tipo' },
                { 'data': 'fec_inicio' },
                { 'data': 'fec_vigencia' },
                { 'data': 'vigente' },
                { 'data': 'doc' },
            ],
            "order": [
                [0, "asc"]
            ],

        });
        $('#tableDocumento').wrap('<div class="overflow" />');
    });
    //Nuevo tercero
    $('#btnNewTercero').on('click', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#slcTipoTercero').val() === '0') {
            $('#slcTipoTercero').addClass('is-invalid');
            $('#slcTipoTercero').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#datFecInicio').val() === '') {
            $('#datFecInicio').addClass('is-invalid');
            $('#datFecInicio').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#slcGenero').val() === '0') {
            $('#slcGenero').addClass('is-invalid');
            $('#slcGenero').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#slcTipoDocEmp').val() === '0') {
            $('#slcTipoDocEmp').addClass('is-invalid');
            $('#slcTipoDocEmp').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#txtCCempleado').val() === '' || parseInt($('#txtCCempleado').val()) < 1) {
            $('#txtCCempleado').addClass('is-invalid');
            $('#txtCCempleado').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#slcPaisEmp').val() === '0') {
            $('#slcPaisEmp').addClass('is-invalid');
            $('#slcPaisEmp').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#slcDptoEmp').val() === '0') {
            $('#slcDptoEmp').addClass('is-invalid');
            $('#slcDptoEmp').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#slcMunicipioEmp').val() === '0') {
            $('#slcMunicipioEmp').addClass('is-invalid');
            $('#slcMunicipioEmp').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#mailEmp').val() === '') {
            $('#mailEmp').addClass('is-invalid');
            $('#mailEmp').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#txtTelEmp').val() === '') {
            $('#txtTelEmp').addClass('is-invalid');
            $('#txtTelEmp').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else if ($('#rdo_planilla_si').is(':checked') && $('#slcRiesgoLab').val() === '0') {
            $('#slcRiesgoLab').addClass('is-invalid');
            $('#slcRiesgoLab').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else {
            let datos = $('#formNuevoTercero').serialize();
            let pasT = hex_sha512($('#txtCCempleado').val());
            datos = datos + '&passT=' + pasT;
            $.ajax({
                type: 'POST',
                url: 'newtercero.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        mje('Nuevo Tercero', 'Tercero registrado correctamente');
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    } else {
                        mjeError('Error', r);
                    }
                }
            });
        }
        ActivaBoton(btn);
        return false;
    });
    var cambiarEstado = function (e, idt, u, btn) {
        $.ajax({
            type: 'POST',
            url: u,
            data: { e: e, idt: idt },
            success: function (r) {
                switch (r) {
                    case '0':
                        $('#' + btn + idt).attr('title', 'Inactivo');
                        $('#' + btn + idt + ' span').removeClass('fa-toggle-on');
                        $('#' + btn + idt + ' span').addClass('fa-toggle-off');
                        $('#' + btn + idt + ' span').removeClass('activo');
                        $('#' + btn + idt + ' span').addClass('inactivo');
                        break;
                    case '1':
                        $('#' + btn + idt).attr('title', 'Activo');
                        $('#' + btn + idt + ' span').removeClass('fa-toggle-off');
                        $('#' + btn + idt + ' span').addClass('fa-toggle-on');
                        $('#' + btn + idt + ' span').removeClass('inactivo');
                        $('#' + btn + idt + ' span').addClass('activo');
                        break;
                    default:
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                        break;
                }
            }
        });
    };
    //detalles tercero
    $('#modificarTerceros').on('click', '.detalles', function () {
        let id = $(this).attr('value');
        $('<form action="detalles_tercero.php" method="post"><input type="hidden" name="id_ter" value="' + id + '" /></form>').appendTo('body').submit();
        return false;
    });
    //cambiar estado tercero
    $('#modificarTerceros').on('click', '.estado', function () {
        let e = !($(this).hasClass('activo')) ? '1' : '0';
        let idt = $(this).attr('value');
        let url = 'actualizar/upestadotercero.php';
        let boton = 'btnestado_';
        cambiarEstado(e, idt, url, boton);
        return false;
    });
    //Actualizar terceros
    $('#modificarTerceros').on('click', '.editar', function () {
        let idt = $(this).attr('value');
        $.post("datos/actualizar/uptercero.php", { idt: idt }, function (he) {
            $('#divTamModalForms').addClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
            $('#slcTipoTercero').focus();
        });
    });
    //Actualizar datos tercero
    $('#divForms').on('click', '#btnUpTercero', function () {
        var msg = 'Diligenciar, campo obligatorio';
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#datFecInicio').val() === '') {
            $('#datFecInicio').addClass('is-invalid');
            $('#datFecInicio').focus();
            mjeError('Error', msg);
        } else if ($('#datFecNacimiento').val() === '') {
            $('#datFecNacimiento').addClass('is-invalid');
            $('#datFecNacimiento').focus();
            mjeError('Error', msg);
        } else if ($('#txtCCempleado').val() === '' || parseInt($('#txtCCempleado').val()) < 1) {
            $('#txtCCempleado').addClass('is-invalid');
            $('#txtCCempleado').focus();
            mjeError('Error', msg);
        } else if ($('#slcMunicipioEmp').val() === '0') {
            $('#slcMunicipioEmp').addClass('is-invalid');
            $('#slcMunicipioEmp').focus();
            mjeError('Error', msg);
        } else if ($('#mailEmp').val() === '') {
            $('#mailEmp').addClass('is-invalid');
            $('#mailEmp').focus();
            mjeError('Error', msg);
        } else if ($('#txtTelEmp').val() === '') {
            $('#txtTelEmp').addClass('is-invalid');
            $('#txtTelEmp').focus();
            mjeError('Error', msg);
        } else if ($('#rdo_planilla_si').is(':checked') && $('#slcRiesgoLab').val() === '0') {
            $('#slcRiesgoLab').addClass('is-invalid');
            $('#slcRiesgoLab').focus();
            mjeError('Error', 'Diligenciar, campo obligatorio');
        } else {
            let datos = $('#formActualizaTercero').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_datos_tercero.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalForms').modal('hide');
                        $('#tableTerceros').DataTable().ajax.reload();
                        mje('Actualizado', 'Datos actualizados correctamente');
                    } else {
                        mjeError('Error', r);
                    }
                }
            });
        }
        ActivaBoton(btn);
        return false;
    });
    //Borrar Tercero confirmar
    $('#modificarTerceros').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Tercero';
        confdel(id, tip);
    });
    //Eliminar tercero confirmar
    $("#divBtnsModalDel").on('click', '#btnConfirDelTercero', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/deltercero.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableTerceros';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Tercero eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Registar Responsabilidad Economica
    $('#modificarTerceros').on('click', '.responsabilidad', function () {
        let idt = $(this).attr('value');
        $.post("datos/registrar/formadd_resp_economica.php", { idt: idt }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl')
            $('#divTamModalForms').addClass('modal-sm')
            $('#divTamModalForms').addClass('modal-lg')
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
            $('#slcRespEcon').focus();
        });
    });
    //Agregar Responsabilidad Economica
    $('#divForms').on('click', '#btnAddRespEcon', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#slcRespEcon').val() === '0') {
            $('#buscarRespEcono').addClass('is-invalid');
            $('#buscarRespEcono').focus();
            mjeError('Error', '¡Debe seleccionar una Resposabilidad Económica!');
        } else {
            datos = $('#formAddRespEcon').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_resp_econ.php',
                data: datos,
                success: function (r) {
                    if (r == 'ok') {
                        if ($('#tableRespEcon').length) {
                            $('#tableRespEcon').DataTable().ajax.reload(null, false);
                        }
                        $('#divModalForms').modal('hide');
                        mje('Responsabilidad Económica agregada correctamente');
                    } else {
                        mjeError('Error', r);
                    }
                }
            });
        }
        ActivaBoton(btn);
        return false;
    });
    //Registar Actividad Economica
    $('#modificarTerceros').on('click', '.actividad', function () {
        let idt = $(this).attr('value');
        $.post("datos/registrar/formadd_actv_economica.php", { idt: idt }, function (he) {
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
            $('#slcActEcon').focus();
        });
    });
    $('#divForms').on('click', '#btnGuardaDocTercero', function () {
        $('.is-invalid').removeClass('is-invalid');
        if ($('#slcTipoDocs').val() === '0') {
            $('#slcTipoDocs').addClass('is-invalid');
            $('#slcTipoDocs').focus();
            mjeError('¡Debe seleccionar un tipo de documento!');
        } else if ($('#datFecInicio').val() === '') {
            $('#datFecInicio').addClass('is-invalid');
            $('#datFecInicio').focus();
            mjeError('¡Debe ingresar Fecha Inicio!');
        } else if ($('#datFecVigencia').val() === '') {
            $('#datFecVigencia').addClass('is-invalid');
            $('#datFecVigencia').focus();
            mjeError('¡Debe ingresar Fecha de Vigencia!');
        } else if ($('#fileDoc').val() === '') {
            $('#fileDoc').addClass('is-invalid');
            $('#fileDoc').focus();
            mjeError('¡Debe elegir un archivo!');
        } else {
            let isVisible = $('#rowCertfBanc').is(":visible")
            let isVisible2 = $('#rowCcontrato').is(":visible");
            let datos = new FormData();
            if (isVisible) {
                if ($('#slcBanco').val() == '0') {
                    $('#slcBanco').addClass('is-invalid');
                    $('#slcBanco').focus();
                    mjeError('¡Debe seleccionar un Banco!');
                    return false;
                } else if ($('#slcTipoCta').val() == '0') {
                    $('#slcTipoCta').addClass('is-invalid');
                    $('#slcTipoCta').focus();
                    mjeError('¡Debe seleccionar un tipo de cuenta!');
                    return false;
                } else if ($('#numCuenta').val() == '') {
                    $('#numCuenta').addClass('is-invalid');
                    $('#numCuenta').focus();
                    mjeError('¡Debe ingresar un número de cuenta!');
                    return false;
                }
                datos.append('slcBanco', $('#slcBanco').prop('value'));
                datos.append('slcTipoCta', $('#slcTipoCta').prop('value'));
                datos.append('numCuenta', $('#numCuenta').prop('value'));
            }
            if (isVisible2) {
                datos.append('slcPerfil', $('#slcPerfil').prop('value'));
                datos.append('txtCargo', $('#txtCargo').prop('value'));
            }
            let archivo = $('#fileDoc').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext !== '.pdf') {
                mjeError('¡Solo se permite documentos .pdf!');
                return false;
            } else if ($('#fileDoc')[0].files[0].size > 5242880) {
                mjeError('¡Documento debe tener un tamaño menor a 5Mb!');
                return false;
            }
            datos.append('idTercero', $('#idTercero').prop('value'));
            datos.append('slcTipoDocs', $('#slcTipoDocs').prop('value'));
            datos.append('datFecInicio', $('#datFecInicio').prop('value'));
            datos.append('datFecVigencia', $('#datFecVigencia').prop('value'));
            datos.append('fileDoc', $('#fileDoc')[0].files[0]);
            $.ajax({
                type: 'POST',
                url: 'registrar/new_doc_tercero.php',
                contentType: false,
                data: datos,
                processData: false,
                cache: false,
                success: function (r) {
                    if (r === 'ok') {
                        $('#tableDocumento').DataTable().ajax.reload(null, false);
                        $('#divModalForms').modal('hide');
                        mje('Documento cargado Correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
        return false;
    });

    $('#divModalForms').on('change', '#slcTipoDocs', function () {
        let tipo = $(this).val();
        if (tipo == '23') {
            $('#rowCertfBanc').css('display', 'block');
        } else {
            $('#rowCertfBanc').css('display', 'none');
        }

        if (tipo == '25') {
            $('#rowCcontrato').css('display', 'block');
        } else {
            $('#rowCcontrato').css('display', 'none');
        }
    });


    //Agregar Actividad Economica
    $('#divForms').on('click', '#btnAddActvEcon', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        if ($('#slcActvEcon').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe seleccionar una Actividad Económica!');
        } else if ($('#datFecInicio').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Fecha Inicio no puede ser vacia!');
        } else {
            datos = $('#formAddActvEcon').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_actv_econ.php',
                data: datos,
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableActvEcon';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Actvidad Económica Agregada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        ActivaBoton(btn);
        return false;
    });

    //-------------- historial terceros
    $('#modificarTerceros').on('click', '.historial', function () {
        let idt = $(this).attr('value');
        $.post("../php/historialtercero/frm_historialtercero.php", { idt: idt }, function (he) {
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
            //$('#slcActEcon').focus();
        });
    });

    //------------------------------
    //Buscar registros de Ingresos
    $('#btn_buscar_filtro').on("click", function () {
        $('.is-invalid').removeClass('is-invalid');
        reloadtable('tableTerceros');
    });

    $('.filtro').keypress(function (e) {
        if (e.keyCode == 13) {
            reloadtable('tableTerceros');
        }
    });
    //-----------------dashboard
    $('#btn_iniciar_dashboard').on("click", async function () {
        try {
            const response = await fetch('../python/dash_controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Indica que envías JSON
                },
                body: JSON.stringify({ action: 'start' }) // Campo 'action' incluido
            });
            const data = await response.json();
            console.log(data.status);
            mje("iniciado");
        } catch (error) {
            console.error("Error:", error);
        }
    });

    $('#btn_detener_dashboard').on("click", async function () {
        try {
            const response = await fetch('../python/dash_controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json', // Indica que envías JSON
                },
                body: JSON.stringify({ action: 'stop' }) // Campo 'action' incluido
            });
            const data = await response.json();
            console.log(data.status);
            mje("detenido");
        } catch (error) {
            console.error("Error:", error);
        }
    });

    $('#btn_dashboard').on("click", async function () {
        try {
            const response = await $.ajax({
                url: window.urlin + "/terceros/python/listar_terceros.php",
                type: 'POST',
                dataType: 'json'
            });

            // Preparamos los datos para el gráfico
            const datosGrafico = response.map(item => ({
                municipio: item.municipio,
                cantidad: parseInt(item.numero)
            }));

            // Codificamos como parámetro URL
            const params = new URLSearchParams();
            params.append('datos', JSON.stringify(datosGrafico));

            window.open(`http://0.0.0.0:8050?${params.toString()}`, "_blank");
        } catch (error) {
            console.error("Error:", error);
            alert("Error al cargar datos para el dashboard");
        }

        /*
        //--------esto pa traer el numero de terceros por ciudad
        $.ajax({
            url: window.urlin + "/terceros/python/listar_terceros.php",
            type: 'POST',
            dataType: 'json',
            data: {
                //------ datos de filtros
                //ccnit: $('#txt_ccnit_filtro').val(),
                //tercero: $('#txt_tercero_filtro').val()
                //--------------------------------
            },
            success: function (data) {
                //$('#txt_ccnit_filtro').val(data.id);
                response(data);                
            },
        });

        var parametro = "Todos";
        try {
            //setTimeout(() => {
            window.open("http://localhost:8050?producto=" + parametro, "_blank");
            // }, 2000);
        } catch (error) {
            console.error("Error:", error);
        }
        
        /*
        source: function (request, response) {
            $.ajax({
                url: window.urlin + "/presupuesto/php/libros_aux_pto/listar_rubros.php",
                type: "POST",
                dataType: "json",
                data: {
                    term: request.term,
                },
                success: function (data) {
                    response(data);
                },
            });
        },
        select: function (event, ui) {
            $("#txt_tipo_doc").val(ui.item.label);
            $("#id_cargue").val(ui.item.id);
            return false;
        },
        focus: function (event, ui) {
            $("#txt_tipo_doc").val(ui.item.label);
            return false;
        },*/

        //----------------------------------------------------
    });
    //-----------------------------------------------------

    //descargar documento PDF
    $('#modificarDocs').on('click', '.descargar', function () {
        let id_doc = $(this).attr('text');
        window.location.href = 'datos/descargas/descarga_docs.php?id=' + id_doc;
        return false;
    });
    $('#txtBuscarTercero').on('input', function () {
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
                $('#txtIdTercero').val(ui.item.id);
                $('#slcTipoTerce').focus();
            }
        });
    });
    $('#divModalForms').on('input', '#buscarRespEcono', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + '/terceros/gestion/datos/listar/buscar_resposabilidad.php',
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
                $('#slcRespEcon').val(ui.item.id);
            }
        });
    });
    $('#divModalForms').on('input', '#buscarActvEcono', function () {
        $(this).autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: window.urlin + '/terceros/gestion/datos/listar/buscar_actividad.php',
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
                $('#slcActvEcon').val(ui.item.id);
                $('#datFecInicio').focus();
            }
        });
    });
    $('#btnNewTipoTercero').on('click', function () {
        if ($('#txtBuscarTercero').val() == '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un Tercero');
        } else if ($('#txtIdTercero').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un Tercero válido');
        } else if ($('#slcTipoTerce').val() == '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe seleccionar un Tipo de Tercero');
        } else {
            let datos = $('#formAddTipoTercero').serialize();
            $.ajax({
                type: 'POST',
                url: 'new_tipo_tercero.php',
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        $('#formAddTipoTercero')[0].reset();
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Tipo de Tercero Agregado Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#btnReporteTerceros').on('click', function () {
        $('<form action="informes/reporte_terceros.php" method="post"></form>').appendTo('body').submit();
    });
    $('#btnActualizaRepositorio').on('click', function () {
        $('#btnActualizaRepositorio').attr('disabled', true);
        //buscar span para poner una animacion de carga
        $('#btnActualizaRepositorio span').addClass('spinner-border spinner-border-sm');
        $.ajax({
            type: 'POST',
            url: 'registrar/newmasivo.php',
            success: function (r) {
                $('#tableTerceros').DataTable().ajax.reload();
                $('#btnActualizaRepositorio').attr('disabled', false);
                $('#btnActualizaRepositorio span').removeClass('spinner-border spinner-border-sm');
                mje(r);
            }
        });
    });
})(jQuery);

function GuardaResponsabilidad() {
    $('.is-invalid').removeClass('is-invalid');
    if ($('#codigoRespEcono').val() == '') {
        $('#codigoRespEcono').addClass('is-invalid');
        $('#codigoRespEcono').focus();
        mjeError('Error', 'Ingresar código de la responsabilidad económica');
    } else if ($('#nombreRespEcono').val() == '') {
        $('#nombreRespEcono').addClass('is-invalid');
        $('#nombreRespEcono').focus();
        mjeError('Error', 'Ingresar descripción de la responsabilidad económica');
    } else {
        var datos = $('#formGestRespEcon').serialize();
        $.ajax({
            type: 'POST',
            url: '../gestion/registrar/guarda_responsabilidad.php',
            data: datos,
            success: function (r) {
                if (r == 'ok') {
                    $('#divModalForms').modal('hide');
                    $('#tableResponsabilidades').DataTable().ajax.reload(null, false);
                    mje('Guardado', 'Responsabilidad Económica guardada correctamente');
                } else {
                    mjeError('Error', r);
                }
            }
        });
    }

}

function BorrarResponsabilidad(id) {
    Swal.fire({
        title: "¿Confirma eliminar la Responsabilidad Económica?",
        text: "¡No podrás revertir esto!",
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
                url: '../gestion/eliminar/delresponsabilidad.php',
                data: { id: id },
                success: function (r) {
                    if (r == 'ok') {
                        $('#tableResponsabilidades').DataTable().ajax.reload(null, false);
                        mje('Eliminado', 'Responsabilidad Económica eliminada correctamente');
                    } else {
                        mjeError('Error', r);
                    }
                }
            });
        }
    });
}

function BorrarDocumentoTercero(id) {
    Swal.fire({
        title: "¿Confirma eliminar el documento?",
        text: "¡No podrás revertir esto!",
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
                url: '../gestion/eliminar/deldocumento.php',
                data: { id: id },
                success: function (r) {
                    if (r == 'ok') {
                        $('#tableDocumento').DataTable().ajax.reload(null, false);
                        mje('Eliminado', 'Documento eliminado correctamente');
                    } else {
                        mjeError('Error', r);
                    }
                }
            });
        }
    });
}

function GuardaPerfilTercero() {
    $('.is-invalid').removeClass('is-invalid');
    if ($('#txtPerfilTercero').val() === '') {
        $('#txtPerfilTercero').addClass('is-invalid');
        $('#txtPerfilTercero').focus();
        mjeError('Error', '¡Debe ingresar un Perfil!');
    } else {
        var datos = $('#formPerfilTercero').serialize();
        $.ajax({
            type: 'POST',
            url: '../gestion/registrar/guarda_perfil_tercero.php',
            data: datos,
            success: function (r) {
                if (r == 'ok') {
                    $('#divModalForms').modal('hide');
                    $('#tablePerfilTercero').DataTable().ajax.reload(null, false);
                    mje('Guardado', 'Perfil guardado correctamente');
                } else {
                    mjeError('Error', r);
                }
            }
        });
    }
}

function EditarPerfilTercero(id) {
    $.post("../gestion/datos/registrar/form_perfil_tercero.php", { id: id }, function (he) {
        $('#divTamModalForms').removeClass('modal-lg');
        $('#divTamModalForms').removeClass('modal-sm');
        $('#divTamModalForms').removeClass('modal-xl');
        $('#divModalForms').modal('show');
        $("#divForms").html(he);
    });
}

function BorrarPerfilTercero(id) {
    Swal.fire({
        title: "¿Confirma eliminar el perfil?",
        text: "¡No podrás revertir esto!",
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
                url: '../gestion/eliminar/delperfil.php',
                data: { id: id },
                success: function (r) {
                    if (r == 'ok') {
                        $('#tablePerfilTercero').DataTable().ajax.reload(null, false);
                        mje('Eliminado', 'Perfil eliminado correctamente');
                    } else {
                        mjeError('Error', r);
                    }
                }
            });
        }
    });
}