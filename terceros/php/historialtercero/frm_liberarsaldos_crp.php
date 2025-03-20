<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : -1;
$otro_form = isset($_POST['otro_form']) ? $_POST['otro_form'] : 0;

// se vuelve a consultar los datos del crp con el id que viene del boton
//------------------------------------
$sql = "SELECT
	     pto_crp.id_pto_crp
            ,pto_crp.id_manu
            ,DATE_FORMAT(pto_crp.fecha, '%Y-%m-%d') AS fecha                         
        FROM
            pto_crp                          
        WHERE pto_crp.id_pto_crp = $id_crp LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();
//----------------------------------------------------- hago la consulta aqui para que los saldos sean cajas de texto
// sino utilizo el script para llamar a listar_saldos.php
/*$sql = "SELECT
            COUNT(*) AS filas
            , pto_crp.id_pto_crp
            , pto_cdp_detalle.id_pto_cdp_det
            , pto_cargue.cod_pptal 
            , SUM(IFNULL(pto_crp_detalle2.valor,0)) AS vr_crp
            , SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS vr_crp_liberado
            , SUM(IFNULL(pto_cop_detalle.valor,0)) AS vr_cop
            , SUM(IFNULL(pto_cop_detalle.valor_liberado,0)) AS vr_cop_liberado
            ,(SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)))-(SUM(IFNULL(pto_cop_detalle.valor,0)) - SUM(IFNULL(pto_cop_detalle.valor_liberado,0))) AS saldo_final
        FROM
            pto_cop_detalle
            INNER JOIN (SELECT id_pto_crp,id_pto_crp_det,id_pto_cdp_det,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_crp_detalle GROUP BY id_pto_crp) AS pto_crp_detalle2 ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle2.id_pto_crp_det)
            INNER JOIN pto_cdp_detalle ON (pto_crp_detalle2.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
            INNER JOIN pto_crp ON (pto_crp_detalle2.id_pto_crp = pto_crp.id_pto_crp)
            INNER JOIN pto_cargue ON (pto_cdp_detalle.id_rubro = pto_cargue.id_cargue)

            WHERE pto_crp_detalle2.id_pto_crp = $id_crp
            GROUP BY pto_crp.id_cdp";

$rs = $cmd->query($sql);
$obj_saldos = $rs->fetchAll(); */

$sql = "SELECT
            COUNT(*) AS filas
            , pto_crp.id_pto_crp
            , pto_cdp_detalle.id_pto_cdp_det
            , pto_cargue.cod_pptal 
            , SUM(IFNULL(pto_crp_detalle2.valor,0)) AS vr_crp
            , SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS vr_crp_liberado
            , SUM(IFNULL(pto_cop_detalle.valor,0)) AS vr_cop
            , SUM(IFNULL(pto_cop_detalle.valor_liberado,0)) AS vr_cop_liberado
            ,(SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)))-(SUM(IFNULL(pto_cop_detalle.valor,0)) - SUM(IFNULL(pto_cop_detalle.valor_liberado,0))) AS saldo_final
        FROM
            (SELECT id_pto_crp,id_pto_crp_det,id_pto_cdp_det,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_crp_detalle GROUP BY id_pto_crp) AS pto_crp_detalle2
            LEFT JOIN pto_cop_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle2.id_pto_crp_det)
            INNER JOIN pto_cdp_detalle ON (pto_crp_detalle2.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
            INNER JOIN pto_crp ON (pto_crp_detalle2.id_pto_crp = pto_crp.id_pto_crp)
            INNER JOIN pto_cargue ON (pto_cdp_detalle.id_rubro = pto_cargue.id_cargue)

            WHERE pto_crp_detalle2.id_pto_crp = $id_crp
            GROUP BY pto_crp.id_cdp";

$rs = $cmd->query($sql);
$obj_saldos = $rs->fetchAll();

//---------------------------------------------------
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LIBERACION DE SALDOS CRP</h5>
        </div>
        <div class="px-2">
            <form id="frm_liberarsaldos_crp">
                <input type="hidden" id="id_crp" name="id_crp" value="<?php echo $id_crp ?>">
                <div class=" form-row">
                    <div class="form-group col-md-3">
                        <label for="txt_num_crp" class="small">NUMERO CRP</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="text" class="filtro form-control form-control-sm" id="txt_num_crp" name="txt_num_crp" readonly="true" value="<?php echo $obj['id_manu'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_crp" class="small">FECHA CRP</label>
                    </div>
                    <div class="form_group col-md-9">
                        <input type="text" class="filtro form-control form-control-sm" id="txt_fec_crp" name="txt_fec_crp" readonly="true" value="<?php echo $obj['fecha'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_lib_crp" class="small">FECHA LIBERACION</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="date" class="form-control form-control-sm" id="txt_fec_lib_crp" name="txt_fec_lib_crp" placeholder="Fecha liberacion" value="<?php echo date('Y-m-d') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_concepto_lib_crp" class="small">CONCEPTO LIBERACION</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control form-control-sm" id="txt_concepto_lib_crp" name="txt_concepto_lib_crp" placeholder="Concepto liberacion">
                    </div>
                </div>

                <div class=" w-100 text-left">
                    <table id="tb_saldos_crp" class="table table-striped table-bordered table-sm nowrap table-hover shadow w-100" style="width:100%; font-size:80%">
                        <thead>
                            <tr class="text-center centro-vertical">
                                <th>Id cdp det</th>
                                <th style="min-width: 50%;">Codigo</th>
                                <th>Valor</th>
                                <th>Valor a liberar</th>
                            </tr>
                        </thead>
                        <tbody class="text-left centro-vertical" id="body_tb_saldos_crp"></tbody>
                        <?php
                        foreach ($obj_saldos as $dll) {
                        ?>
                            <tr>
                                <td class="border" colspan="1">
                                    <input type="text" name="txt_id_rubro_crp[]" class="form-control form-control-sm bg-plain" value="<?php echo $dll['id_pto_cdp_det'] ?>" readonly="true">
                                </td>
                                <td class="border" colspan="1">
                                    <input type="text" name="txt_codigo_crp[]" class="form-control form-control-sm  bg-plain" value="<?php echo $dll['cod_pptal'] ?>" readonly="true">
                                </td>
                                <td class="border" colspan="1">
                                    <input type="text" name="txt_valor_crp[]" class="form-control form-control-sm bg-plain" value="<?php echo $dll['saldo_final'] ?>" readonly="true">
                                </td>
                                <td class="border" colspan="1">
                                    <input type="text" name="txt_valor_liberar_crp[]" class="form-control form-control-sm valfno bg-plain" value="<?php echo $dll['saldo_final'] ?>">
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
        <button type="button" class="btn btn-primary btn-sm" id="btn_liquidar_saldos_crp">Liberar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>