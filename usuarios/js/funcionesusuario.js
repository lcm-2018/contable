(function ($) {
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
            url: window.urlin + '/usuarios/eliminar/confirdel.php',
            data: { id: i, tip: t }
        }).done(function (res) {
            $('#divModalConfDel').modal('show');
            $('#divMsgConfdel').html(res.msg);
            $('#divBtnsModalDel').html(res.btns);
        });
        return false;
    };
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
    if ($("#peReg").val() == '1') {
        setdom = "<'row'<'col-md-5'l><'bttn-plus-dt col-md-2'B><'col-md-5'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    } else {
        setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    }
    $(document).ready(function () {
        $('#tableListUsuarios').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formadduser.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/list_usuarios.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'num_doc' },
                { 'data': 'nombres' },
                { 'data': 'apellidos' },
                { 'data': 'correo' },
                { 'data': 'user' },
                { 'data': 'rol' },
                { 'data': 'estado' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ]
        });
        $('#tableListUsuarios').wrap('<div class="overflow" />');
        $('#tablePerfilesUsuarios').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    $.post("datos/registrar/formaddperfil.php", function (he) {
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
                url: 'datos/listar/list_perfiles.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'id_rol' },
                { 'data': 'rol' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ]
        });
        $('#tablePerfilesUsuarios').wrap('<div class="overflow" />');
    });
    //agregar usuario del sistema
    $("#divModalForms").on('click', '#btnAddUser', function () {
        let login = $("#txtlogin").val();
        let pass = $("#passuser").val();
        let rol = $("#slcRolUser").val();
        if (login == "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Login  no puede estar vacio");
            return false;
        } else if (pass == "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Contraseña no puede estar vacia");
            return false;
        } else if (rol == "0") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Debe elegir un Rol de usuario");
            return false;
        } else {
            let duser = $("#formAddUser").serialize();
            let passencrp = hex_sha512(pass);
            duser = duser + '&passu=' + passencrp;
            $.ajax({
                type: 'POST',
                url: window.urlin + '/usuarios/registrar/newuser.php',
                data: duser,
                success: function (r) {
                    switch (r) {
                        case '0':
                            $('#divModalError').modal('show');
                            $('#divMsgError').html('Usuario ya existe');
                            break;
                        case '1':
                            let id_t = 'tableListUsuarios';
                            reloadtable(id_t);
                            $('#divModalForms').modal('hide');
                            $('#divModalDone').modal('show');
                            $('#divMsgDone').html("Usuario creado correctamente");
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
    //Actualizar usuario del sistema
    $("#divModalForms").on('click', '#btnAddPerfil', function () {
        var data = $('#txtPerfil').val();
        if (data == '') {
            $('#txtPerfil').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese un perfil');
        } else {
            $.ajax({
                type: 'POST',
                url: window.urlin + '/usuarios/registrar/newperfil.php',
                data: { data: data },
                success: function (r) {
                    if (r == 'ok') {
                        let id_t = 'tablePerfilesUsuarios';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Perfíl creado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $("#modificarListUsers").on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/formupuser.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#modificaPerfilesUsuarios").on('click', '.editar', function () {
        let id = $(this).parent().attr('text');
        $.post("datos/actualizar/formupperfil.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $("#divModalForms").on('click', '#btnUpPerfil', function () {
        var perfil = $('#txtPerfil').val();
        var id_perfil = $('#id_perfil').val();
        if (perfil == '') {
            $('#txtPerfil').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Ingrese un perfil');
        } else {
            $.ajax({
                type: 'POST',
                url: window.urlin + '/usuarios/actualizar/upperfil.php',
                data: { perfil: perfil, id_perfil: id_perfil },
                success: function (r) {
                    if (r == 'ok') {
                        let id_t = 'tablePerfilesUsuarios';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Perfíl actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    //up usuario del sistema
    $("#divModalForms").on('click', '#btnUpUser', function () {
        let login = $("#txtUplogin").val();
        let pass = $("#passUpuser").val();
        if (login === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Login  no puede estar vacio");
            return false;
        } else if (pass === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Contraseña no puede estar vacia");
            return false;
        } else {
            let mpass = hex_sha512($('#passUpuser').val());
            if ($('#passAnterior').val() === $('#passUpuser').val()) {
                mpass = $('#passAnterior').val();
            }
            let duser = $("#formAddUser").serialize();
            duser = duser + '&passUp=' + mpass;
            $.ajax({
                type: 'POST',
                url: window.urlin + '/usuarios/actualizar/upuser.php',
                data: duser,
                success: function (r) {
                    if (r == '1') {
                        let id_t = 'tableListUsuarios';
                        reloadtable(id_t);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Usuario actualizado correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
            return false;
        }

    });
    //Eliminar usuario (confirmar)
    $('#modificarListUsers').on('click', '.borrar', function () {
        let id_pd = $(this).attr('value');
        let tip = 'UserSistema';
        confdel(id_pd, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelUserSistema', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/deluser.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableListUsuarios';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#modificaPerfilesUsuarios').on('click', '.borrar', function () {
        let id = $(this).parent().attr('text');
        let tip = 'PerfilUsuario';
        confdel(id, tip);
    });
    $('#divModalConfDel').on('click', '#btnConfirDelPerfilUsuario', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/delperfil.php',
            data: { id: id },
            success: function (r) {
                if (r == 'ok') {
                    let id = 'tablePerfilesUsuarios';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Eliminado correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    //actualizar estado usuarios del sistema
    $('#modificarListUsers').on('click', '.estado', function () {
        let datas = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: window.urlin + '/usuarios/actualizar/upestado.php',
            data: { datas: datas },
            success: function (r) {
                let id = 'tableListUsuarios';
                reloadtable(id);
            }
        });
        return false;
    });
    $('.campo span').click(function () {
        let type = $('#passuser').attr('type');
        if (type === 'password') {
            $('#passuser').attr('type', 'text');
            $('#icon').removeClass('fa fa-eye').addClass('fa fa-eye-slash');
            $("#icon").css("color", "#E74C3C");
        } else {
            $('#passuser').attr('type', 'password');
            $('#icon').removeClass('fa fa-eye-slash').addClass('fa fa-eye');
            $("#icon").css("color", "#2ECC71");
        }
    });

    //-----------------------------------------------
    $('#divForms').on("change", "#sl_centroCosto", function () {
        $('#sl_areaCentroCosto').load('../usuarios/common/listar_areas_centroCosto.php', { id_centroCosto: $(this).val(), titulo: '', todas: true }, function () { });
    });
    //$('#sl_centroCosto').trigger('change');

    //---------------------------------
    $('#divModalForms').on('click', '.chk_sedes', function () {
        let id_sede = $(this).attr('value');

        var sedesSeleccionadas = [];

        $(".chk_sedes:checked").each(function () {
            sedesSeleccionadas.push($(this).val()); // Guarda los valores de las checkboxes marcadas
        });

        //var data = $('#formAddUser').serialize();
        var data = {sed:sedesSeleccionadas};

        if ($.fn.DataTable.isDataTable('#tb_bodegas')) {
            $('#tb_bodegas').DataTable().destroy();
        }
        $('#tb_bodegas').DataTable({
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: 'datos/listar/listar_bodegas.php',
                type: 'POST',
                dataType: 'json',
                data: data
            },
            columns: [
                { 'data': 'select' },
                { 'data': 'id_bodega' }, //Index=1
                { 'data': 'nombre' },
                { 'data': 'tipo' },
                { 'data': 'estado' },
            ],
            columnDefs: [
                { orderable: false, targets: [0] },
                { class: 'text-wrap', targets: [] }
            ],
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('#tb_bodegas').wrap('<div class="overflow"/>');
    });

    
})(jQuery);