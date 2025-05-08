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
                date_format(pto_crp.fecha,'%Y-%m-%d') as fecha
                , pto_cdp.id_manu as id_manu_cdp
                , pto_crp.id_manu as id_manu_crp
                , pto_crp.num_contrato
                , tb_terceros.id_tercero_api
                , tb_terceros.nom_tercero
                , tb_terceros.nit_tercero
                , pto_crp.objeto
                , pto_cargue.cod_pptal
                , pto_cargue.nom_rubro
                , ifnull(pto_crp_detalle.valor,0) as valor_crp
                , sum(ifnull(pto_crp_detalle.valor_liberado,0)) as valor_liberado_crp
                , ((ifnull(pto_crp_detalle.valor,0))-(SUM(IFNULL(pto_crp_detalle.valor_liberado,0)))) as a_crp_menos_crpliberado
                , pto_cop_detalle.valor as b_valor_cop_detalle
                , sum(pto_pag_detalle.valor) as c_valor_pag_detalle
                , (((IFNULL(pto_crp_detalle.valor,0))-(SUM(IFNULL(pto_crp_detalle.valor_liberado,0))))-(pto_cop_detalle.valor)) as a_menos_b
                , ((pto_cop_detalle.valor)-(SUM(pto_pag_detalle.valor))) as b_menos_c
                
            FROM
                pto_crp
                INNER JOIN pto_cdp ON (pto_crp.id_cdp = pto_cdp.id_pto_cdp)
                inner join pto_crp_detalle on (pto_crp.id_pto_crp = pto_crp_detalle.id_pto_crp)
                INNER JOIN tb_terceros ON (pto_crp_detalle.id_tercero_api = tb_terceros.id_tercero_api)
                INNER JOIN pto_cdp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
                INNER JOIN pto_cargue ON (pto_cdp_detalle.id_rubro = pto_cargue.id_cargue) AND (pto_crp_detalle.id_pto_crp = pto_crp.id_pto_crp)
                INNER JOIN pto_cop_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle.id_pto_crp_det)
                INNER JOIN pto_pag_detalle ON (pto_pag_detalle.id_pto_cop_det = pto_cop_detalle.id_pto_cop_det)
            where pto_crp.estado = 2
            and DATE_FORMAT(pto_crp.fecha,'%Y-%m-%d') between $fec_ini and $fec_fin 
            GROUP BY pto_cdp.id_manu
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
