<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../terceros.php';
// Consulta tipo de presupuesto
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$id_doc = isset($_POST['id_doc']) ?  $_POST['id_doc'] : exit('Acceso no disponible');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `pto_crp_detalle`.`id_tercero_api`
                , IFNULL(`pto_crp_detalle`.`valor`,0) - IFNULL(`pto_crp_detalle`.`valor_liberado`,0) AS `valor_crp` 
                , `pto_cdp_detalle`.`id_rubro`
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , IFNULL(`t1`.`valor`,0) - IFNULL(`t1`.`valor_liberado`,0) AS `valor_cop` 
            FROM
                `ctb_doc`
                INNER JOIN `pto_crp` 
                    ON (`ctb_doc`.`id_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `pto_crp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
                INNER JOIN `pto_cargue` 
                    ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                LEFT JOIN 
            (SELECT
                `id_pto_crp_det`
                , IFNULL(SUM(`valor`),0) AS `valor`
                , IFNULL(SUM(`valor_liberado`),0) AS `valor_liberado`
            FROM
                `pto_cop_detalle`
            WHERE (`id_ctb_doc` = $id_doc) 
            GROUP BY `id_pto_crp_det`) AS `t1`  
                    ON (`t1`.`id_pto_crp_det` = `pto_crp_detalle`.`id_pto_crp_det`)
            WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
//consulto los datos del cop
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_pto_crp_det`
                , `valor`
                , `valor_liberado`
                , `id_pto_cop_det`
            FROM
                `pto_cop_detalle`
            WHERE (`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
            <h5 style="color: white;">LISTA DE REGISTROS PARA PAGOS DEL TERCERO OTROS SI </h5>
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

                    $id_t = [];
                    foreach ($listado as $rp) {
                        if ($rp['id_tercero'] != '') {
                            $id_t[] = $rp['id_tercero'];
                        }
                    }

                    $id_t = implode(',', $id_t);
                    $terceros = getTerceros($id_t, $cmd);
                    foreach ($listado as $ce) {

                        $id_doc = $ce['id_ctb_doc'];
                        $fecha = date('Y-m-d', strtotime($ce['fecha']));
                        // Consulta terceros en la api

                        $key = array_search($ce['id_tercero'], array_column($terceros, 'id_tercero_api'));
                        $tercero = ltrim($terceros[$key]['nom_tercero']);
                        $ccnit = $terceros[$key]['nit_tercero'];

                        // fin api terceros

                        $saldo_rp = $ce['valor'] - $ce['val_pagado'];

                        if ((intval($permisos['editar'])) === 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="cargarListaDetallePago(' . $id_doc . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></a>';
                        } else {
                            $editar = null;
                            $detalles = null;
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
                                <td class="text-right"><?php echo number_format($saldo_rp, 2, ',', '.'); ?></td>

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
        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Guardar</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Aceptar</a>
    </div>
</div>
<?php
$cmd = null;
