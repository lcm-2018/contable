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
    $sql = "SELECT M.id_mantenimiento,M.fec_mantenimiento,M.hor_mantenimiento,
                CASE M.tipo_mantenimiento WHEN 1 THEN 'PREVENTIVO' WHEN 2 THEN 'CORRECTIVO INTERNO' WHEN 3 THEN 'CORRECTIVO EXTERNO' END AS tipo_mantenimiento, 
                M.observaciones,
                CONCAT_WS(' ',U.apellido1,U.apellido2,U.nombre1,U.nombre2) AS nom_responsable,
                T.nom_tercero,M.fec_ini_mantenimiento,M.fec_fin_mantenimiento,
                CASE M.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'APROBADO' WHEN 3 THEN 'EN EJECUCION' WHEN 4 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS estado,
                CASE M.estado WHEN 1 THEN M.fec_creacion WHEN 2 THEN M.fec_aprueba WHEN 3 THEN M.fec_ejecucion WHEN 4 THEN M.fec_cierre WHEN 0 THEN M.fec_anulacion END AS fec_estado,
                CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_aprueba,
                usr.descripcion AS usr_perfil,usr.nom_firma
            FROM acf_mantenimiento AS M
            INNER JOIN tb_terceros T ON (T.id_tercero = M.id_tercero)
            INNER JOIN seg_usuarios_sistema U ON (U.id_usuario = M.id_responsable)
            LEFT JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario = M.id_usr_aprueba)
            WHERE M.id_mantenimiento=" . $id . " LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_e = $rs->fetch();

    $sql = "SELECT MD.id_mant_detalle,
                HV.placa,FM.nom_medicamento AS nom_articulo,                
                CA.nom_area,MD.observacion_mant,
                CASE MD.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' WHEN 4 THEN 'SIN SERVICIO' END AS estado_general
            FROM acf_mantenimiento_detalle MD
            INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo = MD.id_activo_fijo)
            INNER JOIN far_medicamentos AS FM ON (FM.id_med = HV.id_articulo)
            INNER JOIN far_centrocosto_area AS CA ON (CA.id_area=MD.id_area)
            WHERE MD.id_mantenimiento=" . $id . " ORDER BY MD.id_mant_detalle";
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
            <th>ORDEN DE MANTENIMIENTO DE ACTIVOS FIJOS</th>
        </tr>
    </table>

    <table style="width:100%; font-size:60%; text-align:left; border:#A9A9A9 1px solid;">
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Id. Mantenimiento</td>
            <td>Fecha</td>
            <td>Hora</td>
            <td>Estado</td>
            <td>Fecha Estado</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['id_mantenimiento']; ?></td>
            <td><?php echo $obj_e['fec_mantenimiento']; ?></td>
            <td><?php echo $obj_e['hor_mantenimiento']; ?></td>
            <td><?php echo $obj_e['estado']; ?></td>
            <td><?php echo $obj_e['fec_estado']; ?></td>
        </tr> 
        <tr style="background-color:#CED3D3; border:#A9A9A9 1px solid">
            <td>Tipo Mantenimiento</td>
            <td>Responsable</td>
            <td>Tercero</td>
            <td>Fecha Inicio Mantenimiento</td>
            <td>Fecha Fin Mantenimiento</td>
        </tr>
        <tr>
            <td><?php echo $obj_e['tipo_mantenimiento']; ?></td>
            <td><?php echo $obj_e['nom_responsable']; ?></td>
            <td><?php echo $obj_e['nom_tercero']; ?></td>
            <td><?php echo $obj_e['fec_ini_mantenimiento']; ?></td>
            <td><?php echo $obj_e['fec_fin_mantenimiento']; ?></td>
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
                <th>Estado General</th>
                <th>Area</th>
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
                        <td>' . $obj['estado_general'] . '</td>
                        <td>' . $obj['nom_area'] . '</td>
                        <td>' . $obj['observacion_mant'] . '</td></tr>';
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
                <div><?php echo $obj_e['usr_aprueba']; ?></div>
                <div><?php echo $obj_e['usr_perfil']; ?></div>
            </td>
            <td style="vertical-align: top">
                <div>-------------------------------------------------</div>
                <div>Aceptado Por</div>
            </td>
        </tr>        
    </table>
</div>