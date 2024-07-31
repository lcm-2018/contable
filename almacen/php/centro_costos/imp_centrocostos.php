<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}

include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$where = "WHERE tb_centrocostos.id_centro<>0";
if (isset($_POST['nombre']) && $_POST['nombre']) {
    $where .= " AND tb_centrocostos.nom_centro LIKE '" . $_POST['nombre'] . "%'";
}

try {
    $sql = "SELECT tb_centrocostos.id_centro,tb_centrocostos.nom_centro,tb_centrocostos.cuenta,
            CONCAT_WS(' - ',ctb_pgcp.cuenta,ctb_pgcp.nombre) AS cuenta,             
            CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_respon
        FROM tb_centrocostos
        INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=tb_centrocostos.id_responsable) 
        LEFT JOIN (SELECT tb_centrocostos_cta.id_cencos,MAX(tb_centrocostos_cta.id_cec_cta) AS id
                        FROM tb_centrocostos_cta
                        WHERE tb_centrocostos_cta.estado=1 AND tb_centrocostos_cta.fecha_vigencia<=DATE_FORMAT(NOW(), '%Y-%m-%d')
                        GROUP BY tb_centrocostos_cta.id_cencos
                        ) AS c ON (c.id_cencos=tb_centrocostos.id_centro)
        LEFT JOIN tb_centrocostos_cta ON (tb_centrocostos_cta.id_cec_cta=c.id)
        LEFT JOIN ctb_pgcp ON (ctb_pgcp.id_pgcp=tb_centrocostos_cta.id_cuenta)            
        $where ORDER BY tb_centrocostos.id_centro DESC";
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
            <th>REPORTE DE CENTROS DE COSTO</th>
        </tr>     
    </table>  

    <table style="width:100% !important">
        <thead style="font-size:80%">                
            <tr style="background-color:#CED3D3; color:#000000; text-align:center">
                <th>ID</th>
                <th>Nombre</th>
                <th>Cuenta Contable Vigente</th>
                <th>Responsable</th>
            </tr>
        </thead>
        <tbody style="font-size: 60%;">
            <?php           
            $tabla = '';                                      
            foreach ($objs as $obj) {                              
                $tabla .=  '<tr class="resaltar" style="text-align:center"> 
                    <td>' .$obj['id_centro'] .'</td>                    
                    <td style="text-align:left">' . mb_strtoupper($obj['nom_centro']). '</td>
                    <td style="text-align:left">' .$obj['cuenta'] .'</td>
                    <td>' .$obj['usr_respon'] .'</td></tr>';
            }            
            echo $tabla;
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <td colspan="4" style="text-align:left">
                    No. de Registros: <?php echo count($objs); ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>