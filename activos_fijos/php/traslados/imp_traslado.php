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
    $sql = "SELECT AT.id_traslado,AT.fec_traslado,AT.hor_traslado,AT.observaciones,                    
                AO.nom_area AS nom_area_origen,SO.nom_sede AS nom_sede_origen,
                CONCAT_WS(' ',UO.apellido1,UO.apellido2,UO.nombre1,UO.nombre2)  AS nom_usuario_origen,                    
                AD.nom_area AS nom_area_destino,SD.nom_sede AS nom_sede_destino,
                CONCAT_WS(' ',UD.apellido1,UD.apellido2,UD.nombre1,UD.nombre2)  AS nom_usuario_destino,                
                CASE AT.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS estado,
                CASE AT.estado WHEN 0 THEN AT.fec_anula WHEN 1 THEN AT.fec_crea WHEN 2 THEN AT.fec_cierre END AS fec_estado,
                CONCAT_WS(' ',USR.nombre1,USR.nombre2,USR.apellido1,USR.apellido2) AS usr_cierra,
                USR.descripcion AS usr_perfil,USR.nom_firma
            FROM acf_traslado AS AT            
            INNER JOIN far_centrocosto_area AS AO ON (AO.id_area = AT.id_area_origen)
            INNER JOIN tb_sedes AS SO ON (SO.id_sede = AO.id_sede)
            LEFT JOIN seg_usuarios_sistema AS UO ON (UO.id_usuario = AT.id_usr_origen)           
            INNER JOIN far_centrocosto_area AS AD ON (AD.id_area = AT.id_area_destino)
            INNER JOIN tb_sedes AS SD ON (SD.id_sede = AD.id_sede)
            LEFT JOIN seg_usuarios_sistema AS UD ON (UD.id_usuario = AT.id_usr_destino)
            LEFT JOIN seg_usuarios_sistema AS USR ON (USR.id_usuario = AT.id_usr_cierre)
            WHERE AT.id_traslado=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT HV.placa,FM.nom_medicamento AS nom_articulo,HV.des_activo,TD.observacion,
                CASE TD.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' WHEN 4 THEN 'SIN SERVICIO' END AS estado_general
            FROM acf_traslado_detalle AS TD
            INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo = TD.id_activo_fijo)
            INNER JOIN far_medicamentos AS FM ON (FM.id_med = HV.id_articulo)
            WHERE TD.id_traslado=" . $id . " ORDER BY TD.id_traslado_detalle";
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
            <th>ORDEN DE TRASLADO DE ACTIVOS FIJOS</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Id. traslado</td>
            <td>Fecha traslado</td>
            <td>Hora traslado</td>
            <td>Estado</td>
            <td colspan="2">Fecha Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['id_traslado']; ?></td>
            <td><?php echo $obj_e['fec_traslado']; ?></td>
            <td><?php echo $obj_e['hor_traslado']; ?></td>
            <td><?php echo $obj_e['estado']; ?></td>
            <td colspan="2"><?php echo $obj_e['fec_estado']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Sede Origen</td>
            <td>Area Origen</td>
            <td>Responsable Origen</td>
            <td>Sede Destino</td>
            <td>Area Destino</td>
            <td>Responsable Destino</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['nom_sede_origen']; ?></td>
            <td><?php echo $obj_e['nom_area_origen']; ?></td>
            <td><?php echo $obj_e['nom_usuario_origen']; ?></td>
            <td><?php echo $obj_e['nom_sede_destino']; ?></td>
            <td><?php echo $obj_e['nom_area_destino']; ?></td>
            <td><?php echo $obj_e['nom_usuario_destino']; ?></td>
        </tr>
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td colspan="6">Observaciones</td>
        </tr>
        <tr>
            <td colspan="6"><?php echo $obj_e['observaciones']; ?></td>
        </tr>
    </table>

    <table style="width:100% !important">
        <thead style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>Placa</th>
                <th>Articulo</th>
                <th>Activo Fijo</th>
                <th>Estado General</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            foreach ($obj_ds as $obj) {
                $tabla .=  '<tr class="resaltar"> 
                        <td>' . $obj['placa'] . '</td>
                        <td style="text-align:left">' . mb_strtoupper($obj['nom_articulo']) . '</td>   
                        <td style="text-align:left">' . mb_strtoupper($obj['des_activo']) . '</td>   
                        <td>' . $obj['estado_general'] . '</td>
                        <td>' . $obj['observacion'] . '</td></tr>';
            }
            echo $tabla;
            ?>
        </tbody>
        <tfoot style="font-size:60%">
            <tr style="background-color:#CED3D3; color:#000000">                
                <td>TOTAL REGISTROS:<?php echo COUNT($obj_ds); ?> </td>
                <td colspan="4"></td>
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