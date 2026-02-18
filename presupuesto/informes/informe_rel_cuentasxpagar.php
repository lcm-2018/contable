<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../conexion.php';
include 'funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$fec_ini = isset($_POST['fecha_ini']) && strlen($_POST['fecha_ini'] > 0) ? "'" . $_POST['fecha_ini'] . "'" : '2020-01-01';
$fec_fin = isset($_POST['fecha_corte']) && strlen($_POST['fecha_corte']) > 0 ? "'" . $_POST['fecha_corte'] . "'" : '2050-12-31';

try {
    //----- relacion de compromisos y cuentas por pagar -----------------------
    $sql = "SELECT
                DATE_FORMAT(pto_crp.fecha,'%Y-%m-%d') AS fecha,
                pto_cdp.id_manu AS id_manu_cdp,
                pto_crp.id_manu AS id_manu_crp,
                pto_crp.num_contrato,
                tb_terceros.id_tercero_api,
                tb_terceros.nit_tercero,
                tb_terceros.nom_tercero,
                pto_crp.objeto,
                pto_cargue.cod_pptal,
                pto_cargue.nom_rubro,
                pto_cdp.id_pto_cdp,
                SUM(pto_crp_detalle2.valor) AS valor_crp,
                SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS valor_liberado_crp,
                (SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0))) AS a_crp_menos_crpliberado,
                IFNULL(cop_sum.b_valor_cop_detalle, 0) AS b_valor_cop_detalle,
                IFNULL(pag_sum.c_valor_pag_detalle, 0) AS c_valor_pag_detalle,
                (SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) - IFNULL(cop_sum.b_valor_cop_detalle, 0)) AS a_menos_b,
                (IFNULL(cop_sum.b_valor_cop_detalle, 0) - IFNULL(pag_sum.c_valor_pag_detalle, 0)) AS b_menos_c
            FROM pto_cdp
            INNER JOIN (SELECT id_pto_cdp, SUM(valor) AS valor, SUM(valor_liberado) AS valor_liberado, id_rubro FROM pto_cdp_detalle GROUP BY id_pto_cdp) AS pto_cdp_detalle2 ON (pto_cdp_detalle2.id_pto_cdp = pto_cdp.id_pto_cdp)
            INNER JOIN pto_crp ON (pto_crp.id_cdp = pto_cdp.id_pto_cdp)
            INNER JOIN (SELECT id_pto_crp, SUM(valor) AS valor, SUM(valor_liberado) AS valor_liberado, id_pto_crp_det FROM pto_crp_detalle GROUP BY id_pto_crp) AS pto_crp_detalle2 ON (pto_crp_detalle2.id_pto_crp = pto_crp.id_pto_crp)  
            INNER JOIN tb_terceros ON (pto_crp.id_tercero_api = tb_terceros.id_tercero_api)   
            INNER JOIN pto_cargue ON (pto_cdp_detalle2.id_rubro = pto_cargue.id_cargue)
            LEFT JOIN (
                SELECT pto_crp_detalle2.id_pto_crp, SUM(pto_cop_detalle.valor) AS b_valor_cop_detalle
                FROM (SELECT id_pto_crp, id_pto_crp_det FROM pto_crp_detalle GROUP BY id_pto_crp, id_pto_crp_det) pto_crp_detalle2
                INNER JOIN pto_cop_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle2.id_pto_crp_det)
                GROUP BY pto_crp_detalle2.id_pto_crp
            ) cop_sum ON cop_sum.id_pto_crp = pto_crp.id_pto_crp
            LEFT JOIN (
                SELECT pto_crp_detalle2.id_pto_crp, SUM(pto_pag_detalle.valor) AS c_valor_pag_detalle
                FROM (SELECT id_pto_crp, id_pto_crp_det FROM pto_crp_detalle GROUP BY id_pto_crp, id_pto_crp_det) pto_crp_detalle2
                INNER JOIN pto_cop_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle2.id_pto_crp_det)
                INNER JOIN pto_pag_detalle ON (pto_pag_detalle.id_pto_cop_det = pto_cop_detalle.id_pto_cop_det)
                INNER JOIN ctb_doc ON (ctb_doc.id_ctb_doc = pto_pag_detalle.id_ctb_doc)
                WHERE ctb_doc.estado = 2
                GROUP BY pto_crp_detalle2.id_pto_crp
            ) pag_sum ON pag_sum.id_pto_crp = pto_crp.id_pto_crp
            where pto_crp.estado = 2
            and DATE_FORMAT(pto_crp.fecha,'%Y-%m-%d') between $fec_ini and $fec_fin 
            GROUP BY pto_cdp.id_pto_cdp
            order by tb_terceros.nom_tercero";

    $rs = $cmd->query($sql);
    $obj_informe = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$nom_informe = "RELACION DE COMPROMISOS Y CUENTAS POR PAGAR";
include_once 'encabezado_empresa.php';

?>
<table style="width:100% !important; border:#A9A9A9 1px solid;">
    <thead style="font-size:70%; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
            <th style="border:#A9A9A9 1px solid;">Fecha</th>
            <th style="border:#A9A9A9 1px solid;">No CDP</th>
            <th style="border:#A9A9A9 1px solid;">No CRP</th>
            <th style="border:#A9A9A9 1px solid;">No Contrato</th>
            <th style="border:#A9A9A9 1px solid;" colspan="2">Tercero</th>
            <th style="border:#A9A9A9 1px solid;">CC/Nit</th>
            <th style="border:#A9A9A9 1px solid;" colspan="2">Detalle</th>
            <th style="border:#A9A9A9 1px solid;">Rubro</th>
            <th style="border:#A9A9A9 1px solid;">Val. Registrado</th>
            <!--<th style="border:#A9A9A9 1px solid;">Val. Liberado</th>-->
            <th style="border:#A9A9A9 1px solid;">Val. Causado</th>
            <th style="border:#A9A9A9 1px solid;">Val. Pagado</th>
            <th style="border:#A9A9A9 1px solid;">Compromiso x pagar</th>
            <th style="border:#A9A9A9 1px solid;">Cuentas x pagar</th>
        </tr>
    </thead>
    <tbody style="font-size: 70%;">
        <?php
        foreach ($obj_informe as $obj) { ?>
            <tr class="resaltar">
                <td style="border:#A9A9A9 1px solid;"><?php echo $obj['fecha'] ?></td>
                <td style="border:#A9A9A9 1px solid;"><?php echo $obj['id_manu_cdp'] ?></td>
                <td style="border:#A9A9A9 1px solid;"><?php echo $obj['id_manu_crp'] ?></td>
                <td style="border:#A9A9A9 1px solid; text-align:left;"> <?php echo mb_strtoupper($obj['num_contrato']) ?> </td>
                <td style="border:#A9A9A9 1px solid; text-align:left;" colspan="2"><?php echo mb_strtoupper($obj['nom_tercero']) ?></td>
                <td style="border:#A9A9A9 1px solid;"><?php echo $obj['nit_tercero'] ?></td>
                <td style="border:#A9A9A9 1px solid;" colspan="2"><?php echo $obj['objeto'] ?></td>
                <td style="border:#A9A9A9 1px solid; text-align:left"><?php echo $obj['cod_pptal'] ?></td>
                <td style="border:#A9A9A9 1px solid; text-align:right"><?php echo $obj['a_crp_menos_crpliberado'] ?></td>
                <!--<td style="border:#A9A9A9 1px solid; text-align:right"><?php echo $obj['valor_liberado_crp'] ?></td>-->
                <td style="border:#A9A9A9 1px solid; text-align:right"><?php echo $obj['b_valor_cop_detalle'] ?></td>
                <td style="border:#A9A9A9 1px solid; text-align:right"><?php echo $obj['c_valor_pag_detalle'] ?></td>
                <td style="border:#A9A9A9 1px solid; text-align:right"><?php echo $obj['a_menos_b'] ?></td>
                <td style="border:#A9A9A9 1px solid; text-align:right"><?php echo $obj['b_menos_c'] ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>
</div>