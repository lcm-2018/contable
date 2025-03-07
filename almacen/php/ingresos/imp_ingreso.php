<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;

try {
    $sql = "SELECT far_orden_ingreso.id_ingreso,far_orden_ingreso.num_ingreso,far_orden_ingreso.fec_ingreso,
            far_orden_ingreso.hor_ingreso,far_orden_ingreso.num_factura,far_orden_ingreso.fec_factura,
            far_orden_ingreso.detalle,far_orden_ingreso.val_total,
            tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega,
            tb_terceros.nom_tercero,far_orden_ingreso_tipo.nom_tipo_ingreso,
            CASE far_orden_ingreso.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS estado,
            CASE far_orden_ingreso.estado WHEN 0 THEN far_orden_ingreso.fec_anulacion WHEN 1 THEN far_orden_ingreso.fec_creacion WHEN 2 THEN far_orden_ingreso.fec_cierre END AS fec_estado,
            CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_cierra,
            usr.descripcion AS usr_perfil,usr.nom_firma
        FROM far_orden_ingreso 
        INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_orden_ingreso.id_sede)
        INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_orden_ingreso.id_bodega)
        INNER JOIN tb_terceros ON (tb_terceros.id_tercero=far_orden_ingreso.id_provedor)
        INNER JOIN far_orden_ingreso_tipo ON (far_orden_ingreso_tipo.id_tipo_ingreso=far_orden_ingreso.id_tipo_ingreso)
        LEFT JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=far_orden_ingreso.id_usr_cierre)
        WHERE id_ingreso=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT far_medicamentos.cod_medicamento,
            CONCAT_WS(' - ',far_medicamentos.nom_medicamento,far_presentacion_comercial.nom_presentacion) as nom_medicamento,
            far_medicamento_lote.lote,
            far_medicamento_lote.fec_vencimiento,far_orden_ingreso_detalle.cantidad,far_orden_ingreso_detalle.valor_sin_iva,
            far_orden_ingreso_detalle.iva,far_orden_ingreso_detalle.valor,
            (far_orden_ingreso_detalle.cantidad*far_orden_ingreso_detalle.valor) AS val_total
        FROM far_orden_ingreso_detalle
        INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_orden_ingreso_detalle.id_presentacion)
        INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote = far_orden_ingreso_detalle.id_lote)
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_medicamento_lote.id_med)
        WHERE far_orden_ingreso_detalle.id_ingreso=" . $id . " ORDER BY far_orden_ingreso_detalle.id_ing_detalle";
    $rs = $cmd->query($sql);
    $obj_ds = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="text-right py-3">
    <a type="button" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
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

    <?php include('../common/reporte_header.php'); ?>

    <table style="width:100%; font-size:70%">
        <tr style="text-align:center">
            <th>ORDEN DE INGRESO</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Id. Ingreso</td>
            <td>No. Ingreso</td>
            <td>Fecha Ingreso</td>
            <td>Hora Ingreso</td>
            <td>Estado</td>
            <td>Fecha Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['id_ingreso']; ?></td>
            <td><?php echo $obj_e['num_ingreso']; ?></td>
            <td><?php echo $obj_e['fec_ingreso']; ?></td>
            <td><?php echo $obj_e['hor_ingreso']; ?></td>
            <td><?php echo $obj_e['estado']; ?></td>
            <td><?php echo $obj_e['fec_estado']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Sede</td>
            <td>Bodega</td>
            <td>Tipo de Ingreso</td>
            <td>No. Factura/Acta/Remisión</td>
            <td>Fecha Factura/Acta/Remisión</td>
            <td>Proveedor</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['nom_sede']; ?></td>
            <td><?php echo $obj_e['nom_bodega']; ?></td>
            <td><?php echo $obj_e['nom_tipo_ingreso']; ?></td>
            <td><?php echo $obj_e['num_factura']; ?></td>
            <td><?php echo $obj_e['fec_factura']; ?></td>
            <td><?php echo $obj_e['nom_tercero']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td colspan="6">Detalle</td>
        </tr>
        <tr>
            <td colspan="6"><?php echo $obj_e['detalle']; ?></td>
        </tr>
    </table>

    <table style="width:100% !important">
        <thead style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Código</th>
                <th>Descripción</th>
                <th>Lote</th>
                <th>Fecha Vencimiento</th>
                <th>Cantidad</th>
                <th>Valor Unitario</th>
                <th>IVA</th>
                <th>Valor Costo</th>
                <th>Valor Total</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($obj_ds as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['cod_medicamento'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_medicamento']) . '</td>   
                        <td>' . $obj['lote'] . '</td>
                        <td>' . $obj['fec_vencimiento'] . '</td>
                        <td>' . $obj['cantidad'] . '</td>
                        <td>' . formato_valor($obj['valor_sin_iva']) . '</td> 
                        <td>' . $obj['iva'] . '</td>
                        <td>' . formato_valor($obj['valor']) . '</td> 
                        <td>' . formato_valor($obj['val_total']) . '</td></tr>';
            }
            echo $tabla;
            ?>
        </tbody>
        <tfoot style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="7"></td>
                <td>TOTAL:</td>
                <td><?php echo formato_valor($obj_e['val_total']); ?> </td>
            </tr>
        </tfoot>
    </table>

    <table style="width:100%; font-size:70%; text-align:center">
        <tr>
            <td style="width:50%">
                <?php if ($obj_e['nom_firma']) : ?>
                    <img src="<?php echo $ruta_firmas . $obj_e['nom_firma'] ?>">
                <?php endif; ?>
            </td>
            <td style="width:50%">               
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top">
                <div>-------------------------------------------------</div>
                <div><?php echo $obj_e['usr_cierra']; ?></div>
                <div><?php echo $obj_e['usr_perfil']; ?></div>
            </td>
            <td style="vertical-align: top">
                <div>-------------------------------------------------</div>
                <div>Entregado Por</div>
            </td>
        </tr>        
    </table>
</div>