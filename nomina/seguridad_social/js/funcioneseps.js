(function($) {
    //Agregar EPS
    var reloadtable = function(nom) {
        $(document).ready(function() {
            var table = $('#' + nom).DataTable();
            table.ajax.reload();
        });
    };
    $("#divModalForms").on('click', '#btnAddEps', function() {
        let nit = $("#txtNitEps").val();
        let nom = $("#txtNomEps").val();
        if (nit === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("NIT  no puede estar vacio");
            return false;
        } else if (nom === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Nombre EPS no puede estar vacio");
            return false;
        } else {
            let deps = $("#formAddEps").serialize();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/nomina/seguridad_social/eps/registrar/neweps.php',
                data: deps,
                success: function(r) {
                    switch (r) {
                        case '0':
                            $('#divModalError').modal('show');
                            $('#divMsgError').html("EPS ya se encuentra registrada");
                            break;
                        case '1':
                            let id = 'tableEmpEPSs';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("EPS creada correctamente");
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

    });
    //Actualizar EPS
    $('#modificarEmpEPSs').on('click', '.editar', function() {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_epss.php", { id: id }, function(he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#divModalForms").on('click', '.actualizarEPSs', function() {
        let nit = $("#txtNitUpEps").val();
        let nom = $("#txtNomUpEps").val();
        if (nit === "") {
            $('#divModalAddUserError').modal('show');
            $('#divAddUserMsgError').html("NIT  no puede estar vacio");
            return false;
        } else if (nom === "") {
            $('#divModalAddUserError').modal('show');
            $('#divAddUserMsgError').html("Nombre EPS no puede estar vacio");
            return false;
        } else {
            let deps = $("#formUpEps").serialize();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/nomina/seguridad_social/eps/actualizar/upeps.php',
                data: deps,
                success: function(r) {
                    if (r === '1') {
                        let id = "tableEmpEPSs";
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalAddUserDone').modal('show');
                        $('#divAddUserMsgDone').html("EPS actualizada correctamente");
                    } else {
                        $('#divModalAddUserError').modal('show');
                        $('#divAddUserMsgError').html(r);
                    }
                }
            });
            return false;
        }

    });
    //Eliminar EPS (confirmar)
    $("#tableEmpEPSs").on('click', '.borrar', function() {
        let id = $(this).attr('value');
        let tip = 'EmpEps';
        confdel(id, tip);
    });
    //Eliminar EPS
    $("#divBtnsModalDel").on('click', '#btnConfirDelEmpEps', function() {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: window.urlin + '/nomina/seguridad_social/eps/eliminar/deleps.php',
            data: {},
            success: function(r) {
                if (r === '1') {
                    let id = 'tableEmpEPSs';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Registro eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
    });
    //Registrar ARL
    $("#divModalForms").on('click', '#btnAddArls', function() {
        let nit = $("#txtNitArl").val();
        let nom = $("#txtNomArl").val();
        if (nit === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("NIT  no puede estar vacio");
            return false;
        } else if (nom === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Nombre ARL no puede estar vacio");
            return false;
        } else {
            let darl = $("#formAddArl").serialize();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/nomina/seguridad_social/arl/registrar/newarl.php',
                data: darl,
                success: function(r) {
                    switch (r) {
                        case '0':
                            $('#divModalError').modal('show');
                            $('#divModalError').html("ARL ya se encuentra registrada");
                            break;
                        case '1':
                            let id = 'tableEmpARLs';
                            reloadtable(id);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("ARL creada correctamente");
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

    });
    //Actualizar ARL
    $("#modificarEmpARLs").on('click', '.editar', function() {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_arls.php", { id: id }, function(he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#divModalForms").on('click', '#btnUpArl', function() {
        let nit = $("#txtNitUpArl").val();
        let nom = $("#txtNomUpArl").val();
        if (nit === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("NIT  no puede estar vacio");
            return false;
        } else if (nom === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Nombre ARL no puede estar vacio");
            return false;
        } else {
            let darl = $("#formUpArl").serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/uparl.php',
                data: darl,
                success: function(r) {
                    if (r === '1') {
                        let id = 'tableEmpARLs';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("ARL actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
            return false;
        }
    });
    //Eliminar ARL (confirmar)
    $("#modificarEmpARLs").on('click', '.borrar', function() {
        let idarl = $(this).attr('value');
        let tip = 'EmpARLs';
        confdel(idarl, tip);
    });
    //Eliminar ARL
    $("#divBtnsModalDel").on('click', '#btnConfirDelEmpARLs', function() {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delarl.php',
            data: {},
            success: function(r) {
                if (r === '1') {
                    let id = 'tableEmpARLs';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Registro eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });

    });
    //Registrar AFP
    $("#divModalForms").on('click', '#btnAddAfp', function() {
        let nit = $("#txtNitAfp").val();
        let nom = $("#txtNomAfp").val();
        if (nit === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("NIT  no puede estar vacio");
            return false;
        } else if (nom === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Nombre AFP no puede estar vacio");
            return false;
        } else {
            let dafp = $("#formAddAfp").serialize();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/nomina/seguridad_social/afp/registrar/newafp.php',
                data: dafp,
                success: function(r) {
                    switch (r) {
                        case '0':
                            $('#divModalError').modal('show');
                            $('#divMsgError').html("AFP ya se encuentra registrada");
                            break;
                        case '1':
                            let id = "tableEmpAFPs";
                            reloadtable(id);
                            $("#divModalForms").modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("AFP creada correctamente");
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
    });
    //Actualizar AFP
    $("#modificarEmpAFPs").on('click', '.editar', function() {
        let id = $(this).attr('value');
        $.post("datos/actualizar/form_up_afps.php", { id: id }, function(he) {
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#divModalForms").on('click', '#btnUpAfp', function() {
        let nit = $("#txtNitUpAfp").val();
        let nom = $("#txtNomUpAfp").val();
        if (nit === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("NIT  no puede estar vacio");
            return false;
        } else if (nom === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Nombre AFP no puede estar vacio");
            return false;
        } else {
            let dafp = $("#formUpAfp").serialize();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/nomina/seguridad_social/afp/actualizar/upafp.php',
                data: dafp,
                success: function(r) {
                    if (r === '1') {
                        let id = "tableEmpAFPs";
                        reloadtable(id);
                        $("#divModalForms").modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("AFP actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
            return false;
        }

    });
    //Eliminar AFP (confirmar)
    $("#modificarEmpAFPs").on('click', '.borrar', function() {
        let idafp = $(this).attr('value');
        let tip = 'EmpAFPs';
        confdel(idafp, tip);
    });
    //Eliminar AFP
    $("#divBtnsModalDel").on('click', '#btnConfirDelEmpAFPs', function() {
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delafp.php',
            data: {},
            success: function(r) {
                if (r === '1') {
                    let id = "tableEmpAFPs";
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Registro eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });

    });
    var setIdioma = {
        "decimal": "",
        "emptyTable": "No hay información",
        "info": "Mostrando _START_ - _END_ registros de _TOTAL_ ",
        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
        "infoFiltered": "(Filtrado de _MAX_ entradas en total )",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "_MENU_ Registros",
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
    var confdel = function(i, t) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: window.urlin + '/nomina/empleados/eliminar/confirdel.php',
            data: { id: i, tip: t }
        }).done(function(res) {
            $('#divModalConfDel').modal('show');
            $('#divMsgConfdel').html(res.msg);
            $('#divBtnsModalDel').html(res.btns);
        });
        return false;
    };
    $(document).ready(function() {
        $('#tableEmpEPSs').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("datos/registrar/form_add_eps.php", function(he) {
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
                url: 'datos/listar/datos_epss.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'nombre' },
                { 'data': 'nit' },
                { 'data': 'telefono' },
                { 'data': 'correo' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ]
        });
        $('#tableEmpEPSs').wrap('<div class="overflow" />');
        //dataTable ARLs
        $('#tableEmpARLs').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("datos/registrar/form_add_arls.php", function(he) {
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
                url: 'datos/listar/datos_arls.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'nombre' },
                { 'data': 'nit' },
                { 'data': 'telefono' },
                { 'data': 'correo' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ]
        });
        $('#tableEmpARLs').wrap('<div class="overflow" />');
        //dataTable AFPs
        $('#tableEmpAFPs').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("datos/registrar/form_add_afps.php", function(he) {
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
                url: 'datos/listar/datos_afps.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'nombre' },
                { 'data': 'nit' },
                { 'data': 'telefono' },
                { 'data': 'correo' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ]
        });
        $('#tableEmpAFPs').wrap('<div class="overflow" />');
    });
})(jQuery);