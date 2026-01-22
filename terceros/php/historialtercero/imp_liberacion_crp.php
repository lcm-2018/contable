<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include 'funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_lib = isset($_POST['id_lib']) && strlen($_POST['id_lib']) > 0 ? $_POST['id_lib'] : -1;
$id_crp = isset($_POST['id_crp']) && strlen($_POST['id_crp']) > 0 ? $_POST['id_crp'] : -1;

try {

    //----datos liberacion----------------
    $sql = "SELECT 
                DATE_FORMAT(fecha_libera, '%Y-%m-%d') AS fecha
                , concepto_libera
                , valor_liberado
            FROM
                pto_crp_detalle
            WHERE
                id_pto_crp_det = $id_lib";
    $rs = $cmd->query($sql);
    $obj_liberacion = $rs->fetch();

    //-----crps-----------------------
    $sql = "SELECT
                pto_crp.id_pto_crp
                , pto_crp.id_manu AS manucrp
                , pto_crp.num_contrato
                , tb_terceros.nit_tercero
                , tb_terceros.nom_tercero
                , pto_cdp.id_manu AS manucdp
            FROM
                pto_crp_detalle
                INNER JOIN pto_crp ON (pto_crp_detalle.id_pto_crp = pto_crp.id_pto_crp)
                INNER JOIN tb_terceros ON (tb_terceros.id_tercero_api = pto_crp.id_tercero_api)
                INNER JOIN pto_cdp ON (pto_crp.id_cdp = pto_cdp.id_pto_cdp)
            WHERE pto_crp.id_pto_crp = $id_crp
            LIMIT 1";

    $rs = $cmd->query($sql);
    $obj_crps = $rs->fetch(); // un solo registro de cabecera del CRP

    //------ codigos ppto cargue con id_rubro (AQUÍ ESTABA EL PROBLEMA)
    $sql = "SELECT
                pto_crp.id_pto_crp
                , pto_cdp_detalle.id_pto_cdp_det
                , pto_cargue.cod_pptal 
                , pto_cargue.nom_rubro
                , SUM(IFNULL(pto_crp_detalle2.valor,0)) AS vr_crp
                , SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS vr_crp_liberado
                , SUM(IFNULL(pto_cop_detalle.valor,0)) AS vr_cop
                , SUM(IFNULL(pto_cop_detalle.valor_liberado,0)) AS vr_cop_liberado
                , (SUM(IFNULL(pto_crp_detalle2.valor,0)) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)))
                  - (SUM(IFNULL(pto_cop_detalle.valor,0)) - SUM(IFNULL(pto_cop_detalle.valor_liberado,0))) AS saldo_final
            FROM
                (
                    SELECT 
                        id_pto_crp,
                        id_pto_crp_det,
                        id_pto_cdp_det,
                        SUM(valor) AS valor,
                        SUM(IFNULL(valor_liberado,0)) AS valor_liberado
                    FROM pto_crp_detalle
                    GROUP BY id_pto_crp, id_pto_crp_det, id_pto_cdp_det
                ) AS pto_crp_detalle2
            LEFT JOIN pto_cop_detalle 
                ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle2.id_pto_crp_det)
            INNER JOIN pto_cdp_detalle 
                ON (pto_crp_detalle2.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
            INNER JOIN pto_crp 
                ON (pto_crp_detalle2.id_pto_crp = pto_crp.id_pto_crp)
            INNER JOIN pto_cargue 
                ON (pto_cdp_detalle.id_rubro = pto_cargue.id_cargue)
            WHERE 
                pto_crp_detalle2.id_pto_crp = $id_crp
            GROUP BY 
                pto_crp.id_pto_crp,
                pto_cdp_detalle.id_pto_cdp_det,
                pto_cargue.cod_pptal,
                pto_cargue.nom_rubro
            ORDER BY 
                pto_cdp_detalle.id_pto_cdp_det";

    $rs = $cmd->query($sql);
    // AHORA SÍ: todos los rubros del CRP
    $obj_codigo = $rs->fetchAll(PDO::FETCH_ASSOC);

    //----datos usuario----------------
    $sql = "SELECT 
                CONCAT(nombre1,' ',nombre2,' ',apellido1,' ',apellido2) AS nombre
                , descripcion
            FROM seg_usuarios_sistema
            WHERE login = '" . $_SESSION['login'] . "'";
    $rs = $cmd->query($sql);
    $obj_usuario = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="text-right py-3">
    <a type="button" class="btn btn-primary btn-sm" id="btnImprimir">Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="content bg-light" id="areaImprimir">
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
            }
        }

        .resaltar:nth-child(even) {
            background-color: #F8F9F9;
        }

        .resaltar:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>

    <?php include('reporte_header.php'); ?>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>GESTION DE RECURSOS FINANCIEROS</th>
        </tr>
        <tr style="text-align:center">
            <th>NOTA PRESUPUESTAL GASTOS</th>
        </tr>
    </table>

    <table style="width:100%; font-size:80%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="border:#A9A9A9 1px solid">
            <td>Fecha nota</td>
            <td colspan="4"><?php echo $obj_liberacion['fecha']; ?></td>
        </tr>
        <tr style="border:#A9A9A9 1px solid">
            <td>No. Identificacion</td>
            <td colspan="4"><?php echo $obj_crps['nit_tercero']; ?></td>
        </tr>
        <tr style="border:#A9A9A9 1px solid">
            <td>Tercero</td>
            <td colspan="4"><?php echo strtoupper($obj_crps['nom_tercero']); ?></td>
        </tr>
        <tr style="border:#A9A9A9 1px solid">
            <td>No. Documento</td>
            <td colspan="4"><?php echo strtoupper($obj_crps['num_contrato']); ?></td>
        </tr>
        <tr style="border:#A9A9A9 1px solid">
            <td>Detalle</td>
            <td colspan="4"><?php echo strtoupper($obj_liberacion['concepto_libera']); ?></td>
        </tr>
    </table>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th></th>
        </tr>
    </table>

    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;" colspan="6">IMPUTACION PRESUPUESTAL</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">CDP</th>
                <th style="border:#A9A9A9 1px solid;">RP</th>
                <th style="border:#A9A9A9 1px solid;" colspan="3">Rubro</th>
                <th style="border:#A9A9A9 1px solid;">Valor Nota</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <?php foreach ($obj_codigo as $codigo): ?>
                <tr style="text-align:left; border:#A9A9A9 1px solid;">
                    <td style="border:#A9A9A9 1px solid;"><?php echo $obj_crps['manucdp']; ?></td>
                    <td style="border:#A9A9A9 1px solid;"><?php echo $obj_crps['manucrp']; ?></td>
                    <th style="border:#A9A9A9 1px solid;" colspan="3">
                        <?php echo $codigo['cod_pptal'] . ' - ' . $codigo['nom_rubro']; ?>
                    </th>
                    <th style="text-align:right; border:#A9A9A9 1px solid;">
                        <?php echo formato_valor($codigo['vr_crp_liberado']); ?>
                    </th>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="text-align: left; padding-top: 70px; font-size: 13px;">
        <div>___________________________________</div>
        <div><?= $obj_usuario['nombre']; ?> </div>
        <div><?= $obj_usuario['descripcion']; ?> </div>
    </div>
</div>