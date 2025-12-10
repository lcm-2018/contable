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
$sql = "WITH
            cdp_por_rubro AS (
            SELECT
                p.id_pto_cdp,
                p.id_rubro,
                SUM(p.valor) AS valorcdp,
                SUM(IFNULL(p.valor_liberado,0)) AS cdpliberado
            FROM pto_cdp_detalle p
            WHERE p.id_pto_cdp = $id_cdp
            GROUP BY p.id_pto_cdp, p.id_rubro
            ),
            crp_por_cdp_det AS (
            SELECT
                pcd.id_pto_cdp_det,
                SUM(pcd.valor) AS valorcrp,
                SUM(IFNULL(pcd.valor_liberado,0)) AS crpliberado
            FROM pto_crp_detalle pcd
            JOIN pto_crp pc ON pcd.id_pto_crp = pc.id_pto_crp
            WHERE pc.estado = 2
            GROUP BY pcd.id_pto_cdp_det
            ),
            crp_por_rubro AS (
            SELECT
                pcd.id_rubro,
                SUM(IFNULL(crp.valorcrp,0))     AS valorcrp,
                SUM(IFNULL(crp.crpliberado,0))  AS crpliberado
            FROM (
                SELECT id_pto_cdp_det, id_rubro
                FROM pto_cdp_detalle
                WHERE id_pto_cdp = $id_cdp
            ) pcd
            LEFT JOIN crp_por_cdp_det crp ON crp.id_pto_cdp_det = pcd.id_pto_cdp_det
            GROUP BY pcd.id_rubro
            )
            SELECT
            c.id_pto_cdp,
            c.id_rubro,
            pc.cod_pptal,
            c.valorcdp,
            c.cdpliberado,
            IFNULL(r.valorcrp,0)    AS valorcrp,
            IFNULL(r.crpliberado,0) AS crpliberado,
            ((c.valorcdp - c.cdpliberado) - (IFNULL(r.valorcrp,0) - IFNULL(r.crpliberado,0))) AS saldo_final,
            GREATEST(0, ((c.valorcdp - c.cdpliberado) - (IFNULL(r.valorcrp,0) - IFNULL(r.crpliberado,0)))) AS puede_liberar
            FROM cdp_por_rubro c
            LEFT JOIN crp_por_rubro r ON r.id_rubro = c.id_rubro
            LEFT JOIN pto_cargue pc ON pc.id_cargue = c.id_rubro
            ORDER BY c.id_rubro";

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
        <a type="button" class="btn btn-primary btn-sm" onclick="RegLiberacionCdp()">Liberar</a>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>