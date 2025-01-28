<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../terceros.php';

// Consulta tipo de presupuesto
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$id_cop_add = $_POST['id_cop_add'] ?? 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto` FROM `pto_presupuestos` WHERE (`id_vigencia` = $id_vigencia AND `id_tipo` = 2)";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT 
                `t1`.`id_ctb_doc`
                , `t1`.`causacion`
                , `t1`.`registro`
                , `t1`.`id_tercero`
                , `t1`.`fecha`
                , SUM(`t1`.`valor`) AS `valor`
                , SUM(`t1`.`valor_pagado`) AS `valor_pagado`
                , `t1`.`num_contrato`
            FROM 
                (SELECT
                    `ctb_doc`.`id_ctb_doc`
                    , `ctb_doc`.`id_manu` AS `causacion`
                    , `pto_crp`.`id_manu` AS `registro`
                    , `ctb_doc`.`id_tercero`
                    , `ctb_doc`.`fecha`
                    , `pto_cop_detalle`.`valor`
                    , IFNULL(`pto_pag_detalle`.`valor_pago`,0) AS `valor_pagado`
                    , `ctt_contratos`.`num_contrato`
                FROM
                    `pto_cop_detalle`
                    LEFT JOIN 
                        (SELECT
                            `id_pto_cop_det`
                            , IFNULL(SUM(`valor`),0) - IFNULL(SUM(`valor_liberado`),0) AS valor_pago
                        FROM
                            `pto_pag_detalle`
                                INNER JOIN `ctb_doc`
                                    ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                        WHERE (`ctb_doc`.`estado` > 0)
                        GROUP BY `id_pto_cop_det`)AS `pto_pag_detalle`
                        ON (`pto_pag_detalle`.`id_pto_cop_det` = `pto_cop_detalle`.`id_pto_cop_det`)
                    INNER JOIN `ctb_doc` 
                        ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc` AND `ctb_doc`.`estado` > 0)
                    INNER JOIN `pto_crp` 
                        ON ( `pto_crp`.`id_pto_crp` = `ctb_doc`.`id_crp`)
                    INNER JOIN `pto_cdp` 
                        ON (`pto_crp`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                    LEFT JOIN `ctt_adquisiciones` 
                        ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
                    LEFT JOIN `ctt_contratos` 
                        ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                WHERE `ctb_doc`.`id_crp` IS NOT NULL) AS `t1`  
            WHERE  `valor` > `valor_pagado`
            GROUP BY `id_ctb_doc`";
    $sql2 = $sql;
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
foreach ($listado as $rp) {
    if ($rp['id_tercero'] !== null) {
        $id_t[] = $rp['id_tercero'];
    }
}
$ids = implode(',', $id_t);
$terceros = getTerceros($ids, $cmd);
?>
<script>
    $('#tableObligacionesPago').DataTable({
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
    $('#tableObligacionesPago').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE OBLIGACIONES PARA PAGO DE TESORERÍA</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableObligacionesPago" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 13%;">Causación</th>
                        <th style="width: 13%;">Rp</th>
                        <th style="width: 13%;">Num Contrato</th>
                        <th style="width: 10%;">Fecha</th>
                        <th style="width: 10%;">Cc / Nit</th>
                        <th style="width: 20%;">Terceros</th>
                        <th style="width: 15%;">Valor</th>
                        <th style="width: 5%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($listado as $ce) {

                        $id_doc = $ce['id_ctb_doc'];
                        $fecha = date('Y-m-d', strtotime($ce['fecha']));
                        $editar = null;

                        // Consulta terceros en la api

                        $key = array_search($ce['id_tercero'], array_column($terceros, 'id_tercero_api'));
                        $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '';
                        $ccnit = $key !== false ? $terceros[$key]['nit_tercero'] : '';

                        // fin api terceros

                        $saldo_rp = $ce['valor'] - $ce['valor_pagado'];

                        if (PermisosUsuario($permisos, 5601, 3) || $id_rol == 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="cargarListaDetallePago(' . $id_doc . ',0)" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></a>';
                        }

                        if ($saldo_rp > 0) {
                    ?>
                            <tr>
                                <td class="text-left"><?php echo $ce['causacion']; ?></td>
                                <td class="text-left"><?php echo $ce['registro'] ?></td>
                                <td class="text-left"><?php echo $ce['num_contrato']   ?></td>
                                <td class="text-left"><?php echo $fecha; ?></td>
                                <td class="text-left"><?php echo $ccnit; ?></td>
                                <td class="text-left"><?php echo $tercero; ?></td>
                                <td class="text-right"> <?php echo number_format($saldo_rp, 2, ',', '.'); ?></td>
                                <td class="text-center"> <?php echo $editar; ?></td>
                            </tr>
                    <?php
                        }
                    }

                    ?>

                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
    </div>
</div>
<?php
$cmd = null;
