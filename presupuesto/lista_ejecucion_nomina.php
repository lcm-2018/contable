<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
// Consulta tipo de presupuesto
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT '0' AS`patronal`, `id_nomina`, `estado`, `descripcion`, `mes`, `vigencia`, `tipo` FROM `nom_nominas` WHERE `estado` = 2
            UNION
            SELECT	
                    `t1`.`seg_patronal` + `t2`.`parafiscales` AS `patronal`
                    , `t1`.`id_nomina`
                    , `nom_nominas`.`planilla` AS estado
                    , `nom_nominas`.`descripcion`
                    , `nom_nominas`.`mes`
                    , `nom_nominas`.`vigencia`
                    , 'PL' AS `tipo`
            FROM
                    (SELECT
                        SUM(`aporte_salud_empresa`) + SUM(`aporte_pension_empresa`) + SUM(`aporte_rieslab`) AS `seg_patronal`
                        , `anio`
                        , `id_nomina`
                    FROM
                        `nom_liq_segsocial_empdo`
                    WHERE `anio` = '$vigencia'
                    GROUP BY `id_nomina`) AS`t1`
            LEFT JOIN 
                    (SELECT
                        SUM(`val_sena`) + SUM(`val_icbf`) + SUM(`val_comfam`) AS `parafiscales`
                        , `anio_pfis`
                        , `id_nomina` 
                    FROM
                        `nom_liq_parafiscales`
                    WHERE `anio_pfis` = '$vigencia'
                    GROUP BY `id_nomina`) AS `t2`
                    ON (`t1`.`id_nomina` = `t2`.`id_nomina`)
            INNER JOIN `nom_nominas` 
                    ON (`t1`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE `nom_nominas`.`planilla` = 2";
    $rs = $cmd->query($sql);
    $solicitudes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT SUM(`valor`) AS `total`, `id_nomina` FROM `nom_cdp_empleados` GROUP BY `id_nomina`";
    $rs = $cmd->query($sql);
    $totxnomina = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$meses = [
    '00' => '',
    '01' => 'ENERO',
    '02' => 'FEBRERO',
    '03' => 'MARZO',
    '04' => 'ABRIL',
    '05' => 'MAYO',
    '06' => 'JUNIO',
    '07' => 'JULIO',
    '08' => 'AGOSTO',
    '09' => 'SEPTIEMBRE',
    '10' => 'OCTUBRE',
    '11' => 'NOVIEMBRE',
    '12' => 'DICIEMBRE'
];
?>
<script>
    $('#tableContrtacionCdp').DataTable({
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
            <h5 style="color: white;">LISTA DE NÓMINA(S) PARA CDP</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableContrtacionCdp" class="table table-striped table-bordered  table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-15">ID</th>
                        <th class="w-60">DESCRIPCIÓN</th>
                        <th class="w-15">VALOR SOLICITADO</th>
                        <th class="w-10">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($solicitudes as $ce) {
                        $id_nom = $ce['id_nomina'];
                        $key = array_search($id_nom, array_column($totxnomina, 'id_nomina'));
                        $total = $key !== false ? $totxnomina[$key]['total'] : 0;
                        $patronal = '';
                        if ($ce['tipo'] == 'PL') {
                            $patronal = ' - PATRONAL';
                            $total = $ce['patronal'];
                        }
                        if (PermisosUsuario($permisos, 5401, 3) || $id_rol == 1) {
                            $editar = '<button value="' . $id_nom . '|' . $ce['tipo'] . '" onclick="CofirmaCdpRp(this)" class="btn btn-outline-primary btn-sm btn-circle shadow-gb confirmar" title="Confirmar Generación de CDP y RP"><span class="fas  fa-check-square fa-lg"></span></button>';
                        } else {
                            $editar = null;
                        }
                        $mesu = $ce['mes'] == '' ? '00' : $ce['mes'];
                    ?> <tr>
                            <td class="text-left"><?php echo $ce['id_nomina'] ?></td>
                            <td class="text-left"><?php echo $ce['descripcion'] . ' - ' . $meses[$mesu] . ' DE ' . $ce['vigencia'] . $patronal ?></td>
                            <td class="text-right">$ <?php echo number_format($total, 2, ',', '.') ?></td>
                            <td class="text-center"> <?php echo $editar ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>