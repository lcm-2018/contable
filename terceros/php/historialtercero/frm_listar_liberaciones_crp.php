<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : -1;
$otro_form = isset($_POST['otro_form']) ? $_POST['otro_form'] : 0;

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE LIBERACIONES A CRP</h5>
        </div>
        <div class="px-2">
            <form id="frm_liberacionescrp">
                <input type="hidden" id="id_crp" name="id_crp" value="<?php echo $id_crp ?>">
                <div class=" w-100 text-left">
                    <table id="tb_liberacionescrp" class="table table-striped table-bordered table-sm nowrap table-hover shadow w-100" style="width:100%; font-size:80%">
                        <thead>
                            <tr class="text-center centro-vertical">
                                <th>Id Lib</th>
                                <th>Fecha</th>
                                <th style="min-width: 50%;">Concepto</th>
                                <th>Valor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-left centro-vertical" id="body_tb_liberacionescrp"></tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script>
    (function($) {
        $(document).ready(function() {
            $('#tb_liberacionescrp').DataTable({
                language: setIdioma,
                processing: true,
                serverSide: true,
                searching: false,
                autoWidth: false,
                ajax: {
                    url: window.urlin + '/terceros/php/historialtercero/listar_liberaciones_crp.php',
                    type: 'POST',
                    dataType: 'json',
                    data: function(data) {
                        data.id_crp = $('#id_crp').val();
                    }
                },
                columns: [{ 'data': 'id_pto_crp_det' },
                          { 'data': 'fecha' },
                          { 'data': 'concepto_libera' },
                          { 'data': 'valor_liberado' },
                          { 'data': 'botones' },
                ],
                columnDefs: [{
                        class: 'text-wrap',
                        targets: [2]
                    } 
                ],
                order: [
                    [2, "asc"]
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'TODO'],
                ]
            });
            $('#tb_liberacionescrp').wrap('<div class="overflow"/>');
        });
    })(jQuery);
</script>