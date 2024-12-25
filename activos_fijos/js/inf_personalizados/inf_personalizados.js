(function($) {
    $(document).on('show.bs.modal', '.modal', function() {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });

    $(document).ready(function() {
        $('#tb_consultas').DataTable({
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: 'listar_consultas.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.nombre = $('#txt_nombre_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_consulta' }, //Index=0
                { 'data': 'nom_consulta' }
            ],
            order: [
                [1, "ASC"]
            ],
            lengthMenu: [
                [5, 10, 20, -1],
                [5, 10, 20, 'TODO'],
            ],
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_consultas').wrap('<div class="overflow"/>');
    });

    //Buascar registros
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_consultas');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_consultas');
        }
    });

    $('#tb_consultas').on('click', 'tr', function() {
        let id = $(this).find('td:eq(0)').text();
        if (id) {
            $.ajax({
                url: "parametros.php",
                dataType: "json",
                type: 'POST',
                data: { id: id }
            }).done(function(data) {
                $('#txt_id_consulta').val(data.id_consulta);
                $('#txt_nom_consulta').val(data.nom_consulta);
                $('#txt_des_consulta').val(data.des_consulta);
                $('#frm_parametros').html('');
                var parametros = JSON.parse(data.parametros),
                    i = 0,
                    str = '';
                if (parametros[0].label) {
                    for (i in parametros) {
                        str = '<label class="form-control-sm">&nbsp;' + parametros[i].label + '</label>';
                        str += '<input type="text" class="form-control-sm" title="' + parametros[i].title + '"/><br/>';
                        $('#frm_parametros').append(str);
                    }
                }
            });
        }
    });

    function Parametro(parametro, valor) {
        this.parametro = parametro;
        this.valor = valor;
    }

    $('#btn_buscar_consulta').on("click", function() {
        $('#dv_resultado').html('');
        $('.is-invalid').removeClass('is-invalid');

        var error = verifica_vacio($('#txt_nom_consulta'));
        error += verifica_vacio($('#txt_limite'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');

        } else if (!verifica_valmax($('#txt_limite'), 100, "La cantidad debe ser menor igual a 100")) {
            $('#divModalEspera').modal('show');
            var parametros = new Array(),
                i = 1,
                id = $('#txt_id_consulta').val(),
                limite = $('#txt_limite').val();

            $('#frm_parametros input:text').each(function() {
                var regExp1 = /^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1??1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]??[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0??2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02??)(-)(29)))$/;
                var regExp2 = /^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1??1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]??[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0??2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(28))|(([0-9][0-9][13579][26])(-)(02??)(-)(29)))$/;

                var val = $(this).val().trim();
                if (!val.match(regExp1) && !val.match(regExp2)) {
                    val = /[a-zA-Z]/.test(val) ? "\'" + val.replace(/,/g, "\',\'") + "\'" : val;
                }

                var parametro = new Parametro('P' + i, val);
                parametros[parametros.length] = parametro;
                i++;
            });

            $.ajax({
                url: 'ejecutar_consulta.php',
                type: 'POST',
                data: 'id=' + id + '&parametros=' + JSON.stringify(parametros) + '&limite=' + limite
            }).done(function(data) {
                $('#dv_resultado').html(data);
                $('#divModalEspera').fadeOut(0);
                setTimeout(function() { $('#divModalEspera').modal('hide'); }, 1000);
            }).fail(function() {
                alert('Ocurrió un error');
            });
        }
    });

    //Imprimir la consulta
    $('#btn_imprimir_consulta').on('click', function() {
        if ($('#txt_id_consulta').val()) {
            $('#divModalEspera').modal('show');
            var parametros = new Array(),
                i = 1,
                id = $('#txt_id_consulta').val(),
                limite = $('#txt_limite').val();

            $('#frm_parametros input:text').each(function() {
                var regExp1 = /^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1??1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]??[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0??2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02??)(-)(29)))$/;
                var regExp2 = /^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1??1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]??[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0??2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(28))|(([0-9][0-9][13579][26])(-)(02??)(-)(29)))$/;

                var val = $(this).val().trim();
                if (!val.match(regExp1) && !val.match(regExp2)) {
                    val = /[a-zA-Z]/.test(val) ? "\'" + val.replace(/,/g, "\',\'") + "\'" : val;
                }

                var parametro = new Parametro('P' + i, val);
                parametros[parametros.length] = parametro;
                i++;
            });

            $.post("imp_consulta.php", {
                id: id,
                parametros: JSON.stringify(parametros),
                limite: limite
            }, function(he) {
                $('#divModalEspera').fadeOut(0);
                setTimeout(function() { $('#divModalEspera').modal('hide'); }, 1000);
                $('#divTamModalImp').removeClass('modal-sm');
                $('#divTamModalImp').removeClass('modal-lg');
                $('#divTamModalImp').addClass('modal-xl');
                $('#divModalImp').modal('show');
                $("#divImp").html(he);
            });
        }
    });

    //Enviar archivo csv
    $('#btn_exportar_consulta').on('click', function() {
        if ($('#txt_id_consulta').val()) {
            $('#divModalEspera').modal('show');
            var parametros = new Array(),
                i = 1,
                id = $('#txt_id_consulta').val();

            $('#frm_parametros input:text').each(function() {
                var regExp1 = /^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1??1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]??[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0??2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02??)(-)(29)))$/;
                var regExp2 = /^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1??1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]??[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0??2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(28))|(([0-9][0-9][13579][26])(-)(02??)(-)(29)))$/;

                var val = $(this).val().trim();
                if (!val.match(regExp1) && !val.match(regExp2)) {
                    val = /[a-zA-Z]/.test(val) ? "\'" + val.replace(/,/g, "\',\'") + "\'" : val;
                }

                var parametro = new Parametro('P' + i, val);
                parametros[parametros.length] = parametro;
                i++;
            });

            $('#divModalEspera').show();
            $.ajax({
                url: 'exportar_consulta.php',
                type: 'POST',
                dataType: 'json',
                data: 'id=' + id + '&parametros=' + JSON.stringify(parametros)
            }).done(function(data) {
                $('#divModalEspera').fadeOut(0);
                if (data.mensaje == 'ok') {
                    $('#lbl_archivo').html('<a href="' + data.archivo + '">' + data.archivo + '</a>');
                    setTimeout(function() { $('#divModalEspera').modal('hide'); }, 1000);
                } else {
                    $('#lbl_archivo').html(data.mensaje);
                }
            }).fail(function() {
                alert('Ocurrió un error');
            });
        }
    });

    /* Editar una consulta */
    $(document).keydown(function(e) {
        if (e.keyCode == 113) {
            e.preventDefault();
            if ($('#txt_id_consulta').val()) {
                $.post("frm_reg_consulta.php", { id: $('#txt_id_consulta').val() }, function(he) {
                    $('#divTamModalForms').removeClass('modal-sm');
                    $('#divTamModalForms').removeClass('modal-lg');
                    $('#divTamModalForms').addClass('modal-xl');
                    $('#divModalForms').modal('show');
                    $("#divForms").html(he);
                });
            }
        }
    });

    //Guardar Consulta 
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_nom_con'));
        error += verifica_vacio($('#sl_opcion'));
        error += verifica_vacio($('#txt_des_con'));
        error += verifica_vacio($('#txt_con_sql'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_consulta').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_consulta.php',
                dataType: 'json',
                data: data
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = $('#tb_consultas').DataTable().page.info().page;
                    reloadtable('tb_consultas', pag);

                    $('#txt_nom_consulta').val(r.nom_consulta);
                    $('#txt_des_consulta').val(r.des_consulta);
                    $('#frm_parametros').html('');

                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Proceso realizado con éxito");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r.mensaje);
                }
            }).always(function() {}).fail(function() {
                alert('Ocurrió un error');
            });
        }
    });

})(jQuery);