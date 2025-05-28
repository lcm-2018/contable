<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';

$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : 0;
$id_tercero = isset($_POST['id_tercero']) ? $_POST['id_tercero'] : 0;
$id_doc_rad = isset($_POST['id_doc_rad']) ? $_POST['id_doc_rad'] : 0;
$id_vigencia = $_SESSION['id_vigencia'];
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT
                `ctb_doc`.`id_ctb_doc`
                , `ctb_doc`.`id_manu`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`estado`
                , DATE_FORMAT(`ctb_doc`.`fecha`,'%Y-%m-%d') AS `fecha`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `causado`.`valor` AS `val_causado`
                , 0 AS `val_recaudado`
            FROM
                `ctb_doc`
                LEFT JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN
                    (SELECT
                        `id_ctb_doc`, SUM(`debito`) AS `valor`
                    FROM
                        `ctb_libaux`
                    GROUP BY `id_ctb_doc`) AS `causado`
                ON (`causado`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (`ctb_fuente`.`cod` = 'FELE' AND `ctb_doc`.`id_tercero` = $id_tercero AND `ctb_doc`.`id_vigencia` = $id_vigencia AND `ctb_doc`.`estado` = 2)";
    $rs = $cmd->query($sql);
    $causaciones = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<script>
    $('#tableCausacionPagos').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ]

    });
    $('#tableCausacionPagos').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE CAUSACIONES PARA PAGO DEL TERCERO</h5>
        </div>
        <div class="px-3 pt-2">
            <table id="tableCausacionPagos" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-15">No causación</th>
                        <th class="w-30">Fecha</th>
                        <th class="w-10">Valor Causado</th>
                        <th class="w-10">Valor Recaudado</th>
                        <th class="w-5">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        foreach ($causaciones as $ce) {
                            $id = $ce['id_ctb_doc'];
                            $fecha = $ce['fecha'];
                            $editar = null;
                            if (PermisosUsuario($permisos, 5601, 2) || $id_rol == 1) {
                                $editar = '<button value="' . $id_doc . '" onclick="cargaRubroPagInvoice(' . $id . ',this)" class="btn btn-outline-info btn-sm btn-circle shadow-gb" title="Imputar"><span class="fas fa-chevron-circle-down fa-lg"></span></a>';
                            }

                            $saldo = $ce['val_causado'] - $ce['val_recaudado'];
                            if (!($saldo > 0)) {
                                $editar = null;
                            }
                        ?>
                            <tr id="<?= $id; ?>">
                                <td class="text-left"><?= $ce['id_manu']; ?></td>
                                <td class="text-left"><?= $fecha;  ?></td>
                                <td class="text-right">$ <?= number_format($ce['val_causado'], 2, '.', ','); ?></td>
                                <td class="text-right">$ <?= number_format($ce['val_recaudado'], 2, '.', ','); ?></td>
                                <td> <?= $editar; ?></td>

                            </tr>
                        <?php
                        }
                        ?>
                    </div>
                </tbody>
            </table>
            <div id="detalle-rubros">

            </div>
            <div class="text-right py-3">
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
            </div>

        </div>


    </div>
    <?php
