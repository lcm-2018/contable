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

$id_tercero = isset($_POST['id_tercero']) ? $_POST['id_tercero'] : -1;
$id_cdp = isset($_POST['id_cdp']) && strlen($_POST['id_cdp'])>0 ? $_POST['id_cdp'] : -1;

try {

    //----datos tercero----------------
    $sql = "SELECT
                nit_tercero
                , nom_tercero
                , dir_tercero
                , tel_tercero
            FROM
                aguazul.tb_terceros
            WHERE
                id_tercero_api=$id_tercero";
    $rs = $cmd->query($sql);
    $obj_tercero = $rs->fetch();

    //-----cdps-----------------------
    $sql = "SELECT
            tb_terceros.id_tercero_api
            , tb_terceros.nit_tercero
            , tb_terceros.nom_tercero
            , pto_cdp.id_manu
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
            INNER JOIN tb_terceros ON (pto_crp.id_tercero_api = tb_terceros.id_tercero_api)
        WHERE tb_terceros.id_tercero_api = $id_tercero   
        GROUP BY pto_cdp.id_pto_cdp";

    $rs = $cmd->query($sql);
    $obj_cdps = $rs->fetchAll();

    //---------------contratos------------------------
    $sql = "SELECT
            ctt_novedad_liquidacion.id_liquidacion
            , ctt_contratos.num_contrato
            , DATE_FORMAT(ctt_contratos.fec_ini, '%Y-%m-%d') AS fec_ini
            , DATE_FORMAT(ctt_contratos.fec_fin, '%Y-%m-%d') AS fec_fin
            , ctt_contratos.val_contrato
            , ctt_novedad_adicion_prorroga.val_adicion
            , ctt_novedad_liquidacion.val_cte
            , CASE ctt_novedad_liquidacion.estado WHEN 1 THEN 'Liquidado' ELSE 'En ejecucion' END AS estado
        FROM
            ctt_contratos
            INNER JOIN ctt_adquisiciones ON (ctt_contratos.id_compra = ctt_adquisiciones.id_adquisicion)
            INNER JOIN pto_cdp ON (ctt_adquisiciones.id_cdp = pto_cdp.id_pto_cdp)
            LEFT JOIN ctt_novedad_adicion_prorroga ON (ctt_novedad_adicion_prorroga.id_adq = ctt_contratos.id_contrato_compra)
            LEFT JOIN ctt_novedad_liquidacion ON (ctt_novedad_liquidacion.id_adq = ctt_contratos.id_contrato_compra)
        WHERE ctt_adquisiciones.id_cdp = $id_cdp AND ctt_novedad_liquidacion.id_tipo_nov = 8
        ORDER BY ctt_novedad_liquidacion.id_liquidacion DESC LIMIT 1";

    $rs = $cmd->query($sql);
    $obj_contratos = $rs->fetchAll();

    //--------------registros presupuestales ---------------
    $sql = "SELECT
            pto_cdp.id_pto_cdp
            , DATE_FORMAT(pto_crp.fecha, '%Y-%m-%d') AS fecha
            , pto_crp.id_manu
            , 'CRP' AS tipo
            , pto_crp.objeto
            , pto_crp.num_contrato
            , pto_crp_detalle.valor - IFNULL(pto_crp_detalle.valor_liberado,0) AS vr_registro
            , (pto_cdp_detalle.valor - IFNULL(pto_cdp_detalle.valor_liberado,0))-(pto_crp_detalle.valor - IFNULL(pto_crp_detalle.valor_liberado,0)) AS vr_saldo
            , CASE pto_crp.estado WHEN 1 THEN 'Pendiente' WHEN 2 THEN 'Cerrado' WHEN 0 THEN 'Anulado' END AS estado
        FROM
            pto_cdp_detalle
            INNER JOIN pto_cdp ON (pto_cdp_detalle.id_pto_cdp = pto_cdp.id_pto_cdp)
            INNER JOIN pto_crp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
            INNER JOIN pto_crp ON (pto_crp_detalle.id_pto_crp = pto_crp.id_pto_crp)
        WHERE pto_cdp.id_pto_cdp = $id_cdp";

    $rs = $cmd->query($sql);
    $obj_regpresupuestal = $rs->fetchAll();

    //------obligaciones ---------------------------------
    $sql = "SELECT
            ctb_doc.id_manu
            , pto_cop_detalle.id_ctb_doc
            , pto_cdp_detalle.id_pto_cdp   
            , DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha
            , ctb_factura.num_doc     
            , pto_cop_detalle.valor-IFNULL(pto_cop_detalle.valor_liberado,0) AS valorcausado
            , SUM(IFNULL(ctb_causa_retencion.valor_retencion,0)) AS descuentos
            , SUM(IFNULL(pto_pag_detalle.valor,0)- IFNULL(pto_pag_detalle.valor_liberado,0)) AS neto       
            , CASE ctb_doc.estado WHEN 1 THEN 'Pendiente' WHEN 2 THEN 'Cerrado' WHEN 0 THEN 'Anulado' END AS estado
            , CASE WHEN ((pto_cop_detalle.valor-IFNULL(pto_cop_detalle.valor_liberado,0))-(SUM(IFNULL(ctb_causa_retencion.valor_retencion,0)))-(SUM(IFNULL(pto_pag_detalle.valor,0)- IFNULL(pto_pag_detalle.valor_liberado,0)))) = 0 THEN 'pagado' ELSE 'causado' END AS est
        FROM
            pto_crp_detalle
            INNER JOIN pto_cdp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
            INNER JOIN pto_cop_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle.id_pto_crp_det)
            INNER JOIN ctb_doc ON (pto_cop_detalle.id_ctb_doc = ctb_doc.id_ctb_doc)
            INNER JOIN ctb_factura ON (ctb_factura.id_ctb_doc = ctb_doc.id_ctb_doc)
            LEFT JOIN ctb_causa_retencion ON (ctb_causa_retencion.id_ctb_doc = ctb_doc.id_ctb_doc)
            LEFT JOIN pto_pag_detalle ON (pto_pag_detalle.id_ctb_doc = ctb_doc.id_ctb_doc)
        WHERE pto_cdp_detalle.id_pto_cdp = $id_cdp
        GROUP BY  ctb_doc.id_ctb_doc";

    $rs = $cmd->query($sql);
    $obj_obligaciones = $rs->fetchAll();

    //-----------------pagos-------------------------------------
    $sql = "SELECT
            pto_cdp_detalle.id_pto_cdp
            , ctb_doc.id_manu
            , DATE_FORMAT(ctb_doc.fecha, '%Y-%m-%d') AS fecha
            , ctb_doc.detalle
            , IFNULL(pto_pag_detalle.valor,0)-IFNULL(pto_pag_detalle.valor_liberado,0) AS valorpagado
        FROM
            pto_pag_detalle
            INNER JOIN ctb_doc ON (pto_pag_detalle.id_ctb_doc = ctb_doc.id_ctb_doc)
            INNER JOIN pto_cop_detalle ON (pto_pag_detalle.id_pto_cop_det = pto_cop_detalle.id_pto_cop_det)
            INNER JOIN pto_crp_detalle ON (pto_cop_detalle.id_pto_crp_det = pto_crp_detalle.id_pto_crp_det)
            INNER JOIN pto_cdp_detalle ON (pto_crp_detalle.id_pto_cdp_det = pto_cdp_detalle.id_pto_cdp_det)
        WHERE pto_cdp_detalle.id_pto_cdp  = $id_cdp";

    $rs = $cmd->query($sql);
    $obj_pagos = $rs->fetchAll();
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
            <th>HISTORIAL DE TERCEROS</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Nit tercero</td>
            <td colspan="2">Tercero</td>
            <td>Dirección</td>
            <td>Teléfono</td>
        </tr>
        <tr>
            <td><?php echo $obj_tercero['nit_tercero']; ?></td>
            <td colspan="2"><?php echo $obj_tercero['nom_tercero']; ?></td>
            <td><?php echo $obj_tercero['dir_tercero']; ?></td>
            <td><?php echo $obj_tercero['tel_tercero']; ?></td>
        </tr>
    </table>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>CDPs</th>
        </tr>
    </table>
    
    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">ID CDP</th>
                <th style="border:#A9A9A9 1px solid;">Documento</th>
                <th style="border:#A9A9A9 1px solid;">Fecha</th>
                <th style="border:#A9A9A9 1px solid;">Objeto</th>
                <th style="border:#A9A9A9 1px solid;">Valor CDP</th>
                <th style="border:#A9A9A9 1px solid;">Saldo</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <?php
            $tabla = '';
            foreach ($obj_cdps as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['id_pto_cdp'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['nit_tercero'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['fecha'] . '</td>
                        <td style="border:#A9A9A9 1px solid; text-align:left;">' . mb_strtoupper($obj['objeto']) . '</td>   
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['valor_cdp']) . '</td>   
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['saldo']) . '</td></tr>';   
            }            
            echo $tabla;
            ?>
        </tbody>
    </table>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>Contratos</th>
        </tr>
    </table>

    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">No Contrato</th>
                <th style="border:#A9A9A9 1px solid;">Fecha inicio</th>
                <th style="border:#A9A9A9 1px solid;">Fecha fin</th>
                <th style="border:#A9A9A9 1px solid;">Valor contrato</th>
                <th style="border:#A9A9A9 1px solid;">Adiciones</th>
                <th style="border:#A9A9A9 1px solid;">Reducciones</th>
                <th style="border:#A9A9A9 1px solid;">Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <?php
            $tabla = '';
            foreach ($obj_contratos as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['num_contrato'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['fec_ini'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['fec_fin'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['val_contrato']) . '</td>  
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['val_adicion']) . '</td>   
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['val_cte']) . '</td> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['estado'] . '</td></tr>';  
            }            
            echo $tabla;
            ?>
        </tbody>
    </table>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>Registro presupuestal</th>
        </tr>
    </table>

    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">No Registro</th>
                <th style="border:#A9A9A9 1px solid;">Fecha</th>
                <th style="border:#A9A9A9 1px solid;">Tipo</th>
                <th style="border:#A9A9A9 1px solid;">No Contrato</th>
                <th style="border:#A9A9A9 1px solid;">Valor registro</th>
                <th style="border:#A9A9A9 1px solid;">Saldo</th>
                <th style="border:#A9A9A9 1px solid;">Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <?php
            $tabla = '';
            foreach ($obj_regpresupuestal as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['id_manu'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['fecha'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['tipo'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['num_contrato'] . '</td>  
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['vr_registro']) . '</td>   
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['vr_saldo']) . '</td> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['estado'] . '</td></tr>';  
            }            
            echo $tabla;
            ?>
        </tbody>
    </table>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>Obligaciones</th>
        </tr>
    </table>

    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">No Causación</th>
                <th style="border:#A9A9A9 1px solid;">Fecha</th>
                <th style="border:#A9A9A9 1px solid;">Soporte</th>
                <th style="border:#A9A9A9 1px solid;">Valor causado</th>
                <th style="border:#A9A9A9 1px solid;">Descuentos</th>
                <th style="border:#A9A9A9 1px solid;">Neto</th>
                <th style="border:#A9A9A9 1px solid;">Estado</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <?php
            $tabla = '';
            foreach ($obj_obligaciones as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['id_ctb_doc'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['fecha'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['num_doc'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['valorcausado']) . '</td>  
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['descuentos']) . '</td>   
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['neto']) . '</td> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['est'] . '</td></tr>';  
            }            
            echo $tabla;
            ?>
        </tbody>
    </table>
    
    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>Pagos</th>
        </tr>
    </table>

    <table style="width:100% !important; border:#A9A9A9 1px solid;">
        <thead style="font-size:70%; border:#A9A9A9 1px solid;">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center; border:#A9A9A9 1px solid;">
                <th style="border:#A9A9A9 1px solid;">Consecutivo</th>
                <th style="border:#A9A9A9 1px solid;">Fecha</th>
                <th style="border:#A9A9A9 1px solid;">Detalle</th>
                <th style="border:#A9A9A9 1px solid;">Valor pagado</th>
            </tr>
        </thead>
        <tbody style="font-size: 70%;">
            <?php
            $tabla = '';
            foreach ($obj_pagos as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td style="border:#A9A9A9 1px solid;">' . $obj['id_manu'] . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . $obj['fecha'] . '</td>
                        <td style="border:#A9A9A9 1px solid; text-align:left;">' . mb_strtoupper($obj['detalle']) . '</td>
                        <td style="border:#A9A9A9 1px solid;">' . formato_valor($obj['valorpagado']) . '</td></tr>';  
            }            
            echo $tabla;
            ?>
        </tbody>
    </table>
</div>