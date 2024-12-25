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
    $sql = "SELECT far_traslado.id_traslado,far_traslado.num_traslado,far_traslado.fec_traslado,far_traslado.hor_traslado,far_traslado.detalle,far_traslado.val_total,
            ss.nom_sede AS nom_sede_solicita,bs.nombre AS nom_bodega_solicita,                    
            sp.nom_sede AS nom_sede_provee,bp.nombre AS nom_bodega_provee,                    
            CASE far_traslado.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS estado,
            CASE far_traslado.estado WHEN 0 THEN far_traslado.fec_anulacion WHEN 1 THEN far_traslado.fec_creacion WHEN 2 THEN far_traslado.fec_cierre END AS fec_estado,
            CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_cierra,
            usr.descripcion AS usr_perfil,usr.nom_firma
        FROM far_traslado             
        INNER JOIN tb_sedes AS ss ON (ss.id_sede = far_traslado.id_sede_destino)
        INNER JOIN far_bodegas AS bs ON (bs.id_bodega = far_traslado.id_bodega_destino)           
        INNER JOIN tb_sedes AS sp ON (sp.id_sede = far_traslado.id_sede_origen)
        INNER JOIN far_bodegas AS bp ON (bp.id_bodega = far_traslado.id_bodega_origen)
        LEFT JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=far_traslado.id_usr_cierre)
        WHERE id_traslado=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
            far_traslado_detalle.cantidad,far_traslado_detalle.valor,
            (far_traslado_detalle.cantidad*far_traslado_detalle.valor) AS val_total
        FROM far_traslado_detalle
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_traslado_detalle.id_medicamento)
        WHERE far_traslado_detalle.id_traslado=" . $id . " ORDER BY far_traslado_detalle.id_ped_detalle";
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
            <th>ORDEN DE traslado DE BODEGA</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Id. traslado</td>
            <td>No. traslado</td>
            <td>Fecha traslado</td>
            <td>Hora traslado</td>
            <td>Estado</td>
            <td>Fecha Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['id_traslado']; ?></td>
            <td><?php echo $obj_e['num_traslado']; ?></td>
            <td><?php echo $obj_e['fec_traslado']; ?></td>
            <td><?php echo $obj_e['hor_traslado']; ?></td>
            <td><?php echo $obj_e['estado']; ?></td>
            <td><?php echo $obj_e['fec_estado']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td colspan="3">Sede y Bodega DE donde se solicita</td>
            <td colspan="3">Sede y Bodega Proveedora A donde se solicita</td>
        </tr>
        <tr>
            <td colspan="2"><?php echo $obj_e['nom_sede_solicita']; ?></td>
            <td><?php echo $obj_e['nom_bodega_solicita']; ?></td>
            <td colspan="2"><?php echo $obj_e['nom_sede_provee']; ?></td>
            <td><?php echo $obj_e['nom_bodega_provee']; ?></td>
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
                <th>Cantidad</th>
                <th>Valor Promedio</th>
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
                        <td>' . $obj['cantidad'] . '</td>
                        <td>' . formato_valor($obj['valor']) . '</td>   
                        <td>' . formato_valor($obj['val_total']) . '</td></tr>';
            }
            echo $tabla;
            ?>
        </tbody>
        <tfoot style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="3"></td>
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
                <div>Aceptado Por</div>
            </td>
        </tr>        
    </table>
</div>