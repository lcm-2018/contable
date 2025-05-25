<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../terceros.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`fecha`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `obligado`.`valor`
                , 0 As `valor_pagado`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN 
                (SELECT
                    `id_ctb_doc`
                    , SUM(`debito`) AS `valor`
                FROM
                    `ctb_libaux`
                GROUP BY `id_ctb_doc`) AS `obligado`
                ON (`ctb_doc`.`id_ctb_doc` = `obligado`.`id_ctb_doc`)
            WHERE (`ctb_doc`.`id_vigencia` = 9 AND `ctb_fuente`.`cod` = 'FELE' AND `ctb_doc`.`estado` = 2)";
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableObligacionesRads').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableObligacionesRads').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE OBLIGACIONES RECONOCIDAS</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableObligacionesRads" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 8%;">Num </th>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 35%;">Tercero</th>
                        <th style="width: 15%;">Doc</th>
                        <th style="width: 20%;">Valor</th>
                        <th style="width: 10%;">Acciones</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($listado as $ce) {
                        $obligar = null;
                        $id_doc = $ce['id_ctb_doc'];
                        $fecha = date('Y-m-d', strtotime($ce['fecha']));
                        $obligar = '<a value="' . $id_doc . '" onclick="cargarListaObligacionRads(' . $id_doc . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Obligar"><span class="fas fa-plus-square fa-lg"></span></a>';
                        $saldo = $ce['valor'] - $ce['valor_pagado'];
                        if ($saldo > 0) {
                    ?>
                            <tr>
                                <td class="text-left"><?= $ce['id_manu']  ?></td>
                                <td class="text-left"><?= $fecha;  ?></td>
                                <td class="text-left"><?= $ce['nom_tercero']; ?></td>
                                <td class="text-left"><?= $ce['nit_tercero']; ?></td>
                                <td class="text-right"><?= number_format($ce['valor'], 2, ',', '.') ?></td>
                                <td class=" text-center"> <?= $obligar ?></td>
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