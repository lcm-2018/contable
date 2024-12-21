let timeout;
let actual = "EAC" + window.location + 'II';
let esta = actual.indexOf('index')
if (esta === -1) {
    document.onmousemove = function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            $.ajax({
                type: 'POST',
                url: window.urlin + '/cerrar_sesion.php',
                success: function (r) {
                    $('#divModalXSesion').modal('show');

                }
            });
        }, 600000);
    }
}
(function ($) {
    /*$(document).ready(function () {
        $("body").on("contextmenu", function (e) {
            return false;
        });
    });*/
    "use strict";
    $("#sidebarToggle").click(function () {
        let val = $(this).val();
        $.ajax({
            type: 'POST',
            url: window.urlin + '/actualizar/hidenav.php',
            data: { val: val }
        });
        $("body").toggleClass("sb-sidenav-toggled");
        let a = $('.sb-nav-fixed').hasClass('sb-sidenav-toggled');
        if (a) {
            $('#navlateralSH').removeClass('fa-bars');
            $('#navlateralSH').addClass('fa-ellipsis-v');
        } else {
            $('#navlateralSH').removeClass('fa-ellipsis-v');
            $('#navlateralSH').addClass('fa-bars');
        }
    });

    $("#btnLogin").click(function () {
        let user = $("#txtUser").val();
        let clave = $("#passuser").val();
        if (user === "") {
            $('#divModalError').modal('show');
            $('#divErrorLogin').html("Debe ingresar Usuario");
        } else if (pass === "") {
            $('#divModalError').modal('show');
            $('#divErrorLogin').html("Debe ingresar Contraseña");
        } else {
            var pass = hex_sha512(clave);
            var passwd = hex_sha512(clave.toLowerCase());
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'validarLogin.php',
                data: { user: user, pass: pass, passwd: passwd }
            }).done(function (res) {
                switch (res.mensaje) {
                    case 0:
                        $('#divModalError').modal('show');
                        $('#divErrorLogin').html("Usuario y/o Contraseña incorrecto(s)");
                        break;
                    case 1:
                        window.location = "vigencia.php";
                        break;
                    case 2:
                        window.location = "terceros/gestion/detalles_tercero.php";
                        break;
                    case 3:
                        $('#divModalError').modal('show');
                        $('#divErrorLogin').html("Usuario suspendido temporalmente");
                        break;
                    default:
                        $('#divModalError').modal('show');
                        $('#divErrorLogin').html(res.mensaje);
                        break;
                }
            });
        }
        return false;
    });

    $("#btnEntrar").click(function () {
        var emp = $("#slcEmpresa").val();
        var vig = $("#slcVigencia").val();
        if (emp === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe selecionar una empresa');
        } else if (vig === '0') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe selecionar una vigencia');
        } else {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'variablesinicio.php',
                data: { vig: vig }
            }).done(function (res) {
                if (res === 1) {
                    window.location = "inicio.php";
                }
            });
        }
        return false;
    });
    $("#btnUpEmpresa").click(function () {
        let nit = $("#txtUplogin").val();
        let nombre = $("#passUpuser").val();
        if (nit === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("NIT no puede ser vacio");
            return false;
        } else if (nombre === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Nombre no puede ser vacio");
            return false;
        } else {
            let dempresa = $("#formUpEmpresa").serialize();
            $.ajax({
                type: 'POST',
                url: 'upempresa.php',
                data: dempresa,
                success: function (r) {
                    if (r === '1') {
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Empresa actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }

            });
            return false;
        }

    });
    //modal para cambiar contraseña
    $('#linkChangePass').on('click', function () {
        $.post(window.urlin + "/actualizar/form_up_password.php", function (he) {
            $('#divTamModalPermisos').removeClass('modal-xl');
            $('#divTamModalPermisos').removeClass('modal-2x');
            $('#divTamModalPermisos').removeClass('modal-lg');
            $('#divTamModalPermisos').addClass('modal-sm');
            $('#divModalPermisos').modal('show');
            $("#divTablePermisos").html(he);
        });
    });
    //modificar contraseña
    $('#divModalPermisos').on('click', '#btnChangePass', function () {
        let pass = $("#passAnt").val();
        let newpas = $("#passNew").val();
        let newpasconfir = $("#passNewConf").val();
        if (pass == "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Contraseña actual no puede ser vacia");
        } else if (newpas == "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Nueva contraseña no puede ser vacia");
        } else if (newpasconfir == "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Confirmar contraseña no puede ser vacia");
        } else if (newpasconfir != newpas) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Las contraseñas no coinciden");
        } else if (pass == newpas) {
            $('#divModalError').modal('show');
            $('#divMsgError').html("La nueva contraseña es igual a la actual");
        } else {
            let pwd = hex_sha512(pass);
            let newpwd = hex_sha512(newpas);
            $.ajax({
                type: 'POST',
                url: window.urlin + '/actualizar/uppassword.php',
                data: { pwd: pwd, newpwd: newpwd },
                success: function (r) {
                    if (r == 'ok') {
                        $('#divModalPermisos').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Contraseña actualizada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
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
    var setdom = "<'row'<'col-md-5'l><'bttn-excel col-md-2'B><'col-md-5'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    //Modal permisos de usuarios 
    var permisosModulos = function (id) {
        $.post(window.urlin + "/actualizar/datos_up_permisos.php", { id: id }, function (he) {
            $('#divTamModalPermisos').removeClass('modal-xl');
            $('#divTamModalPermisos').removeClass('modal-sm');
            $('#divTamModalPermisos').addClass('modal-lg');
            $('#divModalPermisos').modal('show');
            $("#divTablePermisos").html(he);
        });
    }
    var permisosOpciones = function (modulo, usuario) {
        $.post(window.urlin + "/actualizar/datos_permisos_opciones.php", { modulo: modulo, usuario: usuario }, function (he) {
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    }
    var permisosOpcionesRol = function (id) {
        $.post("datos/actualizar/formupconceptos.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    }
    $('#tableListUsuarios').on('click', '.setPermisos', function () {
        let id = $(this).attr('value');
        permisosModulos(id);
    });
    $('#divModalPermisos').on('click', '#tableModulos .listPermisos', function () {
        let modulo = $(this).attr('value');
        let user = $('#id_usuario').val();
        permisosOpciones(modulo, user);
    });
    $("#divModalPermisos").on('click', '#modulos-tab', function () {
        $('#divTamModalPermisos').removeClass('modal-lg');
        $('#divTamModalPermisos').addClass('modal-xl');
    });
    $("#divModalPermisos").on('click', '#crud-tab', function () {
        $('#divTamModalPermisos').removeClass('modal-xl');
        $('#divTamModalPermisos').addClass('modal-lg');
    });
    $("#divModalPermisos").on('click', '#tableModulos .estado', function () {
        var ids = $(this).attr('value');
        var id_user = $('#id_usuario').val();
        $.ajax({
            type: 'POST',
            url: '../actualizar/permiso_modulo.php',
            data: { ids: ids, id_user: id_user },
            success: function (r) {
                if (r == 'ok') {
                    permisosModulos(id_user);
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $("#divModalForms").on('click', '#tableOpcionesModulo .estado', function () {
        var ids = $(this).attr('value');
        var id_user = $('#id_usuario').val();
        var id_modulo = $('#id_modulo').val();
        $.ajax({
            type: 'POST',
            url: '../actualizar/up_permisos_opciones.php',
            data: { ids: ids, id_user: id_user },
            success: function (r) {
                if (r == 'ok') {
                    permisosOpciones(id_modulo, id_user);
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $("#modificaPerfilesUsuarios").on('click', '.setPermisos', function () {
        let id = $(this).parent().attr('text');
        permisosOpcionesRol(id);
    });
    $("#divModalForms").on('click', '#tableOpcionesModuloRol .estado', function () {
        var ids = $(this).attr('value');
        var id_rol = $('#id_rol').val();
        $.ajax({
            type: 'POST',
            url: '../actualizar/up_permisos_opciones_rol.php',
            data: { ids: ids, id_rol: id_rol },
            success: function (r) {
                if (r == 'ok') {
                    permisosOpcionesRol(id_rol);
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#passuser').on('focus', function () {
        $(this).on('keydown', function (e) {
            if (e.originalEvent.getModifierState("CapsLock")) {
                $('#caps-lock-message').show();
            } else {
                $('#caps-lock-message').hide();
            }
        }).on('blur', function () {
            $('#caps-lock-message').hide();
        });
    });
    //Modal cierre de periodos 
    function cierrePeriodo() {
        $.post(window.urlin + "/actualizar/form_cierre_periodo.php", function (he) {
            $('#divTamModalPermisos').removeClass('modal-xl');
            $('#divTamModalPermisos').removeClass('modal-sm');
            $('#divTamModalPermisos').addClass('modal-lg');
            $('#divModalPermisos').modal('show');
            $("#divTablePermisos").html(he);
        });
    }
    function GestionDocs() {
        $.post(window.urlin + "/actualizar/form_gestion_docs.php", function (he) {
            $('#divTamModalPermisos').removeClass('modal-xl');
            $('#divTamModalPermisos').removeClass('modal-sm');
            $('#divTamModalPermisos').addClass('modal-lg');
            $('#divModalPermisos').modal('show');
            $("#divTablePermisos").html(he);
        });
    }
    $('#hrefCierre').on('click', function () {
        cierrePeriodo();
    });

    $("#divTablePermisos").on('click', '.cerrar', function () {
        var id = $(this).attr('text');
        Swal.fire({
            title: "¿Confirma cerrar periodo?, Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#00994C",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si!",
            cancelButtonText: "NO",
        }).then((result) => {
            if (result.isConfirmed) {
                var ruta = window.urlin + '/actualizar/cierre_periodo.php';
                $.ajax({
                    type: 'POST',
                    url: ruta,
                    data: { id: id },
                    success: function (r) {
                        if (r == 'ok') {
                            cierrePeriodo();
                            mje("Registro exitoso");
                        } else {
                            mjeError("Error: " + r);
                        }
                    }
                });
            }
        });
        return false;
    });
    $('#hrefGestionDocs').on('click', function () {
        window.location = window.urlin + '/documentos/maestro.php';
    });
    $("#divTablePermisos").on('click', '#dataTablePermiso span', function () {
        let caden = $(this).attr('value');
        let cad = caden.split("|");
        let est = cad[0] == 'SI' ? '1' : '0';
        let id = cad[1];
        let perm = cad[2];
        if (est === '1') {
            $(this).removeClass('fa-check-circle');
            $(this).removeClass('circle-verde');
            $(this).addClass('fa-times-circle');
            $(this).addClass('circle-rojo');
            $(this).attr('value', 'NO|' + id + '|' + perm)
        } else {
            $(this).removeClass('fa-times-circle');
            $(this).removeClass('circle-rojo');
            $(this).addClass('fa-check-circle');
            $(this).addClass('circle-verde');
            $(this).attr('value', 'SI|' + id + '|' + perm)
        }
        $.ajax({
            type: 'POST',
            url: window.urlin + '/actualizar/uppermisos.php',
            data: { est: est, id: id, perm: perm },
            success: function (r) {
                if (r !== '1') {
                    alert(r + ' Recargar Página');
                }
            }
        });
        return false;
    });
    $('#fullscreen a').click(function () {
        if ((document.fullScreenElement && document.fullScreenElement !== null) || (!document.mozFullScreen && !document.webkitIsFullScreen)) {
            if (document.documentElement.requestFullScreen) {
                document.documentElement.requestFullScreen();
            } else if (document.documentElement.mozRequestFullScreen) {
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.webkitRequestFullScreen) {
                document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
            }
            $('#iconFS').removeClass('fas fa-expand-arrows-alt fa-lg').addClass('fas fa-compress-arrows-alt fa-lg');
            $('#iconFS').attr('title', 'Reducir')
        } else {
            if (document.cancelFullScreen) {
                document.cancelFullScreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            }
            $('#iconFS').removeClass('fas fa-compress-arrows-alt fa-lg').addClass('fas fa-expand-arrows-alt fa-lg');
            $('#iconFS').attr('title', 'Ampliar')
        }
    });
    //Actualizar Perfil usuario del sistema
    $("#btnUpUserPerfil").click(function () {
        let login = $("#txtUsuario").val();
        if (login === "") {
            $('#divModalError').modal('show');
            $('#divMsgError').html("Login  no puede estar vacio");
        } else {
            let duser = $("#formUpUser").serialize();
            $.ajax({
                type: 'POST',
                url: 'upuser.php',
                data: duser,
                success: function (r) {
                    if (r === '1') {
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
    $('.table-hover tbody').on('dblclick', 'tr', function () {
        let table = $('.table-hover').DataTable();
        if ($(this).hasClass('selecionada')) {
            $(this).removeClass('selecionada');
        } else {
            table.$('tr.selecionada').removeClass('selecionada');
            $(this).addClass('selecionada');
        }
    });
    $('#dataTableLiqNom tbody').on('dblclick', 'tr', function () {
        let table = $('#dataTableLiqNom').DataTable();
        if ($(this).hasClass('selecionada')) {
            $(this).removeClass('selecionada');
        } else {
            table.$('tr.selecionada').removeClass('selecionada');
            $(this).addClass('selecionada');
        }
    });
    $(document).ready(function () {
        $('#divModalForms').addClass('overflow');
        let id = $('#idEmpNovEps').val();
        $('#dataTableLiqNom').DataTable({
            scrollY: false,
            scrollX: true,
            scrollCollapse: true,
            paging: true,
            fixedColumns: {
                left: 1
            },
            dom: setdom,
            language: setIdioma,
            buttons: [
                'excel'
            ],
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('#dataTablePermiso').DataTable({
            "autoWidth": true,
            language: setIdioma,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
            "pageLength": -1
        });
        $('.bttn-excel button').html('<span class="fas fa-file-excel fa-lg"></span>');
        $('.bttn-excel').attr('title', 'Exportar a Excel');

    });
    $(document).ready(function () {
        $('.dropdown-submenu a.test').on("click", function (e) {
            $(this).next('ul').toggle();
            e.stopPropagation();
            e.preventDefault();
        });
    });
    $('.table').on('click', '.sorting', function () {
        $('.sorting').removeClass('div-gris');
        $(this).addClass('div-gris');
    });
    $('.dropdown-menu a.dropdown-toggle').on('click', function (e) {
        if (!$(this).next().hasClass('show')) {
            $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
        }
        var $subMenu = $(this).next(".dropdown-menu");
        $subMenu.toggleClass('show');
        $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function (e) {
            $('.dropdown-submenu .show').removeClass("show");
        });
        return false;
    });
    $('#btnRegVigencia').on('click', function () {
        $.post(window.urlin + "/nomina/liquidar_nomina/datos/registrar/form_reg_vigencia.php", function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-sm');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnRegVigencia', function () {
        $('.form-control').removeClass('is-invalid');
        if ($('#vigencia').val() == '' || Number($('#vigencia').val()) <= 2022) {
            $('#vigencia').focus();
            $('#vigencia').addClass('is-invalid');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Vigencia debe ser mayor a 2022');
        } else {
            let datos = $('#formRegConcepXvig').serialize();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/nomina/liquidar_nomina/registrar/vigenciaf.php',
                data: datos,
                success: function (r) {
                    if (r.trim() === 'ok') {
                        $('#divModalForms').modal('hide');
                        let id = "tableVigencia";
                        reloadtable(id);
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html("Vigencia registrada correctamente");
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('.btnListLiqPrima').on('click', function () {
        let tipo = $(this).attr('value');
        let url = window.urlin + '/nomina/liquidar_nomina/listempliquidar_prima.php';
        $('<form action="' + url + '" method="post"><input type="hidden" name="tipo" value="' + tipo + '" /></form>').appendTo('body').submit();
    });
    $('#hrefPerfiles').on('click', function () {
        $.post(window.urlin + "/nomina/liquidar_nomina/datos/registrar/form_reg_vigencia.php", function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').addClass('modal-sm');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    var DownoadFile = function (name) {
        $('<form action="' + window.urlin + '/formatos/download_formato.php" method="post"><input type="hidden" name="nom_file" value="' + name + '" /></form>').appendTo('body').submit();
    };
    $('#formatoExcelPto').on('click', function () {
        DownoadFile('cargue_pto.xlsx');
    });
    $('#formatoExcelPuc').on('click', function () {
        DownoadFile('cargue_puc.xlsx');
    });
    $('.tesoreria').on('click', function () {
        let id = $(this).attr('text');
        $('<form action="' + window.urlin + '/tesoreria/lista_documentos_com.php" method="post">' +
            '<input type="hidden" name="var" value="' + id + '" />' +
            '</form>').appendTo('body').submit();
    });
})(jQuery);

function elegirmes(id) {
    if (id > 0) {
        document.forms[0].submit();
    }
}

function pesos(value) {
    // Convertir a número
    let number = parseFloat(value);
    if (isNaN(number)) number = 0;

    // Usar Intl.NumberFormat para formatear como moneda
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP', // Moneda en pesos colombianos
        minimumFractionDigits: 2, // Mínimo 2 decimales
        maximumFractionDigits: 2  // Máximo 2 decimales
    }).format(number);
}