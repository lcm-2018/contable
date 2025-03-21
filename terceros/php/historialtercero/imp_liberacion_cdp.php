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
$id_cdp = isset($_POST['id_cdp']) && strlen($_POST['id_cdp']) > 0 ? $_POST['id_cdp'] : -1;

try {

    //----datos liberacion----------------
    $sql = "SELECT 
                id_rubro
                , DATE_FORMAT(fecha_libera, '%Y-%m-%d') AS fecha
                , concepto_libera
                , valor_liberado
            FROM
                pto_cdp_detalle
            WHERE
                id_pto_cdp_det=$id_lib";
    $rs = $cmd->query($sql);
    $obj_liberacion = $rs->fetch();

    //-----cdps-----------------------
    $sql = "SELECT
             pto_cdp.id_manu
            , pto_cdp.id_pto_cdp
            , DATE_FORMAT(pto_cdp.fecha, '%Y-%m-%d') AS fecha
            , pto_cdp.objeto
            , SUM(pto_cdp_detalle.valor) AS valor_cdp   
            , SUM(IFNULL(pto_cdp_detalle.valor_liberado,0)) AS valor_cdp_liberado   
            , SUM(pto_crp_detalle.valor) AS valor_crp
            , SUM(IFNULL(pto_crp_detalle.valor_liberado,0)) AS valor_crp_liberado
            , (SUM(pto_cdp_detalle.valor) - SUM(IFNULL(pto_cdp_detalle.valor_liberado,0))) - (SUM(pto_crp_detalle.valor) - SUM(IFNULL(pto_crp_detalle.valor_liberado,0))) AS saldo
        FROM
            pto_cdp_detalle 
            INNER JOIN pto_cdp ON (pto_cdp_detalle.id_pto_cdp = pto_cdp.id_pto_cdp)
            INNER JOIN pto_crp_detalle ON (pto_cdp_detalle.id_pto_cdp_det=pto_crp_detalle.id_pto_cdp_det)    
            INNER JOIN pto_crp ON (pto_crp_detalle.id_pto_crp = pto_crp.id_pto_crp)  
        WHERE pto_cdp.id_pto_cdp = $id_cdp ";

    $rs = $cmd->query($sql);
    // $obj_codigo = $rs->fetchAll(); esto trae varios registros
    $obj_cdps = $rs->fetch(); // esto trae un solo registro

    //------ codigos ppto cargue con id_rubro
    $sql = "SELECT
                pto_cdp_detalle2.id_pto_cdp
                , pto_cdp_detalle2.id_rubro
                , pto_cargue.cod_pptal
                , pto_cargue.nom_rubro
                , pto_cdp_detalle2.id_pto_cdp_det
                ,SUM(pto_cdp_detalle2.valor) AS valorcdp
                ,SUM(IFNULL(pto_cdp_detalle2.valor_liberado,0)) AS cdpliberado
                ,SUM(pto_crp_detalle2.valor) AS valorcrp
                ,SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)) AS crpliberado
                ,((SUM(pto_cdp_detalle2.valor) - SUM(IFNULL(pto_cdp_detalle2.valor_liberado,0))) - (SUM(pto_crp_detalle2.valor) - SUM(IFNULL(pto_crp_detalle2.valor_liberado,0)))) AS saldo_final
            FROM
                pto_cdp
                INNER JOIN (SELECT id_pto_cdp,id_rubro,id_pto_cdp_det,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_cdp_detalle GROUP BY id_pto_cdp) AS pto_cdp_detalle2 ON (pto_cdp_detalle2.id_pto_cdp = pto_cdp.id_pto_cdp)
		        INNER JOIN pto_crp ON (pto_crp.id_cdp = pto_cdp.id_pto_cdp)
                INNER JOIN (SELECT id_pto_crp,SUM(valor) AS valor,SUM(valor_liberado) AS valor_liberado FROM pto_crp_detalle GROUP BY id_pto_crp) AS pto_crp_detalle2 ON (pto_crp_detalle2.id_pto_crp = pto_crp.id_pto_crp)  
                INNER JOIN pto_cargue ON (pto_cdp_detalle2.id_rubro = pto_cargue.id_cargue)
            WHERE pto_cdp_detalle2.id_pto_cdp = $id_cdp 
            GROUP BY pto_cdp.id_pto_cdp ";

    $rs = $cmd->query($sql);
    $obj_codigo = $rs->fetch();

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
    <!--<a type="button" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exportar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>-->
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
            <th>NOTA PRESUPUESTAL CDP</th>
        </tr>
    </table>

    <table style="width:100%; font-size:80%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="border:#A9A9A9 1px solid">
            <td>Fecha nota</td>
            <td colspan="4"><?php echo $obj_liberacion['fecha']; ?></td>
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
                <th style="border:#A9A9A9 1px solid;" colspan="5">IMPUTACION PRESUPUESTAL</th>
            </tr>
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">CDP</th>
                <th style="border:#A9A9A9 1px solid;" colspan="3">Rubro</th>
                <th style="border:#A9A9A9 1px solid;">Valor Nota</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <tr style="text-align:left; border:#A9A9A9 1px solid;">
                <td style="border:#A9A9A9 1px solid;"><?php echo $obj_cdps['id_manu'] ?></td>
                <th style="border:#A9A9A9 1px solid;" colspan="3"><?php echo $obj_codigo['cod_pptal'] ?> - <?php echo $obj_codigo['nom_rubro']; ?></th>
                <th style="text-align:right; border:#A9A9A9 1px solid;"><?php echo formato_valor($obj_liberacion['valor_liberado']); ?></th>
            </tr>
        </tbody>
    </table>

    <div style="text-align: left; padding-top: 70px; font-size: 13px;">
        <div>___________________________________</div>
        <div><?= $obj_usuario['nombre']; ?> </div>
        <div><?= $obj_usuario['descripcion']; ?> </div>
    </div>
</div>