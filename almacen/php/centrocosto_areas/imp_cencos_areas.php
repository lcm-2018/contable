<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$where = "WHERE far_centrocosto_area.id_area<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND far_centrocosto_area.nom_area LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $sql = "SELECT far_centrocosto_area.id_area,far_centrocosto_area.nom_area, 
            tb_centrocostos.nom_centro AS nom_centrocosto, 
            far_area_tipo.nom_tipo AS nom_tipo_area,              
            CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_responsable,
            tb_sedes.nom_sede,far_bodegas.nombre AS nom_bodega
        FROM far_centrocosto_area    
        INNER JOIN tb_centrocostos ON (tb_centrocostos.id_centro=far_centrocosto_area.id_centrocosto)
        INNER JOIN far_area_tipo ON (far_area_tipo.id_tipo=far_centrocosto_area.id_tipo_area)
        INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=far_centrocosto_area.id_responsable)
        INNER JOIN tb_sedes ON (tb_sedes.id_sede=far_centrocosto_area.id_sede)
        LEFT JOIN far_bodegas ON (far_bodegas.id_bodega=far_centrocosto_area.id_bodega)
        $where ORDER BY far_centrocosto_area.id_area DESC";
    $res = $cmd->query($sql);
    $objs = $res->fetchAll();
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
    
    <table style="width:100%; font-size:80%">
        <tr style="text-align:center">
            <th>REPORTE DE AREAS DE CENTRO DE COSTO</th>
        </tr>     
    </table>  

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>ID</th>
                <th>Nombre</th>
                <th>Centro Costo</th>
                <th>Tipo Area</th>
                <th>Responsable</th>
                <th>Sede</th>
                <th>Bodega</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php           
            $tabla = '';                                      
            foreach ($objs as $obj) {                              
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                    <td>' .$obj['id_area'] .'</td>                    
                    <td style="text-align:left">' . mb_strtoupper($obj['nom_area']). '</td>
                    <td>' .$obj['nom_centrocosto'] .'</td>
                    <td>' .$obj['nom_tipo_area'] .'</td>
                    <td>' .$obj['usr_responsable'] .'</td>
                    <td>' .$obj['nom_sede'] .'</td>
                    <td>' .$obj['nom_bodega'] .'</td></tr>';
            }            
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="7" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>