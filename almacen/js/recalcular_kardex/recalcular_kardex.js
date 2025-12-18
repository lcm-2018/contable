(function($) {
    $(document).on('show.bs.modal', '.modal', function() {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });

    $(document).ready(function() {
        //Tabla de Registros
        $('#tb_lotes').DataTable({
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: 'listar_lotes.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_sede = $('#sl_sede_filtro').val();
                    data.id_bodega = $('#sl_bodega_filtro').val();
                    data.codigo = $('#txt_codigo_filtro').val();
                    data.nombre = $('#txt_nombre_filtro').val();
                    data.fecini = $('#txt_fecha_filtro').val();
                    data.id_ing = $('#txt_id_ing_filtro').val();
                    data.id_egr = $('#txt_id_egr_filtro').val();
                    data.id_tra = $('#txt_id_tra_filtro').val();
                    data.id_egr_r = $('#txt_id_egr_r_filtro').val();
                    data.opcion = $("input[name='rdo_opcion']:checked").val();
                    data.selfil = $('#chk_sel_filtro').is(':checked') ? 1 : 0;
                }
            },
            columns: [
                { 'data': 'select' },
                { 'data': 'id_med' }, //Index=1
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'nom_sede' },
                { 'data': 'nom_bodega' },
                { 'data': 'id_lote' },
                { 'data': 'lote' },
                { 'data': 'existencia_lote' },
                { 'data': 'cod_medicamento' },
                { 'data': 'existencia' },
                { 'data': 'val_promedio' },
            ],
            columnDefs: [
                { orderable: false, targets: [0] },
                { class: 'text-wrap', targets: [3, 4, 5] }
            ],
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });
        $('#tb_lotes').wrap('<div class="overflow"/>');
    });

    //Buascar registros de Articulos
    $('#sl_sede_filtro').on("change", function() {
        $('#sl_bodega_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega--' }, function() {});
    });
    $('#sl_sede_filtro').trigger('change');

    $('.chk_aplica').change(function() {
        $('#txt_codigo_filtro').prop('disabled', true);
        $('#txt_nombre_filtro').prop('disabled', true);
        $('#txt_fecha_filtro').prop('disabled', true);
        $('#txt_id_ing_filtro').prop('disabled', true);
        $('#txt_id_egr_filtro').prop('disabled', true);
        $('#txt_id_tra_filtro').prop('disabled', true);
        $('#txt_id_egr_r_filtro').prop('disabled', true);
        if ($("input[name='rdo_opcion']:checked").val() == 'O') {
            $('#txt_codigo_filtro').prop('disabled', false);
            $('#txt_nombre_filtro').prop('disabled', false);
            $('#txt_fecha_filtro').prop('disabled', false);
            $('#txt_codigo_filtro').focus();
        } else if ($("input[name='rdo_opcion']:checked").val() == 'I') {
            $('#txt_id_ing_filtro').prop('disabled', false);
            $('#txt_id_ing_filtro').focus();
        } else if ($("input[name='rdo_opcion']:checked").val() == 'E') {
            $('#txt_id_egr_filtro').prop('disabled', false);
            $('#txt_id_egr_filtro').focus();
        } else if ($("input[name='rdo_opcion']:checked").val() == 'T') {
            $('#txt_id_tra_filtro').prop('disabled', false);
            $('#txt_id_tra_filtro').focus();
        } else if ($("input[name='rdo_opcion']:checked").val() == 'ER') {
            $('#txt_id_egr_r_filtro').prop('disabled', false);
            $('#txt_id_egr_r_filtro').focus();
        }
    });

    function buscar_articulo() {
        $('.is-invalid').removeClass('is-invalid');
        var error = 0;
        if ($("input[name='rdo_opcion']:checked").val() == 'O') {
            error += verifica_vacio($('#txt_codigo_filtro'));
            error += verifica_vacio($('#txt_nombre_filtro'));
            if (error == 1) {
                $('.is-invalid').removeClass('is-invalid');
                error = 0;
            }
            error += verifica_vacio($('#txt_fecha_filtro'));
        } else if ($("input[name='rdo_opcion']:checked").val() == 'I') {
            error += verifica_vacio($('#txt_id_ing_filtro'));
        } else if ($("input[name='rdo_opcion']:checked").val() == 'E') {
            error += verifica_vacio($('#txt_id_egr_filtro'));
        } else if ($("input[name='rdo_opcion']:checked").val() == 'T') {
            error += verifica_vacio($('#txt_id_tra_filtro'));
        } else if ($("input[name='rdo_opcion']:checked").val() == 'ER') {
            error += verifica_vacio($('#txt_id_egr_r_filtro'));
        }

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            reloadtable('tb_lotes');
        }
    }

    $('#btn_buscar_filtro').on("click", function() {
        buscar_articulo();
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            buscar_articulo();
        }
    });

    $('#chk_sel_filtro').change(function() {
        buscar_articulo();
    });

    // Recalcular los Registros de Articulos seleccionados
    $('#btn_recalcular_filtro').on("click", function() {
        let filas = $('#tb_lotes').DataTable().rows().count();
        if (filas == 0) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('No hay registros para reclacular kardex');
        } else {            
            $('#divModalEspera').modal('show');

            var data = $('#frm_lotes').serialize();
            var tipo = $("input[name='rdo_opcion']:checked").val(),
                id_ing = $("#txt_id_ing_filtro").val(),
                id_egr = $("#txt_id_egr_filtro").val(),
                id_tra = $("#txt_id_tra_filtro").val(),
                fec_ini = $("#txt_fecha_filtro").val(),
                id_egr_r = $("#txt_id_egr_r_filtro").val();

            $.ajax({
                type: 'POST',
                url: 'procesar.php',
                dataType: 'json',
                data: data + '&tipo=' + tipo + '&id_ing=' + id_ing + '&id_egr=' + id_egr + '&id_tra=' + id_tra + '&id_egr_r=' + id_egr_r + '&fec_ini=' + fec_ini
            }).done(function(r) {                
                if (r.mensaje == 'ok') {
                    $('#chk_sel_filtro').prop('checked', false)
                    reloadtable('tb_lotes');
                    $('#divModalDone').modal('show');
                    $('#divMsgDone').html("Proceso realizado con éxito");
                } else {
                    $('#divModalError').modal('show');
                    $('#divMsgError').html(r.mensaje);
                }                  
                setTimeout(function() { $('#divModalEspera').modal('hide'); }, 1000);          
            }).always(function() {}).fail(function() {
                $('#divModalEspera').modal('hide');
                alert('Ocurrió un error');
            });
        }
    });
})(jQuery);