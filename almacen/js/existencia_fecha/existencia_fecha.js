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
        $('#tb_articulos').DataTable({
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: 'listar_existencias_fecha.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_sede = $('#sl_sede_filtro').val();
                    data.id_bodega = $('#sl_bodega_filtro').val();
                    data.fecha = $('#txt_fecha_filtro').val();
                    data.codigo = $('#txt_codigo_filtro').val();
                    data.nombre = $('#txt_nombre_filtro').val();
                    data.id_subgrupo = $('#sl_subgrupo_filtro').val();
                    data.tipo_asis = $('#sl_tipoasis_filtro').val();
                    data.con_existencia = $('#sl_conexi_filtro').val();
                    data.lote_ven = $('#sl_lotven_filtro').val();
                    data.artactivo = $('#chk_artact_filtro').is(':checked') ? 1 : 0;
                    data.lotactivo = $('#chk_lotact_filtro').is(':checked') ? 1 : 0;
                }
            },
            columns: [
                { 'data': 'id_med' }, //Index=0
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'nom_subgrupo' },
                { 'data': 'existencia_fecha' },
                { 'data': 'val_promedio_fecha' },
                { 'data': 'val_total' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [2, 3] },
                { orderable: false, targets: [0] }
            ],
            order: [
                [2, "ASC"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_articulos').wrap('<div class="overflow"/>');
    });

    //Buascar registros de Articulos
    $('#sl_sede_filtro').on("change", function() {
        $('#sl_bodega_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega--' }, function() {});
    });
    $('#sl_sede_filtro').trigger('change');

    //Buascar registros de Articulos
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_articulos');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_articulos');
        }
    });

    /* ---------------------------------------------------
    IMPRESORA
    -----------------------------------------------------*/
    //Imprimir listado de registros
    $('#btn_imprime_filtro').on('click', function() {
        reloadtable('tb_articulos');
        $('.is-invalid').removeClass('is-invalid');

        let id_reporte = $('#sl_tipo_reporte').val();
        let reporte = "imp_existencias_fecha.php";

        switch (id_reporte) {
            case '1':
                reporte = "imp_existencias_fecha_asbsg_l.php";
                break;
            case '2':
                reporte = "imp_existencias_fecha_asbsg_a.php";
                break;
            case '3':
                reporte = "imp_existencias_fecha_asbsg_l.php";
                break;
            case '4':
                reporte = "imp_existencias_fecha_invfis.php";
                break;
        }

        $.post(reporte, {
            id_sede: $('#sl_sede_filtro').val(),
            id_bodega: $('#sl_bodega_filtro').val(),
            fecha: $('#txt_fecha_filtro').val(),
            codigo: $('#txt_codigo_filtro').val(),
            nombre: $('#txt_nombre_filtro').val(),
            id_subgrupo: $('#sl_subgrupo_filtro').val(),
            tipo_asis: $('#sl_tipoasis_filtro').val(),
            con_existencia: $('#sl_conexi_filtro').val(),
            lote_ven: $('#sl_lotven_filtro').val(),
            artactivo: $('#chk_artact_filtro').is(':checked') ? 1 : 0,
            lotactivo: $('#chk_lotact_filtro').is(':checked') ? 1 : 0,
            id_reporte: id_reporte
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);