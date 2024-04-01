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
        $('#tb_dependencias').DataTable({
            dom: setdom,
            buttons: [{
                action: function(e, dt, node, config) {
                    $.post("frm_reg_dependencias.php", function(he) {
                        $('#divTamModalForms').removeClass('modal-xl');
                        $('#divTamModalForms').removeClass('modal-lg');
                        $('#divTamModalForms').addClass('modal-sm');
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
                url: 'listar_dependencias.php',
                type: 'POST',
                dataType: 'json',
                data: function(data) {
                    data.nombre = $('#txt_nombre_filtro').val();
                }
            },
            columns: [
                { 'data': 'id_dependencia' }, //Index=0              
                { 'data': 'nom_dependencia' },          
                { 'data': 'botones' }
            ],
            columnDefs: [
                { class: 'text-wrap', targets: 1 },
                { orderable: false, targets: 2 }
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
        $('#tb_dependencias').wrap('<div class="overflow"/>');
    });

    //Buascar registros
    $('#btn_buscar_filtro').on("click", function() {
        reloadtable('tb_dependencias');
    });

    $('.filtro').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_dependencias');
        }
    });

    //Editar un registro    
    $('#tb_dependencias').on('click', '.btn_editar', function() {
        let id = $(this).attr('value');
        $.post("frm_reg_dependencias.php", { id: id }, function(he) {
            $('#divTamModalForms').addClass('modal-sm');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    //Guardar registro 
    $('#divForms').on("click", "#btn_guardar", function() {
        $('.is-invalid').removeClass('is-invalid');
        var error = verifica_vacio($('#txt_nom_dependencia'));     

        if (error >= 1) {
            $('#divModalError').modal('show');
            $('#divMsgError').html('Los datos resaltados son obligatorios');
        } else {
            var data = $('#frm_reg_dependencias').serialize();
            $.ajax({
                type: 'POST',
                url: 'editar_dependencias.php',
                dataType: 'json',
                data: data + "&oper=add"
            }).done(function(r) {
                if (r.mensaje == 'ok') {
                    let pag = ($('#id_dependencia').val() == -1) ? 0 : $('#tb_dependencias').DataTable().page.info().page;
                    reloadtable('tb_dependencias', pag);
                    $('#id_dependencia').val(r.id);
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

    //Borrar un registro 
    $('#tb_dependencias').on('click', '.btn_eliminar', function() {
        let id = $(this).attr('value');
        confirmar_del('dependencias', id);
    });

    $('#divModalConfDel').on("click", "#dependencias", function() {
        var id = $(this).attr('value');
        $.ajax({
            type: 'POST',
            url: 'editar_dependencias.php',
            dataType: 'json',
            data: { id: id, oper: 'del' }
        }).done(function(r) {
            $('#divModalConfDel').modal('hide');
            if (r.mensaje == 'ok') {
                let pag = $('#tb_dependencias').DataTable().page.info().page;
                reloadtable('tb_dependencias', pag);
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

    $('#btnImprimeDependecias').on('click', function () {
        let mes = 2;      
        $.post("imp_dependencias.php", { mes: mes }, function (he) {
            $('#divTamModalForms').removeClass('modal-xl');
            $('#divTamModalForms').removeClass('modal-sm');
            $('#divTamModalForms').addClass('modal-lg');
            $('#divModalForms').modal('show');
            $("#divForms").html(he);
        });
    });

    $('#divModalForms').on('click', '#btnImprimir', function () {
        function imprSelec() {
            var div = $('#areaImprimir').html();
            var ventimp = window.open(' ', '');
            ventimp.document.write('<!DOCTYPE html><html><head><title>Imprimir</title></head><body>');
            ventimp.document.write('<div>' + div + '</div>');
            ventimp.document.write('</body></html>');
            ventimp.print();
            ventimp.close();
        }
        $('#divModalForms .collapse').addClass('show');
        imprSelec();
    });

    $('#divModalForms').on('click', '#btnExcelEntrada', function () {
        let xls = ($('#areaImprimir').html());
        var encoded = window.btoa(xls);
        $('<form action="reporte_excel.php" method="post"><input type="hidden" name="xls" value="' + encoded + '" /></form>').appendTo('body').submit();
    });



})(jQuery);