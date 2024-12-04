<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../financiero/consultas.php';
include '../terceros.php';

$id_caja = isset($_POST['id_caja'])  ? $_POST['id_caja'] : exit('Acceso no permitido');
$id_detalle = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : 0;


$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);


$fecha_cierre = fechaCierre($_SESSION['vigencia'], 6, $cmd);
$fecha = fechaSesion($_SESSION['vigencia'], $_SESSION['id_user'], $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));

try {
    $sql = "SELECT
                    `id_caja_respon`,`id_caja_const`,`id_terceros_api`,`fecha_ini`,`fecha_fin`,`estado`
                FROM `tes_caja_respon`
                WHERE `id_caja_const` = $id_caja";
    $rs = $cmd->query($sql);
    $responsables = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$reg = 0;
$id_t = [];
$terceros = [];
if (!empty($responsables)) {
    foreach ($responsables as $r) {
        if ($r['estado'] == '1') {
            $reg = 1;
        }
        if ($r['id_terceros_api'] != '') {
            $id_t[] = $r['id_terceros_api'];
        }
    }
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);
}

//detalle 
try {
    $sql = "SELECT
                    `id_caja_respon`,`id_caja_const`,`id_terceros_api`,`fecha_ini`,`fecha_fin`,`estado`
                FROM `tes_caja_respon`
                WHERE `id_caja_respon` = $id_detalle";
    $rs = $cmd->query($sql);
    $detalle = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (empty($detalle)) {
    $detalle = [
        'id_caja_respon' => 0,
        'id_caja_const' => $id_caja,
        'id_terceros_api' => 0,
        'fecha_ini' => $fecha,
        'fecha_fin' => $fecha,
        'estado' => 1
    ];
}
$key = array_search($detalle['id_terceros_api'], array_column($terceros, 'id_tercero_api'));
$nombre = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
?>
<script>
    $('#tableResponsableCaja').DataTable({
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
    $('#tableResponsableCaja').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">RESPONSABLE MANEJO DE CAJA MENOR </h5>
        </div>
        <div class="p-3">
            <form id="formAddResponsableCaja">
                <input type="hidden" id="id_caja" name="id_caja" value="<?php echo $id_caja; ?>">
                <input type="hidden" id="id_detalle" name="id_detalle" value="<?php echo $id_detalle; ?>">
                <input type="hidden" id="reg" value="<?php echo $reg; ?>">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="tercerocrp" class="small">RESPONSABLE</label>
                        <input type="text" id="tercerocrp" class="form-control form-control-sm" value="<?php echo $nombre ?>" required>
                        <input type="hidden" name="id_tercero" id="id_tercero" value="<?php echo $detalle['id_terceros_api']; ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_ini" class="small">FECHA INICIAL</label>
                        <input type="date" name="fecha_ini" id="fecha_ini" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $detalle['fecha_ini']; ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_fin" class="small">FECHA FINAL</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo  $detalle['fecha_fin']; ?>">
                    </div>
                </div>
            </form>
            <table id="tableResponsableCaja" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%">
                <thead>
                    <tr>
                        <th>Responsable</th>
                        <th>Fecha inicial</th>
                        <th>Fecha final</th>
                        <th>Estado</th>
                        <th>Acciones </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($responsables as $r) {
                        $key = array_search($r['id_terceros_api'], array_column($terceros, 'id_tercero_api'));
                        $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
                        $editar = '<a class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  onclick="EditResponsableCaja(' . $r['id_caja_respon'] . ')"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                        $estado =  $r['estado'];
                        if ($estado == 1) {
                            $title = 'Activo';
                            $icono = 'on';
                            $color = '#37E146';
                        } else {
                            $title = 'Inactivo';
                            $icono = 'off';
                            $color = 'gray';
                        }
                        $boton = '<a class="btn btn-sm btn-circle estado" title="' . $title . '" onclick="ModEstadoResposableCaja(' . $r['id_caja_respon'] . ',' . $estado . ')"><span class="fas fa-toggle-' . $icono . ' fa-2x" style="color:' . $color . ';"></span></a>';
                        echo '<tr>';
                        echo '<td class="text-left">' . $tercero . '</td>';
                        echo '<td>' . $r['fecha_ini'] . '</td>';
                        echo '<td>' . $r['fecha_fin'] . '</td>';
                        echo '<td>' . $boton . '</td>';
                        echo '<td>' . $editar . '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
            <div class="text-right pt-3">
                <a type="button" class="btn btn-success btn-sm" onclick="GuardaRespCaja()">Guardar</a>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
            </div>

        </div>


    </div>