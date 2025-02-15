<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../../conexion.php';

$id_arqueo = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT
                `id_ctb_doc`, `tes_causa_arqueo`.`id_tercero`, `fecha_ini`, `fecha_fin`, `valor_fac`, `valor_arq`, `observaciones`,  `nom_tercero`
            FROM `tes_causa_arqueo`
                LEFT JOIN `tb_terceros`
                    ON(`tes_causa_arqueo`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE `id_causa_arqueo` = $id_arqueo";
    $rs = $cmd->query($sql);
    $data = $rs->fetch();
    $tercero = $data['id_tercero'];
    $fecha_ini = $data['fecha_ini'];
    $fecha_fin = $data['fecha_fin'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT 
                `tb_terceros`.`id_tercero_api`
                , `tr`.`nro_factura`
                , `tr`.`tipo_atencion`
                , `tr`.`fec_factura`
                , `tr`.`valor`
                , `tr`.`valor_anulado`
                , `tr`.`fec_anulado`
            FROM
                (SELECT
                    `seg_usuarios_sistema`.`num_documento`
                    , IF(`fac_facturacion`.`num_efactura` IS NULL, `fac_facturacion`.`num_factura`, CONCAT(`fac_facturacion`.`prefijo`, `fac_facturacion`.`num_efactura`)) AS `nro_factura`
                    , `adm_tipo_atencion`.`nombre` AS `tipo_atencion`
                    , `fac_facturacion`.`fec_factura`
                    , `fac_facturacion`.`val_copago` AS `valor`
                    , CASE `fac_facturacion`.`estado` WHEN 0 THEN `fac_facturacion`.`val_copago` ELSE 0 END  AS `valor_anulado`      
                    , DATE_FORMAT(`fac_facturacion`.`fec_anulacion`, '%Y-%m-%d') AS `fec_anulado`
                FROM `fac_facturacion` 
                    INNER JOIN `seg_usuarios_sistema` 
                        ON (`fac_facturacion`.`id_usr_crea` = `seg_usuarios_sistema`.`id_usuario`)
                    INNER JOIN `adm_ingresos` 
                        ON (`fac_facturacion`.`id_ingreso`=`adm_ingresos`.`id_ingreso`)
                    INNER JOIN `adm_tipo_atencion` 
                        ON (`adm_ingresos`.`id_tipo_atencion`=`adm_tipo_atencion`.`id_tipo`)
                WHERE `fac_facturacion`.`val_copago` >0 AND `fac_facturacion`.`estado` <> 1 AND `fac_facturacion`.`fec_factura` BETWEEN '$fecha_ini' AND '$fecha_fin'
                UNION ALL
                SELECT
                    `seg_usuarios_sistema`.`num_documento`
                    , IF(`far_ventas`.`num_efactura` IS NULL,`far_ventas`.`num_factura`,CONCAT(`far_ventas`.`prefijo`,`far_ventas`.`num_efactura`)) AS `nro_factura` 
                    , 'VENTA PARTICULAR FARMACIA' AS `tipo_atencion`
                    , `far_ventas`.`fec_venta` AS `fec_factura`
                    , `far_ventas`.`val_factura` AS `valor`
                    , CASE `far_ventas`.`estado` WHEN 0 THEN `far_ventas`.`val_factura` ELSE 0 END `valor_anulado`    
                    , DATE_FORMAT(`far_ventas`.`fec_anulacion`, '%Y-%m-%d') AS `fec_anulado`
                FROM
                    far_ventas 
                    INNER JOIN seg_usuarios_sistema  
                        ON (far_ventas.id_usr_crea = seg_usuarios_sistema.id_usuario)
                WHERE far_ventas.estado <> 1 AND far_ventas.fec_venta BETWEEN '$fecha_ini' AND '$fecha_fin' 
                UNION ALL
                SELECT
                    seg_usuarios_sistema.num_documento
                    , IF(num_efactura IS NULL,fac_otros.num_factura, CONCAT(fac_otros.prefijo,fac_otros.num_efactura)) AS nro_factura   
                    , fac_otros.detalle    
                    , fac_otros.fec_factura
                    , fac_otros.val_factura AS valor
                    , CASE fac_otros.estado WHEN 0 THEN fac_otros.val_factura ELSE 0 END valor_anulado   
                    , DATE_FORMAT(fac_otros.fec_anulacion, '%Y-%m-%d') AS fec_anulado
                FROM
                    fac_otros 
                    INNER JOIN seg_usuarios_sistema 
                        ON (fac_otros.id_usr_crea = seg_usuarios_sistema.id_usuario)
                WHERE fac_otros.id_eps = 1 AND fac_otros.estado <> 1 AND fac_otros.val_factura>0 AND fac_otros.fec_factura BETWEEN '$fecha_ini' AND '$fecha_fin') AS `tr`
                LEFT JOIN `tb_terceros`
                    ON(`tb_terceros`.`nit_tercero` = `tr`.`num_documento`)
            WHERE `tb_terceros`.`id_tercero_api` = $tercero";
    $rs = $cmd->query($sql);
    $facturado = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableArqueoFacturador').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            "decimal": "",
            "emptyTable": "No hay información",
            "info": "Mostrando _START_ - _END_ registros de _TOTAL_ ",
            "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
            "infoFiltered": "(Filtrado de _MAX_ entradas en total )",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "Ver _MENU_ Filas",
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "search": '<i class="fas fa-search fa-flip-horizontal" style="font-size:1.5rem; color:#2ECC71;"></i>',
            "zeroRecords": "No se encontraron registros",
            "paginate": {
                "first": "&#10096&#10096",
                "last": "&#10097&#10097",
                "next": "&#10097",
                "previous": "&#10096"
            },
        },
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableCausacionArqueo').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 class="mb-0 text-light">LISTA DE ARQUEO DE CAJA<br><?= $data['nom_tercero'] ?></h5>
        </div>
        <div class="px-3 pt-2">
            <table id="tableArqueoFacturador" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th colspan="5">TOTAL</th>
                        <th>$ <?= number_format($data['valor_fac'], 2) ?></th>
                    </tr>
                    <tr>
                        <th>No. Factura</th>
                        <th>Atención</th>
                        <th>Fecha</th>
                        <th>Valor</th>
                        <th>Valor anulado</th>
                        <th>Fecha anula</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($facturado as $row) {
                        echo "<tr class='text-left'>";
                        echo "<td>{$row['nro_factura']}</td>";
                        echo "<td>{$row['tipo_atencion']}</td>";
                        echo "<td>{$row['fec_factura']}</td>";
                        echo "<td class='text-right'>$ " . number_format($row['valor'], 2) . "</td>";
                        echo "<td class='text-right'>$ " . number_format($row['valor_anulado'], 2) . "</td>";
                        echo "<td>{$row['fec_anulado']}</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <div class="text-right py-3">
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
            </div>
        </div>
    </div>
</div>