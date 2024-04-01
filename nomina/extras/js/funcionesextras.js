(function ($) {
    var consec = 0;
    var confdel = function (i, t) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: window.urlin + '/almacen/eliminar/confirdel.php',
            data: { id: i, tip: t }
        }).done(function (res) {
            $('#divModalConfDel').modal('show');
            $('#divMsgConfdel').html(res.msg);
            $('#divBtnsModalDel').html(res.btns);
        });
        return false;
    };
    //Agregar horas extra
    $("#btnModalError").click(function () {
        $('#divModalError').modal('hide');
    });
    $("#btnCancelDelHe").click(function () {
        $('#divConfirmdelHex').modal('hide');
    });
    $("#btnXErrorDel").click(function () {
        $('#divModalErrorDelHex').modal('hide');
    });
    $("#btnAddHe").click(function () {
        let hdo = $("#numCantHeDo").val();
        let hno = $("#numCantHeNo").val();
        let rhno = $("#numRecCantHeNo").val();
        let hdd = $("#numCantHeDd").val();
        let rhdd = $("#numRecCantHeDd").val();
        let hnd = $("#numCantHeNd").val();
        let hhd = $("#numCantHeHd").val();
        if (hdo === "99" && hno === "99" && hdd === "99" && hnd === "99" && hhd === "99" && rhno === '99' && rhdd === '99') {
            $('#divModalError').modal('show');
            $('#divMsgError').html("¡Debe ingresar todos los campos!");
            return false;
        }
        let hoex = $("#formAddHe").serialize();
        $.ajax({
            type: 'POST',
            url: 'addhoras.php',
            data: hoex,
            success: function (r) {
                if (r === '1') {
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("¡Horas extras registradas correctamente!");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;

    });

    //eliminar horas extra (confirmar)
    $("#elimhoex button").click(function () {
        let idhoext = $(this).val();
        window.rowdel = $(this).closest("tr").get(0);
        $.ajax({
            type: 'POST',
            url: '../eliminar/confirdel.php',
            data: { idhoext: idhoext },
            success: function (r) {
                $('#divConfirmdelHex').modal('show');
                $('#divMsgConfirmDel').html(r);
            }
        });
        return false;
    });
    //Eliminar horas extras
    $("#btnModalConfdelHe").click(function () {
        $('#divConfirmdelHex').modal('hide');
        $.ajax({
            type: 'POST',
            url: '../eliminar/delhoraex.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    rowdel();
                    $('#divModalHoExitoExito').modal('show');
                    $('#divMsgExitoHoex').html("Registro eliminado correctamente");
                } else {
                    $('#divModalErrorDelHex').modal('show');
                    $('#divMsgErrorDel').html(r);
                }
            }
        });

    });
    //Actualizar horas extra
    $("#btnUpHoex").click(function () {
        let fli = $('#datFecLabHeIup').val();
        let flf = $('#datFecLabHeFup').val();
        let hli = $('#timeInicioHeup').val();
        let hlf = $('#timeFinHeup').val();
        if (fli > flf) {
            $('#divModalErrorUpHoEx').modal('show');
            $('#divMsgErrorUpHoEx').html("Fecha Inicial no puede ser menor que Fecha Final");
        } else if (hli === '') {
            $('#divModalErrorUpHoEx').modal('show');
            $('#divMsgErrorUpHoEx').html("Hora Inicial no puede ser vacía.");
        } else if (hlf === '') {
            $('#divModalErrorUpHoEx').modal('show');
            $('#divMsgErrorUpHoEx').html("Hora final no puede ser vacía.");
        } else {
            let dhoex = $("#formupHoex").serialize();
            $.ajax({
                type: 'POST',
                url: 'uphoex.php',
                data: dhoex,
                success: function (r) {
                    if (r === '1') {
                        $('#divModalHoExDone').modal('show');
                        $('#divMsgDoneHoEx').html("Registro actualizado correctamente");
                    } else {
                        $('#divModalErrorUpHoEx').modal('show');
                        $('#divMsgErrorUpHoEx').html(r);
                    }
                }
            });
        }
        return false;
    });
    //Actualizar viaticos
    $("#btnUpViat").click(function () {
        let form = $(this).parents("#formUpViat");
        let check = checkCampos(form);
        if (!check) {
            $('#divModalErrorUpHoEx').modal('show');
            $('#divMsgErrorUpHoEx').html('Debe completar todos los campos.');
        } else {
            let dviat = $("#formUpViat").serialize();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/nomina/extras/viaticos/actualizar/upviat.php',
                data: dviat,
                success: function (r) {
                    if (r === '1') {
                        $('#divModalViatDone').modal('show');
                        $('#divMsgDoneViat').html("Registro actualizado correctamente");
                    } else {
                        $('#divModalErrorUpHoEx').modal('show');
                        $('#divMsgErrorUpHoEx').html(r);
                    }
                }
            });
        }
        return false;
    });
    //add row viaticos
    $("#btnModalErrorNewViat").click(function () {
        $('#divModalErrorNewViat').modal('hide');
    });
    $("#btnAddRowViat button").click(function () {
        let valxdia = $(this).val();
        consec++;
        $.ajax({
            type: 'POST',
            url: window.urlin + '/nomina/extras/viaticos/registrar/addrow.php',
            data: { consec: consec, valxdia: valxdia },
            success: function (r) {
                if (r !== '0') {
                    $('#fila' + consec).html(r);
                } else {
                    $('#divModalErrorNewViat').modal('show');
                    $('#divMsgErrorNewViat').html("No se admiten mas registros");
                }
            }
        });

        return false;
    });
    //Comprobar inputs vacios
    var checkCampos = function (obj) {
        let camposRellenados = true;
        obj.find("input").each(function () {
            let $this = $(this);
            if ($this.val().length <= 0) {
                camposRellenados = false;
                return false;
            }
        });
        if (camposRellenados == false) {
            return false;
        } else {
            return true;
        }
    }
    //add viaticos
    $("#btnModalExitoNewViat").click(function () {
        $('#divModalExitoNewViat').modal('hide');
    });
    $("#btnAddViat").click(function () {
        let form = $(this).parents("#formAddViat");
        let check = checkCampos(form);
        if ($("#txtDescViat").val() === "") {
            $('#divModalErrorNewViat').modal('show');
            $('#divMsgErrorNewViat').html("Debe Ingresar una descripción");
        } else if (!check) {
            $('#divModalErrorNewViat').modal('show');
            $('#divMsgErrorNewViat').html("Debe diligenciar todos los campos");
        } else {
            let dviat = $("#formAddViat").serialize();
            $.ajax({
                type: 'POST',
                url: window.urlin + '/nomina/extras/viaticos/registrar/addviaticos.php',
                data: dviat,
                success: function (r) {
                    switch (r) {
                        case '0':
                            $('#divModalErrorNewViat').modal('show');
                            $('#divMsgErrorNewViat').html("Empleado no registrado");
                            break;
                        case '1':
                            $("#formAddViat")[0].reset();
                            for (let i = 0; i <= consec; i++) {
                                $('#fila' + i).html("");
                            }
                            consec = 0;
                            $('#divModalExitoNewViat').modal('show');
                            $('#divMsgExitoNewviat').html("Viático(s) agregado(s) correctamente");
                            break;
                        default:
                            $('#divModalErrorNewViat').modal('show');
                            $('#divMsgErrorNewViat').html(r);
                            break;
                    }
                }
            });
        }
        return false;
    });
    //add row viaticos UP
    $("#btnModalErrorNewViat").click(function () {
        $('#divModalErrorNewViat').modal('hide');
    });
    $("#btnAddRowUpViat").click(function () {
        consec++;
        $.ajax({
            type: 'POST',
            url: window.urlin + '/nomina/extras/viaticos/actualizar/addrowupviat.php',
            data: { consec: consec },
            success: function (r) {
                if (r !== '0') {
                    $('#filaup' + consec).html(r);
                } else {
                    $('#divModalErrorUpHoEx').modal('show');
                    $('#divMsgErrorUpHoEx').html("No se admiten mas registros");
                }
            }
        });

        return false;
    });
    //eliminar Viaticos (confirmar)
    $("#elimviat button").click(function () {
        let iddetviat = $(this).val();
        window.rowdel = $(this).closest("tr").get(0);
        $.ajax({
            type: 'POST',
            url: '../eliminar/confirdelviat.php',
            data: { iddetviat: iddetviat },
            success: function (r) {
                $('#divConfirmdelViat').modal('show');
                $('#divMsgConfirmDelviat').html(r);
            }
        });
        return false;
    });
    $("#btnModalConfdelViat").click(function () {
        $('#divConfirmdelViat').modal('hide');
        $.ajax({
            type: 'POST',
            url: '../eliminar/delviatico.php',
            data: {},
            success: function (r) {
                if (r === '1') {
                    rowdel();
                    $('#divModalExitoDelViat').modal('show');
                    $('#divMsgExitoDelViat').html("Registro eliminado correctamente");
                } else {
                    $('#divModalErrorDelHex').modal('show');
                    $('#divMsgErrorDel').html(r);
                }
            }
        });

    });
    //Funcion para hacer los calculos de cantidad de horas extra
    var calhe = function (p, r) {
        let fli = $('#dat' + r + 'FecLabHe' + p + 'I').val();
        let flf = $('#dat' + r + 'FecLabHe' + p + 'F').val();
        let hei = $('#time' + r + 'InicioHe' + p).val();
        let hef = $('#time' + r + 'FinHe' + p).val();
        let tip = p;
        if (fli === '') {
            $('#time' + r + 'FinHe' + p).val('');
            $('#dat' + r + 'FecLabHe' + p + 'I').focus();
            $('#e' + r + 'FecLabHe' + p + 'I').show();
            setTimeout(function () {
                $('#e' + r + 'FecLabHe' + p + 'I').fadeOut(600);
            }, 800);
        } else if (flf === '') {
            $('#time' + r + 'FinHe' + p).val('');
            $("#dat' + r + 'FecLabHe" + p + 'F').focus();
            $('#e' + r + 'FecLabHe' + p + 'F').show();
            setTimeout(function () {
                $('#e' + r + 'FecLabHe' + p + 'F').fadeOut(600);
            }, 800);
        } else if (hei === '') {
            $('#time' + r + 'FinHe' + p).val('');
            $('#time' + r + 'InicioHe' + p).focus();
            $('#etime' + r + 'InicioHe' + p).show();
            setTimeout(function () {
                $('#etime' + r + 'InicioHe' + p).fadeOut(600);
            }, 800);
        } else if (fli > flf) {
            $('#time' + r + 'FinHe' + p).val('');
            $('#dat' + r + 'FecLabHe' + p + 'F').focus();
            $('#dat' + r + 'FecLabHe' + p + 'F').val('');
            $('#e' + r + 'FecMenor' + p).show();
            setTimeout(function () {
                $('#e' + r + 'FecMenor' + p).fadeOut(600);
            }, 800);
            $.ajax({
                type: 'POST',
                url: 'horasnull.php',
                data: { p: p, r: r },
                success: function (rs) {
                    $('#' + r + 'CantHe' + p).html(rs);
                }
            });
        } else {
            $.ajax({
                type: 'POST',
                url: 'cal_he.php',
                data: { fli: fli, flf: flf, hei: hei, hef: hef, tip: tip, r: r },
                success: function (rs) {
                    if (rs === '0') {
                        $('#time' + r + 'FinHe' + p).focus();
                        $('#time' + r + 'FinHe' + p).val('');
                        $.ajax({
                            type: 'POST',
                            url: 'horasnull.php',
                            data: { p: p, r: r },
                            success: function (rs) {
                                $('#' + r + 'CantHe' + p).html(rs);
                            }
                        });
                        $('#e' + r + 'HoraMenor' + p).show();
                        setTimeout(function () {
                            $('#e' + r + 'HoraMenor' + p).fadeOut(600);
                        }, 800);

                    } else {
                        $('#' + r + 'CantHe' + p).html(rs);
                    }
                }
            });
        }
    };
    var comhe = function (p, r) {
        let fli = $('#dat' + r + 'FecLabHe' + p + 'I').val();
        let flf = $('#dat' + r + 'FecLabHe' + p + 'F').val();
        let hei = $('#time' + r + 'InicioHe' + p).val();
        let hef = $('#time' + r + 'FinHe' + p).val();
        if (fli === '' || flf === '' || hei === '' || hef === '') {
            $('#time' + r + 'FinHe' + p).val('');
            $.ajax({
                type: 'POST',
                url: 'horasnull.php',
                data: { p: p, r: r },
                success: function (rs) {
                    $('#' + r + 'CantHe' + p).html(rs);
                }
            });
        } else {
            calhe(p, r);
        }
    };
    var validarfec = function (p, r) {
        let fli = $('#dat' + r + 'FecLabHe' + p + 'I').val();
        let flf = $('#dat' + r + 'FecLabHe' + p + 'F').val();
        if (fli > flf) {
            $('#dat' + r + 'FecLabHe' + p + 'F').val('');
            $('#e' + r + 'FecMenor' + p).show();
            setTimeout(function () {
                $('#e' + r + 'FecMenor' + p).fadeOut(600);
            }, 800);
        } else {
            comhe(p, r);
        }
    };
    var validarhora = function (p) {
        $('#timeFinHe' + p).val('');
    };
    //Calcular Hora Extra Do
    $('#datFecLabHeDoI').on('input', function () {
        let tipo = 'Do';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeDoF').on('input', function () {
        let tipo = 'Do';
        let rec = '';
        validarfec(tipo, rec);
        return false;
    });
    $('#timeInicioHeDo').on('input', function () {
        let tipo = 'Do';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    $('#timeFinHeDo').on('input', function () {
        let tipo = 'Do';
        let rec = '';
        calhe(tipo, rec);
        return false;
    });
    //Calcular Hora Extra No
    $('#timeFinHeNo').on('input', function () {
        let tipo = 'No';
        let rec = '';
        calhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeNoI').on('input', function () {
        let tipo = 'No';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeNoF').on('input', function () {
        let tipo = 'No';
        let rec = '';
        validarfec(tipo, rec);
        return false;
    });
    $('#timeInicioHeNo').on('input', function () {
        let tipo = 'No';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    //Calcular Hora Extra Recargo Nocturno
    $('#timeRecFinHeNo').on('input', function () {
        let tipo = 'No';
        let rec = 'Rec';
        calhe(tipo, rec);
        return false;
    });
    $('#datRecFecLabHeNoI').on('input', function () {
        let tipo = 'No';
        let rec = 'Rec';
        comhe(tipo, rec);
        return false;
    });
    $('#datRecFecLabHeNoF').on('input', function () {
        let tipo = 'No';
        let rec = 'Rec';
        validarfec(tipo, rec);
        return false;
    });
    $('#timeRecInicioHeNo').on('input', function () {
        let tipo = 'No';
        let rec = 'Rec';
        comhe(tipo, rec);
        return false;
    });
    //Calcular Hora Extra Dominical y festivo
    $('#timeFinHeDd').on('input', function () {
        let tipo = 'Dd';
        let rec = '';
        calhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeDdI').on('input', function () {
        let tipo = 'Dd';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeDdF').on('input', function () {
        let tipo = 'Dd';
        let rec = '';
        validarfec(tipo, rec);
        return false;
    });
    $('#timeInicioHeDd').on('input', function () {
        let tipo = 'Dd';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    //Calcular Hora Extra Recargo Dominical y festivo
    $('#timeRecFinHeDd').on('input', function () {
        let tipo = 'Dd';
        let rec = 'Rec';
        calhe(tipo, rec);
        return false;
    });
    $('#datRecFecLabHeDdF').on('input', function () {
        let tipo = 'Dd';
        let rec = 'Rec';
        comhe(tipo, rec);
        return false;
    });
    $('#datRecFecLabHeDdF').on('input', function () {
        let tipo = 'Dd';
        let rec = 'Rec';
        validarfec(tipo, rec);
        return false;
    });
    $('#timeRecInicioHeDd').on('input', function () {
        let tipo = 'Dd';
        let rec = 'Rec';
        comhe(tipo, rec);
        return false;
    });
    //Calcular Hora Extra Nd
    $('#timeFinHeNd').on('input', function () {
        let tipo = 'Nd';
        let rec = '';
        calhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeNdI').on('input', function () {
        let tipo = 'Nd';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeNdF').on('input', function () {
        let tipo = 'Nd';
        let rec = '';
        validarfec(tipo, rec);
        return false;
    });
    $('#timeInicioHeNd').on('input', function () {
        let tipo = 'Nd';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    //Calcular Hora Extra Hd
    $('#timeFinHeHd').on('input', function () {
        let tipo = 'Hd';
        let rec = '';
        calhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeHdI').on('input', function () {
        let tipo = 'Hd';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    $('#datFecLabHeHdF').on('input', function () {
        let tipo = 'Hd';
        let rec = '';
        validarfec(tipo, rec);
        return false;
    });
    $('#timeInicioHeHd').on('input', function () {
        let tipo = 'Hd';
        let rec = '';
        comhe(tipo, rec);
        return false;
    });
    //Calcular actualizar
    var calheup = function () {
        let fli = $('#datFecLabHeIup').val();
        let flf = $('#datFecLabHeFup').val();
        let hei = $('#timeInicioHeup').val();
        let hef = $('#timeFinHeup').val();
        let tip = "";
        $.ajax({
            type: 'POST',
            url: window.urlin + '/nomina/extras/horas/registrar/cal_he.php',
            data: { fli: fli, flf: flf, hei: hei, hef: hef, tip: tip },
            success: function (r) {
                if (r === '0') {
                    $('#timeFinHeup').val('');
                    $('#etimeFinHeup').show();
                    setTimeout(function () {
                        $('#etimeFinHeup').fadeOut(600);
                    }, 800);
                    return false;
                } else {
                    $('#CantHeup').html(r);
                }
            }
        });
    };
    $('#datFecLabHeIup').on('input', function () {
        let fli = $('#datFecLabHeIup').val();
        let flf = $('#datFecLabHeFup').val();
        if (fli > flf) {
            $('#datFecLabHeIup').val('');
            $('#edatFecLabHeIup').show();
            setTimeout(function () {
                $('#edatFecLabHeIup').fadeOut(600);
            }, 800);
            return false;
        }
        calheup();
        return false;
    });
    $('#datFecLabHeFup').on('input', function () {
        let fli = $('#datFecLabHeIup').val();
        let flf = $('#datFecLabHeFup').val();
        if (fli > flf) {
            $('#datFecLabHeFup').val('');
            $('#edatFecLabHeFup').show();
            setTimeout(function () {
                $('#edatFecLabHeFup').fadeOut(600);
            }, 800);
            return false;
        }
        calheup();
        return false;
    });
    $('#timeInicioHeup').on('input', function () {
        calheup();
        return false;
    });
    $('#timeFinHeup').on('input', function () {
        calheup();
        return false;
    });
    var rowdel = function () {
        $(document).ready(function () {
            var table = $('.table').DataTable();
            table.row(window.rowdel).remove().draw();
        });
    };
    var reloadtable = function (nom) {
        $(document).ready(function () {
            var table = $('#' + nom).DataTable();
            table.ajax.reload();
        });
    };
    var setIdioma = {
        "decimal": "",
        "emptyTable": "No hay información",
        "info": "Mostrando _START_ - _END_ registros de _TOTAL_ ",
        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
        "infoFiltered": "(Filtrado de _MAX_ entradas en total )",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "Ver _MENU_ Filas",
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
    $(document).ready(function () {
        //dataTable listar empleados resolucion de viáticos.
        $('#tableListaResolucionesViaticos').DataTable({
            dom: setdom,
            buttons: [{
                action: function (e, dt, node, config) {
                    //Registar Responsabilidad Economica desde Detalles
                    $.post("datos/registrar/formadd_resoluciones_viaticos.php", function (he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-2x');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                        $('#slcRespEcon').focus();
                    });
                }
            }],
            language: setIdioma,
            "ajax": {
                url: 'datos/listar/resoluciones_viaticos.php',
                type: 'POST',
                dataType: 'json',
            },
            "columns": [
                { 'data': 'grupo' },
                { 'data': 'no_resolucion' },
                { 'data': 'id_cdp' },
                { 'data': 'no_documento' },
                { 'data': 'nombre' },
                { 'data': 'fec_inicia' },
                { 'data': 'fec_final' },
                { 'data': 'tot_dias' },
                { 'data': 'dias_pernocta' },
                { 'data': 'objetivo' },
                { 'data': 'destino' },
                { 'data': 'botones' },
            ],
            "order": [
                [0, "asc"]
            ]
        });
        $('#tableListaResolucionesViaticos').wrap('<div class="overflow" />');
    });
    //bajar formato horas extra
    $('#formHoEx').on('click', function () {
        $('<form action="../exportar/formato_hoex.php" method="post"><input type="hidden" name="horext" value="" /></form>').appendTo('body').submit();
    });
    //cargar horas extra con excel
    $('#subirHoex').on('click', function () {
        $.post("../datos/cargar/carga_horas_extra.php", function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-lg');
            //$('#divTamModalForms').addClass('modal-sm');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    //agregar  horas extra desde excel
    $('#divModalForms').on('click', '#btnAddHoraExs', function () {
        if ($('#fileDocHoEx').val() === '') {
            $('#divModalError').modal('show');
            $('#divMsgError').html('¡Debe elegir un archivo!');
        } else {
            let archivo = $('#fileDocHoEx').val();
            let ext = archivo.substring(archivo.lastIndexOf(".")).toLowerCase();
            if (ext !== '.xlsx') {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Solo se permite documentos .xlsx!');
                return false;
            } else if ($('#fileDocHoEx')[0].files[0].size > 2097152) {
                $('#divModalError').modal('show');
                $('#divMsgError').html('¡Documento debe tener un tamaño menor a 2Mb!');
                return false;
            }
            let datos = new FormData();
            datos.append('fileDocHoEx', $('#fileDocHoEx')[0].files[0]);
            $('#btnAddHoraExs').attr('disabled', true);
            $('#btnAddHoraExs').html('<i class="fas fa-spinner fa-pulse"></i> Cargando...');
            $.ajax({
                type: 'POST',
                url: 'cargar_horas.php',
                contentType: false,
                data: datos,
                processData: false,
                cache: false,
                success: function (r) {
                    $('#btnAddHoraExs').attr('disabled', false);
                    $('#btnAddHoraExs').html('Agregar');
                    if (r == '1') {
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Horas Extra Cargadas Correctamente');
                    } else {
                        $('#divModalForms').modal('hide');
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $('#divModalForms').on('click', '#btnGenerarResolucion', function () {
        var b = 1;
        $('input[type=checkbox]:checked').each(function () {
            $('input').each(function () {
                $(this).removeClass('border-danger');
            });
            let idcheck = $(this).val();
            if ($('#fec_inicia_' + idcheck).val() == '') {
                $('#fec_inicia_' + idcheck).addClass('border-danger');
                b = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Campo requerido');
            } else if ($('#fec_final_' + idcheck).val() == '') {
                $('#fec_final_' + idcheck).addClass('border-danger');
                b = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Campo requerido');
            } else if ($('#fec_inicia_' + idcheck).val() > $('#fec_final_' + idcheck).val()) {
                $('#fec_inicia_' + idcheck).addClass('border-danger');
                b = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Fecha inicial no puede ser mayor a fecha final');
            } else if ($('#tot_dias_' + idcheck).val() == '' || parseInt($('#tot_dias_' + idcheck).val()) <= 0) {
                $('#tot_dias_' + idcheck).addClass('border-danger');
                b = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Campo requerido');
            } else if ($('#dias_pernocta_' + idcheck).val() == '' || parseInt($('#dias_pernocta_' + idcheck).val()) < 0) {
                $('#dias_pernocta_' + idcheck).addClass('border-danger');
                b = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Campo requerido');
            } else if (parseInt($('#tot_dias_' + idcheck).val()) < parseInt($('#dias_pernocta_' + idcheck).val())) {
                $('#dias_pernocta_' + idcheck).addClass('border-danger');
                b = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Dias de pernocta no puede ser mayor a dias totales');
            } else if ($('#objetivo_' + idcheck).val() == '') {
                $('#objetivo_' + idcheck).addClass('border-danger');
                b = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Campo requerido');
            } else if ($('#destino_' + idcheck).val() == '') {
                $('#destino_' + idcheck).addClass('border-danger');
                b = 0;
                $('#divModalError').modal('show');
                $('#divMsgError').html('Campo requerido');
            }
            if (b == 0) {
                return false;
            }

        });
        if (b == 1) {
            let datos = $('#formDatosResoluciones').serialize();
            $.ajax({
                type: 'POST',
                url: 'registrar/add_resoluciones.php',
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableListaResolucionesViaticos';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Resolución(es) Generada(s) Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
    });
    $('#divModalForms').on('change', '#selectAll', function () {
        if ($(this).prop('checked')) {
            $('#selectAll').attr('title', 'Desmarcar todos');
        } else {
            $('#selectAll').attr('title', 'Marcar todos');
        }

        $('.listado > input[type=checkbox]').prop('checked', $(this).is(':checked'));
    });
    $('#modificarResolucionViatics').on('click', '.editar', function () {
        let id = $(this).attr('value');
        $.post("datos/actualizar/formup_resoluciones_viaticos.php", { id: id }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').removeClass('modal-2x');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });
    $('#divModalForms').on('click', '#btnActualizaResolucion', function () {
        $('input').each(function () {
            $(this).removeClass('border-danger');
        });
        if ($('#fec_inicia').val() == '') {
            $('#fec_inicia').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Campo requerido');
        } else if ($('#fec_final').val() == '') {
            $('#fec_final').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Campo requerido');
        } else if ($('#fec_inicia').val() > $('#fec_final').val()) {
            $('#fec_inicia').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Fecha inicial no puede ser mayor a fecha final');
        } else if ($('#tot_dias').val() == '' || parseInt($('#tot_dias').val()) <= 0) {
            $('#tot_dias').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Campo requerido');
        } else if ($('#dias_pernocta').val() == '' || parseInt($('#dias_pernocta').val()) < 0) {
            $('#dias_pernocta').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Campo requerido');
        } else if (parseInt($('#tot_dias').val()) < parseInt($('#dias_pernocta').val())) {
            $('#dias_pernocta').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Dias de pernocta no puede ser mayor a dias totales');
        } else if ($('#objetivo').val() == '') {
            $('#objetivo').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Campo requerido');
        } else if ($('#destino').val() == '') {
            $('#destino').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Campo requerido');
        } else {
            let datos = $('#formUpResolucionViaticos').serialize();
            $.ajax({
                type: 'POST',
                url: 'actualizar/up_resoluciones.php',
                data: datos,
                success: function (r) {
                    if (r == '1') {
                        let id = 'tableListaResolucionesViaticos';
                        reloadtable(id);
                        $('#divModalForms').modal('hide');
                        $('#divModalDone').modal('show');
                        $('#divMsgDone').html('Resolución Actualizada Correctamente');
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;

    });
    $('#modificarResolucionViatics').on('click', '.borrar', function () {
        let id = $(this).attr('value');
        let tip = 'ResolucionViaticos';
        confdel(id, tip);
    });
    $("#divBtnsModalDel").on('click', '#btnConfirDelResolucionViaticos', function () {
        let id = $(this).attr('value');
        $('#divModalConfDel').modal('hide');
        $.ajax({
            type: 'POST',
            url: 'eliminar/del_resolucion_viatico.php',
            data: { id: id },
            success: function (r) {
                if (r == '1') {
                    let id = 'tableListaResolucionesViaticos';
                    reloadtable(id);
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Resolución de Viáticos Eliminada Correctamente");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r);
                }
            }
        });
        return false;
    });
    $('#modificarResolucionViatics').on('click', '.descargar', function () {
        let id = $(this).attr('value');
        $('<form action="datos/soporte/resolucion_viaticos.php" method="post"><input type="hidden" name="id" value="' + id + '" /></form>').appendTo('body').submit();
    });
    $('#btnAWordxGrupo').on('click', function () {
        if ($('#numGrupoResols').val() == '' || parseInt($('#numGrupoResols').val()) <= 0) {
            $('#numGrupoResols').addClass('border-danger');
            $('#divModalError').modal('show');
            $('#divMsgError').html('Debe ingresar un número de grupo válido');
        } else {
            $('#numGrupoResols').removeClass('border-danger');
            let grupo = $('#numGrupoResols').val();
            $.ajax({
                type: 'POST',
                url: 'datos/listar/validar_grupo.php',
                data: { grupo: grupo },
                success: function (r) {
                    if (r == '1') {
                        $('<form action="datos/soporte/resoluciones_viaticos.php" method="post"><input type="hidden" name="grupo" value="' + grupo + '" /></form>').appendTo('body').submit();
                    } else {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html(r);
                    }
                }
            });
        }
        return false;
    });
    $('#dataTable').on('click', '.editHE', function () {
        let data = $(this).attr('value').split('|');
        let idUpHe = data[0];
        let idMesHe = data[1];
        $('<form action="actualizar/listuphoraex.php" method="post"><input type="hidden" name="idUpHe" value="' + idUpHe + '" /><input type="hidden" name="idMesHe" value="' + idMesHe + '" /></form>').appendTo('body').submit();

    });
    $('#dataTable').on('click', '.editarHEespecifca', function () {
        let data = $(this).attr('value').split('|');
        let idUpHoexs = data[0];
        let valmesreghe = data[1];
        let validreghe = data[2];
        $('<form action="formuphoex.php" method="post"><input type="hidden" name="idUpHoexs" value="' + idUpHoexs + '" /><input type="hidden" name="valmesreghe" value="' + valmesreghe + '" /><input type="hidden" name="validreghe" value="' + validreghe + '" /></form>').appendTo('body').submit();
    });
})(jQuery);