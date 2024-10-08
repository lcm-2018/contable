<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../terceros.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                `tes_causa_arqueo`.`id_causa_arqueo`
                ,`ctb_doc`.`id_ctb_doc`
                ,`ctb_doc`.`id_manu`
                , `ctb_doc`.`fecha`
                , `ctb_doc`.`id_tercero`
                , `ctb_doc`.`detalle`
                , SUM(`tes_causa_arqueo`.`valor_arq`) as valor
            FROM
                `tes_causa_arqueo`
                INNER JOIN `ctb_doc` 
                    ON (`tes_causa_arqueo`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`tes_causa_arqueo`.`estado` =0) 
            GROUP BY `ctb_doc`.`id_manu`;";
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
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
            <h5 style="color: white;">LISTA DE ARQUEOS DE CAJA PENDIENTE CONSIGNACION</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableObligacionesPago" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
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
                    $id_t = [];
                    foreach ($listado as $rp) {
                        if ($rp['id_tercero'] !== null) {
                            $id_t[] = $rp['id_tercero'];
                        }
                    }
                    $ids = implode(',', $id_t);
                    $terceros = getTerceros($ids, $cmd);
                    foreach ($listado as $ce) {
                        $id_doc = $ce['id_ctb_doc'];
                        $fecha = date('Y-m-d', strtotime($ce['fecha']));
                        // Consulta terceros en la api
                        $key = array_search($ce['id_tercero'], array_column($terceros, 'id_tercero_api'));
                        $tercero = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
                        $ccnit = $key !== false ? $terceros[$key]['nit_tercero'] : '---';
                        // fin api terceros

                        if ((intval($permisos['editar'])) === 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="cargarListaArqueoConsignacion(' . $id_doc . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></a>';
                            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            ...
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Historial</a>
                            </div>';
                        } else {
                            $editar = null;
                            $detalles = null;
                        }
                    ?>
                        <tr>
                            <td class="text-left"><?php echo $ce['id_manu']  ?></td>
                            <td class="text-left"><?php echo $fecha;  ?></td>
                            <td class="text-left"><?php echo $tercero;   ?></td>
                            <td class="text-left"><?php echo $ccnit; ?></td>
                            <td class="text-right"><?php echo number_format($ce['valor'], 2, ',', '.') ?></td>
                            <td class=" text-center"> <?php echo $editar .  $acciones; ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Procesar lote</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>
<?php
$cmd = null;
