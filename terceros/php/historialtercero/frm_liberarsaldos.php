<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_cdp = isset($_POST['id_cdp']) ? $_POST['id_cdp'] : -1;
$otro_form = isset($_POST['otro_form']) ? $_POST['otro_form'] : 0;

// se vuelve a consultar los datos del cdp con el id que viene del boton
//------------------------------------
$sql = "SELECT
	        pto_cdp.id_pto_cdp
            ,pto_cdp.id_manu
            , DATE_FORMAT(pto_cdp.fecha, '%Y-%m-%d') AS fecha                         
        FROM
            pto_cdp                          
        WHERE pto_cdp.id_pto_cdp = $id_cdp LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();
//----------------------------------------------------- hago la consulta aqui para que los saldos sean cajas de texto
// sino utilizo el script para llamar a listar_saldos.php
$sql = "SELECT
                COUNT(*) AS filas
                ,pto_cdp_detalle2.id_pto_cdp
                ,pto_cdp_detalle2.id_rubro
                ,pto_cargue.cod_pptal
                ,pto_cdp_detalle2.id_pto_cdp_det
                ,SUM(pto_cdp_detalle2.valor) AS valorcdp
                ,SUM(pto_cdp_detalle2.valor_liberado) AS cdpliberado
                ,SUM(pto_crp_detalle.valor) AS valorcrp
                ,SUM(IFNULL(pto_crp_detalle.valor_liberado,0)) AS crpliberado
                ,((SUM(pto_cdp_detalle2.valor) - SUM(pto_cdp_detalle2.valor_liberado)) - (SUM(pto_crp_detalle.valor) - SUM(IFNULL(pto_crp_detalle.valor_liberado,0)))) AS saldo_final
            FROM
                pto_crp_detalle
                INNER JOIN (SELECT id_pto_cdp,id_rubro,id_pto_cdp_det,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_cdp_detalle GROUP BY id_pto_cdp) AS pto_cdp_detalle2 ON (pto_cdp_detalle2.id_pto_cdp_det = pto_crp_detalle.id_pto_cdp_det)
                INNER JOIN pto_cargue ON (pto_cdp_detalle2.id_rubro = pto_cargue.id_cargue)
            WHERE pto_cdp_detalle2.id_pto_cdp = $id_cdp
            GROUP BY pto_cdp_detalle2.id_pto_cdp,pto_cdp_detalle2.id_rubro ";

$rs = $cmd->query($sql);
$obj_saldos = $rs->fetchAll();

//---------------------------------------------------
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LIBERACION DE SALDOS</h5>
        </div>
        <div class="px-2">
            <form id="frm_liberarsaldos">
                <input type="hidden" id="id_cdp" name="id_cdp" value="<?php echo $id_cdp ?>">
                <div class=" form-row">
                    <div class="form-group col-md-3">
                        <label for="txt_num_cdp" class="small">NUMERO CDP</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="text" class="filtro form-control form-control-sm" id="txt_num_cdp" name="txt_num_cdp" readonly="true" value="<?php echo $obj['id_manu'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_cdp" class="small">FECHA CDP</label>
                    </div>
                    <div class="form_group col-md-9">
                        <input type="text" class="filtro form-control form-control-sm" id="txt_fec_cdp" name="txt_fec_cdp" readonly="true" value="<?php echo $obj['fecha'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_lib" class="small">FECHA LIBERACION</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="date" class="form-control form-control-sm" id="txt_fec_lib" name="txt_fec_lib" placeholder="Fecha liberacion" value="<?php echo date('Y-m-d') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_concepto_lib" class="small">CONCEPTO LIBERACION</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control form-control-sm" id="txt_concepto_lib" name="txt_concepto_lib" placeholder="Concepto liberacion">
                    </div>
                </div>

                <div class=" w-100 text-left">
                    <table id="tb_saldos" class="table table-striped table-bordered table-sm nowrap table-hover shadow w-100" style="width:100%; font-size:80%">
                        <thead>
                            <tr class="text-center centro-vertical">
                                <th>Id Rubro</th>
                                <th style="min-width: 50%;">Codigo</th>
                                <th>Valor</th>
                                <th>Valor a liberar</th>
                            </tr>
                        </thead>
                        <tbody class="text-left centro-vertical" id="body_tb_saldos"></tbody>
                        <?php
                        foreach ($obj_saldos as $dll) {
                        ?>
                            <tr>
                                <td class="border" colspan="1">
                                    <input type="text" name="txt_id_rubro[]" class="form-control form-control-sm bg-plain" value="<?php echo $dll['id_rubro'] ?>" readonly="true">
                                </td>
                                <td class="border" colspan="1">
                                    <input type="text" name="txt_codigo[]" class="form-control form-control-sm  bg-plain" value="<?php echo $dll['cod_pptal'] ?>" readonly="true">
                                </td>
                                <td class="border" colspan="1">
                                    <input type="text" name="txt_valor[]" class="form-control form-control-sm bg-plain" value="<?php echo $dll['saldo_final'] ?>" readonly="true">
                                </td>
                                <td class="border" colspan="1">
                                    <input type="text" name="txt_valor_liberar[]" class="form-control form-control-sm valfno bg-plain" value="<?php echo $dll['saldo_final'] ?>">
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_liquidar">Liberar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script>/*
    (function($) {
        $(document).ready(function() {
            $('#tb_saldos').DataTable({
                language: setIdioma,
                processing: true,
                serverSide: true,
                searching: false,
                autoWidth: false,
                ajax: {
                    url: window.urlin + '/terceros/php/historialtercero/listar_saldos.php',
                    type: 'POST',
                    dataType: 'json',
                    data: function(data) {
                        data.id_cdp = $('#id_cdp').val();
                    }
                },
                columns: [{
                        'data': 'id_rubro'
                    }, //Index=0
                    {
                        'data': 'cod_pptal'
                    },
                    {
                        'data': 'saldo_final'
                    },
                ],
                columnDefs: [{
                        class: 'text-wrap',
                        targets: []
                    } //,
                    //{ width: '5%', targets: [0,1,3,4] }
                ],
                order: [
                    [2, "asc"]
                ],
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'TODO'],
                ]
            });
            $('#tb_saldos').wrap('<div class="overflow"/>');
        });
    })(jQuery);

    //Buascar registros de articulos de Articulos
    /*
    $('#btn_buscar_articulo_fil').on("click", function() {
        reloadtable('tb_articulos_activos');
    });

    $('.filtro_art').keypress(function(e) {
        if (e.keyCode == 13) {
            reloadtable('tb_articulos_activos');
        }
    });

    $('.filtro_art').mouseup(function(e) {
        reloadtable('tb_articulos_activos');
    });

    $('#sl_subgrupo_art_fil').on("change", function() {
        sessionStorage.setItem("id_subgrupo", $(this).val());
    });*/
</script>