<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
unset($_SESSION['id_doc']);
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_nomina_pto_ctb_tes`.`id`
                , `nom_nomina_pto_ctb_tes`.`id_nomina`
                , `nom_nomina_pto_ctb_tes`.`tipo`
                , `nom_nomina_pto_ctb_tes`.`cdp`
                , `nom_nomina_pto_ctb_tes`.`crp`
                , `nom_nomina_pto_ctb_tes`.`cnom`
                , `nom_nominas`.`descripcion`
                , `nom_nominas`.`mes`
                , `nom_nominas`.`vigencia`
                , `nom_nominas`.`estado`
                , DATE_FORMAT(`ctb_doc`.`fecha`, '%Y-%m-%d') AS `fecha`
                , `tb_terceros`.`nom_tercero`
                , `valores`.`valor`
            FROM
                `nom_nominas`
                INNER JOIN `nom_nomina_pto_ctb_tes` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
                LEFT JOIN `ctb_doc`
                    ON (`nom_nomina_pto_ctb_tes`.`cnom` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN `tb_terceros`
                    ON (`ctb_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                LEFT JOIN 
                    (SELECT
                        `id_ctb_doc`
                        , SUM(`valor`) AS `valor` 
                    FROM
                        `pto_cop_detalle`
                    GROUP BY `id_ctb_doc`) AS `valores`
                    ON (`nom_nomina_pto_ctb_tes`.`cnom` = `valores`.`id_ctb_doc`)
            WHERE `nom_nominas`.`estado` = 4 OR (`nom_nominas`.`planilla` = 4 AND `nom_nomina_pto_ctb_tes`.`tipo` = 'PL')";
    $rs = $cmd->query($sql);
    $nominas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableContrtacionRp').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ],
        columnDefs: [{
            class: 'text-wrap',
            targets: [1],
        }],

    });
    $('#tableContrtacionCdp').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE REGISTROS PRESUPUESTALES PARA OBLIGACION </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableContrtacionRp" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                        <th>Terceros</th>
                        <th>Valor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($nominas)) {
                        foreach ($nominas as $nm) {
                            $ids = $nm['cdp'] . '|' . $nm['crp'] . '|' . $nm['cnom'];
                            $id_nomina = $nm['id_nomina'];
                            $pl = $nm['tipo'] == 'PL' ? 'PATRONALES' : '';
                            $causar = '<button text="' . base64_encode($id_nomina . '|' . $nm['tipo'] . '|' . $ids) . '" onclick="CausaCENomina(this)" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></button>';
                    ?>
                            <tr>
                                <td class="text-center"><?php echo $id_nomina ?></td>
                                <td class="text-left" style="min-width: 300PX;"><?php echo $nm['descripcion'] . ' ' . $pl . ', NÓMINA No. ' . $nm['id_nomina'] . ' DE ' . $vigencia ?></td>
                                <td class="text-left"><?php echo '<input type="date" name="fec_doc[]" class="form-control form-control-sm" value="' . $nm['fecha'] . '" min="' . $nm['fecha'] . '" max="' . $vigencia . '-12-31">' ?></td>
                                <td class="text-left"><?php echo $nm['nom_tercero'] ?></td>
                                <td class="text-left text-right"><?php echo '$ ' . number_format($nm['valor'], 2, ',', '.') ?></td>
                                <td class="text-center"> <?php echo $causar ?></td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="7" class="text-center">No hay registros</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
    </div>
</div>