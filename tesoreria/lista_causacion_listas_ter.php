<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
$id_doc = $_POST['id_doc'] ?? '';
$ccnit = $_POST['ccnit'] ?? '';
$id_cop = $_POST['id_cop'] ?? '';
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT
    `pto_documento_detalles`.`id_ctb_doc`
    , `ctb_doc`.`fecha`
    , SUM(`pto_documento_detalles`.`valor`) as valor
    , `ctb_doc`.`id_tercero`
    , `ctb_doc`.`id_manu`
    , `pto_documento_detalles`.`estado`
FROM
    `ctb_doc`
    INNER JOIN `pto_documento_detalles` 
        ON (`ctb_doc`.`id_ctb_doc` = `pto_documento_detalles`.`id_ctb_doc`)
WHERE (`ctb_doc`.`id_tercero` =$ccnit 
    AND `pto_documento_detalles`.`tipo_mov` ='COP'
    AND `pto_documento_detalles`.`estado` =0)
GROUP BY `pto_documento_detalles`.`id_ctb_doc`;";
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
    $('#tableCausacionPagos').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE CAUSACIONES PARA PAGO DEL TERCERO</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-5">
            <table id="tableCausacionPagos" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-15">No causación</th>
                        <th class="w-30">Fecha</th>
                        <th class="w-10">Valor causado</th>
                        <th class="w-10">Valor Pagos</th>
                        <th class="w-5">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        foreach ($causaciones as $ce) {
                            $id = $ce['id_ctb_doc'];
                            $fecha = $ce['fecha'];
                            // Obtener el valor pagado asociado al documento
                            try {
                                $sql = "SELECT sum(valor) as valorpag FROM pto_documento_detalles WHERE id_ctb_cop =$id AND tipo_mov='PAG' AND estado <0";
                                $rs = $cmd->query($sql);
                                $sumacrp = $rs->fetch();
                                $valor_pagos = $sumacrp['valorpag'];
                            } catch (PDOException $e) {
                                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                            }

                            if ((intval($permisos['editar'])) === 1) {
                                $editar = '<a value="' . $id_doc . '" onclick="cargaRubrosPago(' . $id . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                                $borrar = '<a value="' . $id_doc . '" onclick="eliminarFormaPago(' . $id_doc . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
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
                            $saldo = $ce['valor'] - $valor_pagos;
                            if ($saldo == 0) {
                                $editar = null;
                            }
                            $fecha_doc = date('Y-m-d',  strtotime($fecha));
                        ?>
                            <tr id="<?php echo $id; ?>">
                                <td><?php echo $ce['id_manu']; ?></td>
                                <td><?php echo $fecha_doc;  ?></td>
                                <td> <?php echo number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td> <?php echo number_format($valor_pagos, 2, '.', ','); ?></td>
                                <td> <?php echo $editar .  $acciones; ?></td>

                            </tr>
                        <?php
                        }
                        ?>
                    </div>
                </tbody>
            </table>
            <div class="text-right pt-3">
                <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</a>


            </div>

        </div>


    </div>
    <?php
