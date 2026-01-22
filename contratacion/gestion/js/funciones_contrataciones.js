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

    $(document).ready(function () {
        let id_t = $('#id_tercero').val();
        //dataTable Modalidad
        $('#tableModalidad').DataTable({
            "bFilter": false,
            "bInfo": false,
            "bLengthChange": false,
            dom: setdom,
            buttons: [{
                //Registar modalidad de contratación
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_modalidad.php", function (he) {
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
                url: 'datos/listar/datos_modalidad.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'modalidad' },
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
        $('#tableModalidad').wrap('<div class="overflow" />');
        //dataTable Tipo Contrato
        $('#tableTipoContrato').DataTable({
            "bInfo": false,
            "bLengthChange": false,
            dom: setdom,
            buttons: [{
                //Registar tipo de contrato
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_tipo_contrato.php", function (he) {
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
                url: 'datos/listar/datos_tipo_contrato.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'tipo_compra' },
                { 'data': 'tipo_contrato' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"],
                [1, "asc"],
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableTipoContrato').wrap('<div class="overflow" />');
        //dataTable Tipo de bien o servicio
        $('#tableTipoBnSv').DataTable({
            dom: setdom,
            buttons: [{
                //Registar tipo bien o servicio
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_tipo_bn_sv.php", function (he) {
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
                url: 'datos/listar/datos_tipo_bien_servicio.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'tipo_compra' },
                { 'data': 'tipo_contrato' },
                { 'data': 'tipo_bs' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"],
                [1, "asc"],
                [2, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableTipoBnSv').wrap('<div class="overflow" />');
        //dataTable Tipo de bien o servicio
        $('#tableBnSv').DataTable({
            dom: setdom,
            buttons: [{
                //Registar bien o servicio
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadd_bn_sv.php", function (he) {
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
                url: 'datos/listar/datos_bien_servicio.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'tipo_compra' },
                { 'data': 'tipo_contrato' },
                { 'data': 'tipo_bs' },
                { 'data': 'bn_servicio' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"],
                [1, "asc"],
                [2, "asc"],
                [3, "asc"]
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#tableBnSv').wrap('<div class="overflow" />');
        $('#tableFormCtt').DataTable({
            dom: setdom,
            buttons: [{
                //Registar modalidad de contratación
                action: function (e, dt, node, config) {
                    $.post("datos/cargar/formatos_ctt.php", function (he) {
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
                url: 'datos/listar/datos_formatos_ctt.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id' },
                { 'data': 'formato' },
                { 'data': 'tp_ctt' },
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
        $('#tableFormCtt').wrap('<div class="overflow" />');
        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
    });
    //Agregar modalidad contratacion
    $('#divForms').on('click', '#btnAddModalidad', function () {
        if ($('#txtModalidad').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Modalidad no puede ser Vacía!');
        } else {
            datos = $('#txtModalidad').val();
            $.ajax({
                type: 'POST',
                url: 'registrar/new_modalidad.php',
                data: { datos: datos },
                success: function (r) {
                    if (r === '1') {
                        let id = 'tableModalidad';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Modalidad de contratación Agregada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Borrar modalidad confirmar
    $('#modificarModalidades').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'Modalidad';
        confdel(id, tip);
    });
    //Eliminar modalidad
    $("#divBtnsModalDel").on('click', '#btnConfirDelModalidad', function () {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_modalidad.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    let id = 'tableModalidad';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Modalidad de contratación eliminada correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //Agregar tipo de contrato
    $('#divForms').on('click', '#btnAddTipoContrato', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
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
        ActivaBoton(btn);
        return false;
    });
    //Actualizar tipo de contrato -> formulario
    $('#modificarTipoContratos').on('click', '.editar', function () {
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
    $('#divForms').on('click', '#btnUpTipoContrato', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
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
        ActivaBoton(btn);
        return false;
    });
    //Borrar tipo de contrato confirmar
    $('#modificarTipoContratos').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'TipoContrato';
        confdel(id, tip);
    });
    //Eliminar tipo de contrato
    $("#divBtnsModalDel").on('click', '#btnConfirDelTipoContrato', function () {
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
    $('#divForms').on('click', '#btnAddTipoBnSv', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        if ($('#slcTipoContrato').val() === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe selecionar tipo de contrato!');
        } else if ($('#txtTipoBnSv').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Tipo de contrato no puede ser Vacío!');
        } else if ($('#txtObjPre').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Objeto predefinido no puede ser Vacío!');
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
        ActivaBoton(btn);
        return false;
    });
    //Actualizar tipo de bien o servicio -> formulario
    $('#modificarTipoBnSvs').on('click', '.editar', function () {
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
    $('#divForms').on('click', '#btnUpTipoBnSv', function () {
        let id;
        var btn = $(this).get(0);
        InactivaBoton(btn);
        if ($('#txtTipoContrato').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Tipo de bien o servicio no puede ser Vacío!');
        } else if ($('#txtObjPre').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Objeto predefinido no puede ser Vacío!');
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
        ActivaBoton(btn);
        return false;
    });
    //Borrar tipo de bien o servicio
    $('#modificarTipoBnSvs').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'TipoBnSv';
        confdel(id, tip);
    });
    //Eliminar tipo de bien o servicio
    $("#divBtnsModalDel").on('click', '#btnConfirDelTipoBnSv', function () {
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
    $('#divForms').on('click', '#btnAddBnSv', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
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
                    if (r == '1') {
                        let id = 'tableBnSv';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Bien o servicio Agregado Correctamente');
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
    //Actualizar bien o servicio -> formulario
    $('#modificarBnSvs').on('click', '.editar', function () {
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
    $('#divForms').on('click', '#btnUpBnSv', function () {
        let id;
        var btn = $(this).get(0);
        InactivaBoton(btn);
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
        ActivaBoton(btn);
        return false;
    });
    //Borrar bien o servicio
    $('#modificarBnSvs').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'BnSv';
        confdel(id, tip);
    });
    //Eliminar bien o servicio
    $("#divBtnsModalDel").on('click', '#btnConfirDelBnSv', function () {
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
    $('#btnExcelHomolgBnSv').on('click', function () {
        let id = '1';
        $('<form action="datos/listar/homologa_bn_sv.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>')
            .appendTo('body').submit();
    });
    $('#btnExcelHomolgEscHonor').on('click', function () {
        let id = '1';
        $('<form action="datos/listar/homologa_esc_honor.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>')
            .appendTo('body').submit();
    });
    $('.subirHomologacion').on('click', function () {
        let tipo = $(this).attr('text');
        $.post("datos/cargar/homologacion.php", { tipo: tipo }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-lg');
            //$('#divTamModalForms').addClass('modal-sm');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#subirFormatos').on('click', function () {
        alert('Subir formatos');
    });
    $('#divModalForms').on('click', '#btnGuardaHomologacion', function () {
        let tipo = $(this).attr('text');
        var btn = $(this).get(0);
        InactivaBoton(btn);
        if ($('#fileHomologacion').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe elegir un archivo!');
        } else {
            let archivo = $('#fileHomologacion').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext !== '.csv') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Solo se permite documentos .csv!');
                ActivaBoton(btn);
                return false;
            } else if ($('#fileHomologacion')[0].files[0].size > 2097152) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Documento debe tener un tamaño menor a 2Mb!');
                ActivaBoton(btn);
                return false;
            }
            let datos = new FormData();
            datos.append('fileHomologacion', $('#fileHomologacion')[0].files[0]);
            datos.append('tipo', tipo);
            $.ajax({
                type: 'POST',
                url: 'registrar/new_homologacion.php',
                contentType: false,
                data: datos,
                processData: false,
                cache: false,
                success: function (r) {
                    if (r == '1') {
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Proceso realizado Correctamente');
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

    $('#divModalForms').on('click', '#btnGuardaFormatoCtt', function () {
        var btn = $(this).get(0);
        InactivaBoton(btn);
        $('.is-invalid').removeClass('is-invalid');
        if ($('#slcTipoFormato').val() === '0') {
            $('#slcTipoFormato').addClass('is-invalid');
            $('#slcTipoFormato').focus();
            mjeError('¡Debe seleccionar un tipo de formato!');
        } else if ($('#slcTipoBnSv').val() === '0') {
            $('#slcTipoBnSv').addClass('is-invalid');
            $('#slcTipoBnSv').focus();
            mjeError('¡Debe seleccionar un tipo de bien o servicio!');
        } else if ($('#fileContratacion').val() === '') {
            $('#fileContratacion').addClass('is-invalid');
            $('#fileContratacion').focus();
            mjeError('¡Debe elegir un archivo!');
        } else {
            let archivo = $('#fileContratacion').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext !== '.docx') {
                mjeError('¡Solo se permite documentos .docx!');
                ActivaBoton(btn);
                return false;
            } else if ($('#fileContratacion')[0].files[0].size > 10485760) {
                mjeError('¡Documento debe tener un tamaño menor a 10Mb!');
                ActivaBoton(btn);
                return false;
            }
            let datos = new FormData();
            datos.append('fileContratacion', $('#fileContratacion')[0].files[0]);
            datos.append('slcTipoFormato', $('#slcTipoFormato').val());
            datos.append('slcTipoBnSv', $('#slcTipoBnSv').val());
            $('#btnGuardaContratacion').attr('disabled', true);
            $('#btnGuardaContratacion').html('<i class="fas fa-spinner fa-pulse"></i> Cargando...');
            $.ajax({
                type: 'POST',
                url: 'registrar/new_formato.php',
                contentType: false,
                data: datos,
                processData: false,
                cache: false,
                success: function (r) {
                    $('#btnGuardaContratacion').attr('disabled', false);
                    $('#btnGuardaContratacion').html('Guardar');
                    if (r == 'ok') {
                        $('#tableFormCtt').DataTable().ajax.reload();
                        $('#divModalForms').modal('hide');
                        mje('Proceso realizado Correctamente');
                    } else {
                        mjeError(r);
                    }
                }
            });
        }
        ActivaBoton(btn);
        return false;
    });
    $('#tableFormCtt').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        Swal.fire({
            title: "¿Confirma que desea eliminar el documento?",
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
                    url: 'eliminar/del_formato.php',
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            $('#tableFormCtt').DataTable().ajax.reload();
                            mje('Documento eliminado correctamente');
                        } else {
                            mjeError(r);
                        }
                    }
                });
            }
        });
    });
    $('#tableFormCtt').on('click', '.descargar', function () {
        let id = $(this).attr('value');
        $('<form action="datos/descargar/formato_ctt.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>')
            .appendTo('body').submit();
    });
    $('#btnDownloadVarsCtt').on('click', function () {
        window.location.href = 'datos/listar/variables_contratacion.php';
    });
    $(document).ready(function () {
        var maxBnSv = 100;
        var inputHTML = '<div class="input-group input-group-sm mb-3"><input name="txtBnSv[]" type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm"><div class="input-group-prepend"><a href="javascript:void(0);" class="btn btn-outline-danger btn_removeBnSv" title="Quitar"><span class="fas fa-minus-circle fa-lg"></span></a></div></div>';
        var x = 1;
        $('#divForms').on('click', '#celdaPR .btn_addBnSv', function () {
            if (x < maxBnSv) {
                x++;
                $('#content_inputs').append(inputHTML);
            }
            return false;
        });
        $('#divForms').on('click', '.btn_removeBnSv', function (e) {
            e.preventDefault();
            $(this).parent('div').parent('div').remove();
            x--;
            return false;
        });
    });
    $('#divModalForms').on('focus', '.val_bnsv', function () {
        var elemento = $(this);
        var id = elemento.attr('text');
        $.ajax({
            type: 'POST',
            url: 'datos/listar/valor_servicio.php',
            data: { id: id },
            success: function (r) {
                elemento.val(r);
            }
        });
    });
})(jQuery);