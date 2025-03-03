(function ($) {
    $(document).ready(function() {
        //Tabla de sedes
        $('#tb_sedes').DataTable({
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: 'datos/listar/listar_sedes.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    /*data.id_sede = $('#sl_sede_filtro').val();
                    data.id_bodega = $('#sl_bodega_filtro').val();
                    data.opcion = $("input[name='rdo_opcion']:checked").val();
                    data.selfil = $('#chk_sel_filtro').is(':checked') ? 1 : 0;*/
                }
            },
            columns: [
                { 'data': 'select' },
                { 'data': 'id_sede' }, //Index=1
                { 'data': 'nom_sede' },
                { 'data': 'dir_sede' },
                { 'data': 'tel_sede' },
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
        $('#tb_sedes').wrap('<div class="overflow"/>');
    });
})(jQuery);