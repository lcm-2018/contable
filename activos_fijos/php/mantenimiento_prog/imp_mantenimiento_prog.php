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
    $sql = "SELECT MM.id_mantenimiento,MM.fec_mantenimiento,MM.hor_mantenimiento,
                CASE MM.tipo_mantenimiento WHEN 1 THEN 'PREVENTIVO' WHEN 2 THEN 'CORRECTIVO INTERNO' WHEN 3 THEN 'CORRECTIVO EXTERNO' END AS tipo_mantenimiento, 
                MM.fec_ini_mantenimiento,MM.fec_fin_mantenimiento,
                CASE MM.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'APROBADO' WHEN 3 THEN 'EN EJECUCION' WHEN 4 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS estado_orden,
                CASE MM.estado WHEN 1 THEN MM.fec_creacion WHEN 2 THEN MM.fec_aprueba WHEN 3 THEN MM.fec_ejecucion WHEN 4 THEN MM.fec_cierre WHEN 0 THEN MM.fec_anulacion END AS fec_estado_orden,
                MD.id_mant_detalle,MD.observacion_mant,MD.observacion_fin_mant,
                HV.placa,HV.num_serial,FM.nom_medicamento AS nom_articulo,                
                CASE MD.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' WHEN 4 THEN 'SIN SERVICIO' END AS estado_general,
                CASE MD.estado_fin_mant WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' WHEN 4 THEN 'SIN SERVICIO' END AS estado_fin_mant,
                CASE MD.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'EN MANTENIMIENTO' WHEN 3 THEN 'FINALIZADO' END AS estado,
                CASE MD.estado WHEN 1 THEN MM.fec_ejecucion WHEN 2 THEN MM.fec_ejecucion WHEN 3 THEN MD.fec_finaliza END AS fec_estado,
                CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_finaliza,
                usr.descripcion AS usr_perfil,usr.nom_firma
            FROM acf_mantenimiento_detalle AS MD
            INNER JOIN acf_mantenimiento AS MM ON (MM.id_mantenimiento=MD.id_mantenimiento)
            INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo=MD.id_activo_fijo)
            INNER JOIN far_medicamentos FM ON (FM.id_med=HV.id_articulo)
            LEFT JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario = MD.id_usr_finaliza)
            WHERE MD.id_mant_detalle=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT MN.id_det_nota,MN.fec_nota,MN.hor_nota,MN.observacion,MN.archivo,
                CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_crea,
                usr.descripcion AS usr_perfil,usr.nom_firma
            FROM acf_mantenimiento_detalle_nota AS MN
            LEFT JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario = MN.id_usr_crea)
            WHERE id_mant_detalle=" . $id . " ORDER BY id_det_nota";
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
            <th>PROGRESO DE MANTENIMIENTO DE ACTIVOS FIJOS</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">            
            <td>Id. Orden Mantenimiento</td>
            <td>Fecha</td>
            <td>Hora</td>
            <td>Tipo de Mantenimiento</td>
            <td>Periodo</td>
            <td>Estado</td>
            <td>Fecha Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['id_mantenimiento']; ?></td>
            <td><?php echo $obj_e['fec_mantenimiento']; ?></td>
            <td><?php echo $obj_e['hor_mantenimiento']; ?></td>
            <td><?php echo $obj_e['tipo_mantenimiento']; ?></td>
            <td><?php echo $obj_e['fec_ini_mantenimiento'].'-'.$obj_e['fec_fin_mantenimiento']; ?></td>
            <td><?php echo $obj_e['estado_orden']; ?></td>
            <td><?php echo $obj_e['fec_estado_orden']; ?></td>
        </tr> 
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Id. Registro Mantenimiento</td>
            <td>Placa</td>
            <td colspan="2">Articulo</td>            
            <td>Serial</td>
            <td>Estado</td>
            <td>Fecha Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['id_mant_detalle']; ?></td>
            <td><?php echo $obj_e['placa']; ?></td>
            <td colspan="2"><?php echo $obj_e['nom_articulo']; ?></td>
            <td><?php echo $obj_e['num_serial']; ?></td>
            <td><?php echo $obj_e['estado']; ?></td>
            <td><?php echo $obj_e['fec_estado']; ?></td>
        </tr>        
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <th colspan="3">Inicio del Mantenimiento</td>
            <th colspan="4">Finalización del Mantenimiento</td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Estado General</td>
            <td colspan="2">Observacion</td>
            <td>Estado General</td>            
            <td colspan="3">Observacion</td>
        </tr>
        <tr">            
            <td><?php echo $obj_e['estado_general']; ?></td>
            <td colspan="2"><?php echo $obj_e['observacion_mant']; ?></td>
            <td><?php echo $obj_e['estado_fin_mant']; ?></td>
            <td colspan="3"><?php echo $obj_e['observacion_fin_mant']; ?></td>
        </tr>
    </table>

    <table style="width:100% !important">
        <thead style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Fecha</th>
                <th>Hora</th>
                <th>Observación</th>
                <th>Archivo Adjunto</th>
                <th>Responsable</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php foreach ($obj_ds as $obj) : ?>
                <tr class="resaltar">
                    <td><?php echo $obj['fec_nota'] ?></td>
                    <td><?php echo $obj['hor_nota'] ?></td>
                    <td style="text-align:left"><?php echo $obj['observacion'] ?></td>
                    <td><?php echo $obj['archivo'] ?></td> 
                    <td>
                        <?php if ($obj['nom_firma']): ?>
                            <img src="<?php echo $ruta_firmas . $obj['nom_firma'] ?>">
                        <?php endif; ?>
                        <div>-------------------------------------------------</div>
                        <div><?php echo $obj_e['usr_finaliza']; ?></div>
                        <div><?php echo $obj_e['usr_perfil']; ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000">                
                <td>TOTAL REGISTROS:<?php echo COUNT($obj_ds); ?> </td>
                <td colspan="3"></td>
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
                <div><?php echo $obj_e['usr_finaliza']; ?></div>
                <div><?php echo $obj_e['usr_perfil']; ?></div>
            </td>
            <td style="vertical-align: top">
                <div>-------------------------------------------------</div>
                <div>Aceptado Por</div>
            </td>
        </tr>        
    </table>
</div>