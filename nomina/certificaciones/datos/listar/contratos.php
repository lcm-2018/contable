<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
$cedula = isset($_POST['cc']) ? $_POST['cc'] : exit('Acceso denegado');
$fini = $_POST['fini'] == '' ? '1900-01-01' : $_POST['fini'];
$ffin = $_POST['ffin'] == '' ? '2999-12-31' : $_POST['ffin'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `ctt_contratos`.`id_contrato_compra`
                , `ctt_contratos`.`fec_ini`
                , `ctt_contratos`.`fec_fin`
                , `ctt_contratos`.`num_contrato`
                , `seg_terceros`.`no_doc`
                , `tb_area_c`.`area`
            FROM
                `ctt_contratos`
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                INNER JOIN `tb_area_c` 
                    ON (`ctt_adquisiciones`.`id_area` = `tb_area_c`.`id_area`)
                INNER JOIN `seg_terceros` 
                    ON (`ctt_adquisiciones`.`id_tercero` = `seg_terceros`.`id_tercero`)
            WHERE (`seg_terceros`.`no_doc` = '$cedula' AND (`ctt_contratos`.`fec_ini` BETWEEN '$fini' AND '$ffin' OR `ctt_contratos`.`fec_fin` BETWEEN '$fini' AND '$ffin'))";
    $rs = $cmd->query($sql);
    $contratos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<script>
    var setIdioma = {
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
        }
    };
    setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    $('#tbListaContratos').DataTable({
        language: setIdioma,
        dom: setdom,
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, 'TODO'],
        ],
        "pageLength": -1,
        "order": [
            [0, "desc"]
        ]
    });
</script>
<div class="table-responsive" style="max-height: 300px;">
    <table id="tbListaContratos" class="table table-striped table-bordered table-sm nowrap table-hover shadow p-2" style="width:100%">
        <thead>
            <div class="scroll-vertical">
                <tr>
                    <th>
                        <div class="text-center"><input type="checkbox" id="selectAll" class="check" title="Desmarcar todos" checked></div>
                    </th>
                    <th>Num.</th>
                    <th>Area</th>
                    <th>Inicia</th>
                    <th>Termina</th>
                </tr>
        </thead>
        <tbody class="text-left">
            <?php
            $tabla = '';
            foreach ($contratos as $c) {
                $id_contrato = $c['id_contrato_compra'];
                $num_contrato = $c['num_contrato'];
                $area = $c['area'];
                $fec_ini = $c['fec_ini'];
                $fec_fin = $c['fec_fin'];
                $tabla .= '<tr>';
                $tabla .= '<td><div class="text-center listado"><input type="checkbox" class="check" name="contrato[' . $id_contrato . ']" checked></div></td>';
                $tabla .= '<td>' . $num_contrato . '</td>';
                $tabla .= '<td>' . $area . '</td>';
                $tabla .= '<td>' . $fec_ini . '</td>';
                $tabla .= '<td>' . $fec_fin . '</td>';
                $tabla .= '</tr>';
            }
            echo $tabla;
            ?>
        </tbody>
    </table>
</div>