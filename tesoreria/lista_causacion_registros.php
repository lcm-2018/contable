<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
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
            FROM
                `nom_nomina_pto_ctb_tes`
                INNER JOIN `nom_nominas` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE (`nom_nominas`.`estado` = 4) AND`nom_nomina_pto_ctb_tes`.`tipo` <> 'PL'
            UNION 
            SELECT
                `nom_nomina_pto_ctb_tes`.`id`
                , `nom_nomina_pto_ctb_tes`.`id_nomina`
                , `nom_nomina_pto_ctb_tes`.`tipo`
                , `nom_nomina_pto_ctb_tes`.`cdp`
                , `nom_nomina_pto_ctb_tes`.`crp`
                , `nom_nomina_pto_ctb_tes`.`cnom`
                , `nom_nominas`.`descripcion`
                , `nom_nominas`.`mes`
                , `nom_nominas`.`vigencia`
                , `nom_nominas`.`planilla` AS `estado`
            FROM
                `nom_nomina_pto_ctb_tes`
                INNER JOIN `nom_nominas` 
                    ON (`nom_nomina_pto_ctb_tes`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE (`nom_nominas`.`planilla` = 4 AND `nom_nomina_pto_ctb_tes`.`tipo` = 'PL')";
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
                                <td class="text-left"><?php echo $nm['descripcion'] . ' ' . $pl . ', NÓMINA No. ' . $nm['id_nomina'] . ' DE ' . $vigencia ?></td>
                                <td class="text-left"><?php echo ''  ?></td>
                                <td class="text-left"><?php echo '' ?></td>
                                <td class="text-left"><?php echo '' ?></td>
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