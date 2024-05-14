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
                url: 'listar_existencias_lote.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_sede = $('#sl_sede_filtro').val();
                    data.id_bodega = $('#sl_bodega_filtro').val();
                    data.codigo = $('#txt_codigo_filtro').val();
                    data.nombre = $('#txt_nombre_filtro').val();
                    data.id_subgrupo = $('#sl_subgrupo_filtro').val();
                    data.artactivo = $('#chk_artact_filtro').is(':checked') ? 1 : 0;
                    data.lotactivo = $('#chk_lotact_filtro').is(':checked') ? 1 : 0;
                    data.conexistencia = $('#chk_conexi_filtro').is(':checked') ? 1 : 0;
                }
            },
            columns: [
                { 'data': 'id_lote' }, //Index=0
                { 'data': 'nom_sede' },
                { 'data': 'nom_bodega' },
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'nom_subgrupo' },
                { 'data': 'lote' },
                { 'data': 'existencia' },
                { 'data': 'val_promedio' },
                { 'data': 'val_total' },
                { 'data': 'fec_vencimiento' },
                { 'data': 'estado' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [4] },
                { orderable: false, targets: [0, 12] }
            ],
            order: [
                [4, "ASC"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_lotes').wrap('<div class="overflow"/>');
    });

    //Buascar registros de Articulos
    $('#sl_sede_filtro').on("change", function() {
        $('#sl_bodega_filtro').load('../common/cargar_bodegas_usuario.php', { id_sede: $(this).val(), titulo: '--Bodega--' }, function() {});
    });
    $('#sl_sede_filtro').trigger('change');

    //Buascar registros de Articulos
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_lotes');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_lotes');
        }
    });

    //Examinar una tarjeta kardex
    $('#tb_lotes').on('click', '.btn_examinar', function() {
        let id = $(this).attr('value');
        $.post("frm_kardex_lote.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    /* ---------------------------------------------------
    TARJETA KARDEX
    -----------------------------------------------------*/
    $('#divForms').on('click', '#btn_buscar_fil_kar', function() {
        reloadtable('tb_kardex');
    });

    /* ---------------------------------------------------
    IMPRESORA
    -----------------------------------------------------*/
    //Imprimir listado de registros
    $('#btn_imprime_filtro').on('click', function() {
        reloadtable('tb_lotes');
        $('.is-invalid').removeClass('is-invalid');
        $.post("imp_existencias_lote.php", {
            id_sede: $('#sl_sede_filtro').val(),
            id_bodega: $('#sl_bodega_filtro').val(),
            codigo: $('#txt_codigo_filtro').val(),
            nombre: $('#txt_nombre_filtro').val(),
            id_subgrupo: $('#sl_subgrupo_filtro').val(),
            artactivo: $('#chk_artact_filtro').is(':checked') ? 1 : 0,
            lotactivo: $('#chk_lotact_filtro').is(':checked') ? 1 : 0,
            conexistencia: $('#chk_conexi_filtro').is(':checked') ? 1 : 0
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

    //Imprimit una Tarjeta Kardex
    $('#divForms').on("click", "#btn_imprimir", function() {
        reloadtable('tb_kardex');
        $.post("imp_kardex_lote.php", {
            id_lote: $('#id_lote').val(),
            fec_ini: $('#txt_fecini_fil').val(),
            fec_fin: $('#txt_fecfin_fil').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);