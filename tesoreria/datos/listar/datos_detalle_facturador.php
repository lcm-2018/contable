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
                , `tr`.`id_arqueo`
                , `tr`.`nro_factura`
                , `tr`.`tipo_atencion`
                , `tr`.`fec_factura`
                , `tr`.`valor`
                , 0 as `valor_anulado`
                , `tr`.`fec_anulado`
            FROM
                (SELECT   
                    `fac_arqueo`.`id_arqueo`
                    , `fac_facturacion`.`prefijo`
                    , `fac_facturacion`.`num_efactura` AS `nro_factura`
                    , `fac_facturacion`.`fec_factura` AS `fec_factura`
                    , `adm_tipo_atencion`.`nombre` AS `tipo_atencion` 
                    , `fac_facturacion`.`val_factura` AS `valor`
                    , `fac_facturacion`.`val_copago` 
                    , `fac_facturacion`.`val_subsidio`
                    , `fac_facturacion`.`fec_anulacion` AS `fec_anulado`
                    , DATE_FORMAT(`fac_arqueo`.`fec_creacion`, '%Y-%m-%d') AS `fec_creacion`
                    , `tb_terceros`.`nit_tercero` AS `num_documento`
                FROM
                    `fac_arqueo_detalles`
                    INNER JOIN `fac_arqueo` ON (`fac_arqueo_detalles`.`id_arqueo` = `fac_arqueo`.`id_arqueo`)
                    INNER JOIN `fac_facturacion` ON (`fac_arqueo_detalles`.`id_factura` = `fac_facturacion`.`id_factura`)
                    INNER JOIN `adm_ingresos` ON (`fac_facturacion`.`id_ingreso` = `adm_ingresos`.`id_ingreso`)
                    INNER JOIN `adm_tipo_atencion` ON (`adm_ingresos`.`id_tipo_atencion` = `adm_tipo_atencion`.`id_tipo`)
                    INNER JOIN `seg_usuarios_sistema` ON (`fac_arqueo`.`id_facturador` = `seg_usuarios_sistema`.`id_usuario`)
                    INNER JOIN `tb_terceros` ON (`seg_usuarios_sistema`.`num_documento` = `tb_terceros`.`nit_tercero`)
                WHERE  DATE_FORMAT(`fac_arqueo`.`fec_creacion`, '%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_fin'   
                UNION ALL
                SELECT
                    `fac_arqueo`.`id_arqueo`
                    , `far_ventas`.`prefijo`
                    , `far_ventas`.`num_efactura`  AS `nro_factura` 
                    , `far_ventas`.`fec_venta` AS `fec_factura`
                    , 'Venta de medicamentos' AS `tipo_atencion` 
                    , `far_ventas`.`val_factura` AS `valor`
                    , `far_ventas`.`val_factura`
                    , 0 AS `val_subsidio`
                    , `far_ventas`.`fec_anulacion` AS `fec_anulado`
                    , DATE_FORMAT(`fac_arqueo`.`fec_creacion`, '%Y-%m-%d') AS `fec_creacion`
                    , `tb_terceros`.`nit_tercero` as `num_documento`
                FROM
                    `fac_arqueo_detalles`
                    INNER JOIN `fac_arqueo` ON (`fac_arqueo_detalles`.`id_arqueo` = `fac_arqueo`.`id_arqueo`)
                    INNER JOIN `far_ventas` ON (`fac_arqueo_detalles`.`id_venta` = `far_ventas`.`id_venta`)
                    INNER JOIN `seg_usuarios_sistema` ON (`fac_arqueo`.`id_facturador` = `seg_usuarios_sistema`.`id_usuario`)
                    INNER JOIN `tb_terceros` ON (`seg_usuarios_sistema`.`num_documento` = `tb_terceros`.`nit_tercero`)
                WHERE  DATE_FORMAT(`fac_arqueo`.`fec_creacion`, '%Y-%m-%d') BETWEEN '$fecha_ini' AND '$fecha_fin') AS `tr`
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
        language: setIdioma,
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
                        <th colspan="6">TOTAL</th>
                        <th>$ <?= number_format($data['valor_fac'], 2) ?></th>
                    </tr>
                    <tr>
                        <th>ID Arqueo</th>
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
                        echo "<td>{$row['id_arqueo']}</td>";
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