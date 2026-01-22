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

$id_reporte = $_POST['id_reporte'];
$titulo = '';
 switch($id_reporte){
    case '1':
        $titulo = 'REPORTE DE INGRESOS ENTRE:' . $_POST['fec_ini'] . ' y ' .  $_POST['fec_fin'] . ', TOTALIZADOS POR TIPO DE INGRESO-TERCERO-SUBGRUPO';
        break;
} 

$where = "WHERE 1";
if (isset($_POST['id_ing']) && $_POST['id_ing']) {
    $where .= " AND far_orden_ingreso.id_ingreso='" . $_POST['id_ing'] . "'";
}
if (isset($_POST['num_ing']) && $_POST['num_ing']) {
    $where .= " AND far_orden_ingreso.num_ingreso='" . $_POST['num_ing'] . "'";
}
if (isset($_POST['num_fac']) && $_POST['num_fac']) {
    $where .= " AND far_orden_ingreso.num_factura LIKE '" . $_POST['num_fac'] . "%'";
}
if (isset($_POST['fec_ini']) && $_POST['fec_ini'] && isset($_POST['fec_fin']) && $_POST['fec_fin']) {
    $where .= " AND far_orden_ingreso.fec_ingreso BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
}
if (isset($_POST['id_tercero']) && $_POST['id_tercero']) {
    $where .= " AND far_orden_ingreso.id_provedor=" . $_POST['id_tercero'] . "";
}
if (isset($_POST['id_tiping']) && $_POST['id_tiping']) {
    $where .= " AND far_orden_ingreso.id_tipo_ingreso=" . $_POST['id_tiping'] . "";
}
if (isset($_POST['estado']) && strlen($_POST['estado'])) {
    $where .= " AND far_orden_ingreso.estado=" . $_POST['estado'];
}
if (isset($_POST['modulo']) && strlen($_POST['modulo'])) {
    $where .= " AND far_orden_ingreso.creado_far=" . $_POST['modulo'];
}

try {
    $sql = "SELECT far_orden_ingreso_tipo.id_tipo_ingreso,far_orden_ingreso_tipo.nom_tipo_ingreso,
                SUM(far_orden_ingreso.val_total) AS val_total_ti
            FROM far_orden_ingreso
            INNER JOIN far_orden_ingreso_tipo ON (far_orden_ingreso_tipo.id_tipo_ingreso=far_orden_ingreso.id_tipo_ingreso)
            $where 
            GROUP BY far_orden_ingreso_tipo.id_tipo_ingreso
            ORDER BY far_orden_ingreso_tipo.id_tipo_ingreso";
    $res = $cmd->query($sql);
    $objs = $res->fetchAll();

    $sql = "SELECT tb_terceros.id_tercero,tb_terceros.nom_tercero,tb_tipos_documento.codigo_ne,tb_terceros.nit_tercero,
                tb_terceros.dir_tercero,tb_terceros.tel_tercero,tb_municipios.nom_municipio,tb_departamentos.nom_departamento,
                SUM(far_orden_ingreso.val_total) AS val_total_tr
            FROM far_orden_ingreso            
            INNER JOIN tb_terceros ON (tb_terceros.id_tercero=far_orden_ingreso.id_provedor) 
            LEFT JOIN tb_tipos_documento ON (tb_tipos_documento.id_tipodoc=tb_terceros.tipo_doc) 
            LEFT JOIN tb_municipios ON (tb_municipios.id_municipio=tb_terceros.id_municipio)   
            LEFT JOIN tb_departamentos ON (tb_departamentos.id_departamento=tb_municipios.id_departamento) 
            $where AND far_orden_ingreso.id_tipo_ingreso=:id_tipo_ingreso
            GROUP BY tb_terceros.id_tercero
            ORDER BY tb_terceros.nom_tercero";
    $rs_t = $cmd->prepare($sql);

    $sql = "SELECT far_subgrupos.id_subgrupo,CONCAT_WS(' - ',far_subgrupos.cod_subgrupo,far_subgrupos.nom_subgrupo) AS nom_subgrupo,                
                SUM(far_orden_ingreso_detalle.cantidad*far_orden_ingreso_detalle.valor) AS val_total_sg
            FROM far_orden_ingreso_detalle
            INNER JOIN far_orden_ingreso ON (far_orden_ingreso.id_ingreso=far_orden_ingreso_detalle.id_ingreso)
            INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_orden_ingreso_detalle.id_lote)
            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
            INNER JOIN far_subgrupos ON (far_subgrupos.id_subgrupo=far_medicamentos.id_subgrupo)
            $where AND far_orden_ingreso.id_tipo_ingreso=:id_tipo_ingreso AND far_orden_ingreso.id_provedor=:id_tercero
            GROUP BY far_subgrupos.id_subgrupo
            ORDER BY far_subgrupos.id_subgrupo";
    $rs_d = $cmd->prepare($sql);

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
            <th><?php echo $titulo; ?></th>
        </tr>     
    </table>

    <table style="width:100% !important">
        <tbody style="font-size: 60%;">
            <?php
            $tabla = '';
            switch($id_reporte){
                case '1':
                    $tabla = '<tr style="background-color:#CED3D3; text-align:center">
                        <th>Identificación</th><th>Tercero</th><th>Direccion</th><th>Munhicipio</th><th>Vr. Parcial</th><th>Vr. Total</th></tr>';
                    break; 
            }

            $total = 0;
            $numreg = 0;

            foreach ($objs as $obj1) {
                $id_tipo_ingreso = $obj1['id_tipo_ingreso'];
                
                $tabla .= '<tr><th colspan="5" style="text-align:left">TIPO DE INGRESO: ' . mb_strtoupper($obj1['nom_tipo_ingreso']) . '</th>
                            <th style="text-align:right">' . formato_valor($obj1['val_total_ti']) . '</th></tr>';

                $rs_t->bindParam(':id_tipo_ingreso',$id_tipo_ingreso);
                $rs_t->execute();
                $objt = $rs_t->fetchAll();

                foreach ($objt as $obj2) {
                    $id_tercero = $obj2['id_tercero'];
                
                    $tabla .= '<tr><th style="text-align:left">' . str_repeat('&nbsp',10) . $obj2['codigo_ne'] . ' ' . $obj2['nit_tercero'] . '</th>
                                <th style="text-align:left">' . mb_strtoupper($obj2['nom_tercero']) . '</th>
                                <th style="text-align:left">' . mb_strtoupper($obj2['dir_tercero']) . '</th>
                                <th style="text-align:left">' . mb_strtoupper($obj2['nom_municipio']) . ' - ' . mb_strtoupper($obj2['nom_departamento']) .  '</th>
                                <th style="text-align:right">' . formato_valor($obj2['val_total_tr']) . '</th></tr>';

                    $rs_d->bindParam(':id_tipo_ingreso',$id_tipo_ingreso);
                    $rs_d->bindParam(':id_tercero',$id_tercero);
                    $rs_d->execute();
                    $objd = $rs_d->fetchAll();

                    foreach ($objd as $obj) {
                        $tabla .=  '<tr class="resaltar">                                                                                 
                            <td colspan="4" style="text-align:left">' . str_repeat('&nbsp',20) . mb_strtoupper($obj['nom_subgrupo']) . '</td>
                            <td style="text-align:right">' . formato_valor($obj['val_total_sg']) . '</td></tr>'; 
                        $total += $obj['val_total_sg'];
                        $numreg += 1;
                    }    
                }                
            }    
            echo $tabla;                   
            ?>            
        </tbody>
        <tfoot style="font-size:60%"> 
            <tr style="background-color:#CED3D3; color:#000000">
                <th colspan="4" style="text-align:left">
                    No. de Registros: <?php echo $numreg; ?>  
                </th>
                <th style="text-align:left">
                    TOTAL:
                </th>
                <th style="text-align:right">
                    <?php echo formato_valor($total); ?>  
                </th>
            </tr>
        </tfoot>
    </table>
</div>