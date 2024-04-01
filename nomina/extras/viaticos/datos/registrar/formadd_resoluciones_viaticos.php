<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
include '../../../../../permisos.php';
$vigencia = $_SESSION['vigencia'];
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`, `no_documento`, CONCAT_WS(' ',`apellido1`, `apellido2`, `nombre1`, `nombre2`) AS `nombre_completo`
            FROM
                `nom_empleado`
            WHERE `estado` = '1'";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$datos = [];
foreach ($empleados as $e) {
    $datos[] = array(
        'check' => '<div class="text-center listado"><input type="checkbox" name="check[]" value="' . $e['id_empleado'] . '" class="check"/></div>',
        'no_doc' => $e['no_documento'],
        'nombre' => mb_strtoupper($e['nombre_completo']),
        'fec_inicia' => '<div class="text-center"><input type="date" name="fec_inicia_' . $e['id_empleado'] . '" id="fec_inicia_' . $e['id_empleado'] . '" class="form-control form-control-sm altura"/></div>',
        'fec_final' => '<div class="text-center"><input type="date" name="fec_final_' . $e['id_empleado'] . '" id="fec_final_' . $e['id_empleado'] . '" class="form-control form-control-sm altura"/></div>',
        'tot_dias' => '<div class="text-center"><input type="number" name="tot_dias_' . $e['id_empleado'] . '" id="tot_dias_' . $e['id_empleado'] . '" class="form-control form-control-sm altura"/></div>',
        'dias_pernocta' => '<div class="text-center"><input type="number" name="dias_pernocta_' . $e['id_empleado'] . '" id="dias_pernocta_' . $e['id_empleado'] . '" class="form-control form-control-sm altura"/></div>',
        'objetivo' => '<div class="text-center"><input type="text" name="objetivo_' . $e['id_empleado'] . '" id="objetivo_' . $e['id_empleado'] . '" class="form-control form-control-sm altura"/></div>',
        'destino' => '<div class="text-center"><input type="text" name="destino_' . $e['id_empleado'] . '" id="destino_' . $e['id_empleado'] . '" class="form-control form-control-sm altura"/></div>',
    );
}
?>
<script>
    $('#tableListaEmpleadoViaticos').DataTable({
        dom: "<'row'<'col-md-6'l><'col-md-6'f>>" +
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
            [2, "asc"]
        ]
    });
    $('#tableListaEmpleadoViaticos').wrap('<div class="overflow" />');
    $('#tableListaEmpleadoViaticos_length').addClass('text-left');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">EMPLEADOS PARA GENERAR RESOLUCIÓN DE VIÁTICOS</h5>
        </div>
        <div class="px-4 pt-3">
            <form id="formDatosResoluciones">
                <table id="tableListaEmpleadoViaticos" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="text-center">&nbsp&nbsp<input type="checkbox" id="selectAll">&nbsp</th>
                            <th class="text-center">No. Doc.</th>
                            <th class="text-center">Nombre Completo</th>
                            <th class="text-center">Fecha Inicia</th>
                            <th class="text-center">Fecha Finaliza</th>
                            <th class="text-center">Total Días</th>
                            <th class="text-center">Total<br>Días Pernocta</th>
                            <th class="text-center">Objetivo</th>
                            <th class="text-center">Ciudad Destino</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($datos as $d) { ?>
                            <tr>
                                <td><?php echo $d['check'] ?></td>
                                <td><?php echo $d['no_doc'] ?></td>
                                <td><?php echo $d['nombre'] ?></td>
                                <td><?php echo $d['fec_inicia'] ?></td>
                                <td><?php echo $d['fec_final'] ?></td>
                                <td><?php echo $d['tot_dias'] ?></td>
                                <td><?php echo $d['dias_pernocta'] ?></td>
                                <td><?php echo $d['objetivo'] ?></td>
                                <td><?php echo $d['destino'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button class="btn btn-primary btn-sm" id="btnGenerarResolucion">
            <i class="fas fa-file-contract"></i>
            GENERAR RESOLUCIONES
        </button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
    </div>
</div>