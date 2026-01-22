(function($) {
    $(document).ready(function() {
        $('#tb_egresos_detalles').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    let id_egreso = $('#id_egreso').val();
                    let table = $('#tb_egresos_detalles').DataTable();
                    let filas = table.rows().count();
                    let es_conpedido = $('#sl_tip_egr').find('option:selected').attr('data-conpedido');
                    let es_devfianza = $('#sl_tip_egr').find('option:selected').attr('data-devfianza');
                    let id_pedido = $('#txt_id_pedido').val();
                    let id_ingreso = $('#txt_id_ingreso').val();

                    if (id_egreso == -1 || es_conpedido == 1 && filas == 0 && id_pedido || es_devfianza == 1 && filas == 0 && id_ingreso) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Primero debe guardar la Orden de Egreso');
                    } else if (es_conpedido == 1 && !id_pedido) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Debe seleccionar un Número de Pedido');
                    } else if (es_devfianza == 1 && !id_ingreso) {
                        $('#divModalError').modal('show');
                        $('#divMsgError').html('Debe seleccionar un Número de Orden de Ingreso Fianza');
                    } else {
                        $.post("../common/buscar_lotes_frm.php", {
                            id_sede: $('#sl_sede_egr').val(),
                            id_bodega: $('#sl_bodega_egr').val(),
                            id_subgrupo: sessionStorage.getItem("id_subgrupo")
                        }, function(he) {
                            $('#divTamModalBus').removeClass('modal-lg');
                            $('#divTamModalBus').removeClass('modal-sm');
                            $('#divTamModalBus').addClass('modal-xl');
                            $('#divModalBus').modal('show');
                            $("#divFormsBus").html(he);
                        });
                    }
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: 'listar_egresos_detalles.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.id_egreso = $('#id_egreso').val();
                }
            },
            columns: [
                { 'data': 'id_egr_detalle' }, //Index=0
                { 'data': 'cod_medicamento' },
                { 'data': 'nom_medicamento' },
                { 'data': 'lote' },
                { 'data': 'existencia' },
                { 'data': 'fec_vencimiento' },
                { 'data': 'cantidad' },
                { 'data': 'valor' },
                { 'data': 'val_total' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 2 },
                { orderable: false, targets: 9 }
            ],
            order: [
                [0, "asc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        }).on('draw', function() {
            let table = $('#tb_egresos_detalles').DataTable();
            let rows = table.rows({ filter: 'applied' }).count();
            let es_conpedido = $('#sl_tip_egr').find('option:selected').attr('data-conpedido');
            let es_devfianza = $('#sl_tip_egr').find('option:selected').attr('data-devfianza');
            if (rows > 0) {
                $('#sl_sede_egr').prop('disabled', true);
                $('#sl_bodega_egr').prop('disabled', true);
                $('#txt_des_pedido').prop('disabled', true);
                $('#btn_cancelar_pedido').prop('disabled', true);
                $('#txt_des_ingreso').prop('disabled', true);
                $('#btn_cancelar_ingreso').prop('disabled', true);
                $('#sl_tip_egr').prop('disabled', true);
            } else {
                $('#txt_des_pedido').prop('disabled', false);
                $('#btn_cancelar_pedido').prop('disabled', false);
                $('#txt_des_ingreso').prop('disabled', false);
                $('#btn_cancelar_ingreso').prop('disabled', false);
                if (es_conpedido != 1 && es_devfianza != 1 || es_conpedido == 1 && $('#txt_id_pedido').val() == '' || es_devfianza == 1 && $('#txt_id_ingreso').val() == '') {
                    $('#sl_sede_egr').prop('disabled', false);
                    $('#sl_bodega_egr').prop('disabled', false);
                    $('#sl_tip_egr').prop('disabled', false);
                }
            }
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_egreso_detalles').wrap('<div class="overflow"/>');
    });
})(jQuery);