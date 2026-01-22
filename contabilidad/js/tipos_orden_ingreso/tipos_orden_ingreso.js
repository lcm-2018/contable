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
        $('#tb_tipos_orden_ingreso').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_tipos_orden_ingreso.php", function(he) {
                        $('#divTamModalForms').removeClass('modal-sm');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-xl');
                        $('#divModalForms').modal('show');
                        $("#divForms").html(he);
                    });
                }
            }],
            language: setIdioma,
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: 'listar_tipos_orden_ingreso.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.nombre = $('#txt_nombre_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_tipo_ingreso' }, //Index=0
                { 'data': 'nom_tipo_ingreso' },
                { 'data': 'es_int_ext' },
                { 'data': 'orden_compra' },
                { 'data': 'fianza' },
                { 'data': 'almacen' },
                { 'data': 'farmacia' },                
                { 'data': 'activofijo' },
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: [1] },
                { orderable: false, targets: 8 }
            ],
            order: [
                [0, "desc"]
            ],
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'TODO'],
            ],
        });

        $('.bttn-plus-dt span').html('<span class="icon-dt fas fa-plus-circle fa-lg"></span>');
        $('#tb_tipos_orden_ingreso').wrap('<div class="overflow"/>');
    });

    //Buascar registros
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_tipos_orden_ingreso');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_tipos_orden_ingreso');
        }
    });

    //Editar un registro    
    $('#tb_tipos_orden_ingreso').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_tipos_orden_ingreso.php", { id: id }, function(he) {
            $('#divTamModalForms').removeClass('modal-lg');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-xl');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro 
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_nom_tipoingreso'));
        error += verifica_vacio($('#sl_esintext'));
        error += verifica_vacio($('#sl_ordencompra'));
        error += verifica_vacio($('#sl_fianza'));
        error += verifica_vacio($('#sl_farmacia'));
        error += verifica_vacio($('#sl_almacen'));
        error += verifica_vacio($('#sl_activofijo'));

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_tipos_orden_ingreso').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_tipos_orden_ingreso.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_tipo_ingreso').val() == -1) ? 0 : $('#tb_tipos_orden_ingreso').DataTable().page.info().page;
                    reloadtable('tb_tipos_orden_ingreso', pag);
                    $('#id_tipo_ingreso').val(r.id);
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

    //Borrarr un registro 
    $('#tb_tipos_orden_ingreso').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('tipos_orden_ingreso', id);
    });

    $('#divModalConfDel').on("click", "#tipos_orden_ingreso", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_tipos_orden_ingreso.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_tipos_orden_ingreso').DataTable().page.info().page;
                reloadtable('tb_tipos_orden_ingreso', pag);
                $('#divModalDone').modal('show');
                $('#divMsgDone').html("Proceso realizado con éxito");
            } else {
                $('#divModalError').modal('show');
                $('#divMsgError').html(r.mensaje);
            }
        }).always(function() {}).fail(function() {
            alert('Ocurrió un error');
        });
    });

    //Imprimir registros
    $('#btn_imprime_filtro').on('click', function() {
        reloadtable('tb_tipos_orden_ingreso');
        $.post("imp_tipos_orden_ingreso.php", {
            nombre: $('#txt_nombre_filtro').val()
        }, function(he) {
            $('#divTamModalImp').removeClass('modal-sm');
            $('#divTamModalImp').removeClass('modal-lg');
            $('#divTamModalImp').addClass('modal-xl');
            $('#divModalImp').modal('show');
            $("#divImp").html(he);
        });
    });

})(jQuery);