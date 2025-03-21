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
$id_cop = isset($_POST['id_cop']) ? $_POST['id_cop'] : 0;
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT
                `t1`.`id_pto_cop_det`
                , SUM(`t1`.`val_cop`) AS `val_cop`
                , SUM(`t1`.`val_pag`) AS `val_pag`
                , `t1`.`id_manu`
                , `t1`.`id_ctb_doc`
                , `t1`.`fecha`
            FROM 
                (SELECT
                    `pto_cop_detalle`.`id_pto_cop_det`
                    , IFNULL(`pto_cop_detalle`.`valor`,0) - IFNULL(`pto_cop_detalle`.`valor_liberado`,0) AS `val_cop`
                    , IFNULL(`pagado`.`val_pag`,0) AS `val_pag` 
                    , `ctb_doc`.`id_manu`
                    , `ctb_doc`.`id_ctb_doc`
                    , `ctb_doc`.`fecha`
                FROM
                    `pto_cop_detalle`
                INNER JOIN `ctb_doc` 
                    ON (`pto_cop_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                LEFT JOIN 
                    (SELECT
                        `id_pto_cop_det`
                        , SUM(IFNULL(`valor`,0) - IFNULL(`valor_liberado`,0)) AS `val_pag`
                    FROM
                        `pto_pag_detalle`
                        INNER JOIN `ctb_doc` 
                            ON (`pto_pag_detalle`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
                    WHERE (`id_tercero_api` = $id_tercero AND `ctb_doc`.`estado` = 2)
                    GROUP BY `id_pto_cop_det`) AS `pagado`
                    ON (`pto_cop_detalle`.`id_pto_cop_det` = `pagado`.`id_pto_cop_det`)
                WHERE `pto_cop_detalle`.`id_tercero_api` = $id_tercero AND `ctb_doc`.`estado` = 2) AS `t1`
            WHERE `val_cop` > `val_pag`
            GROUP BY `t1`.`id_ctb_doc`";
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
        <div class="px-3 pt-2">
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

                            if (PermisosUsuario($permisos, 5601, 3) || $id_rol == 1) {
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
                            $saldo = $ce['val_cop'] - $ce['val_pag'];
                            if ($saldo == 0) {
                                $editar = null;
                            }
                            $fecha_doc = date('Y-m-d',  strtotime($fecha));
                        ?>
                            <tr id="<?php echo $id; ?>">
                                <td class="text-left"><?php echo $ce['id_manu']; ?></td>
                                <td class="text-left"><?php echo $fecha_doc;  ?></td>
                                <td class="text-right">$ <?php echo number_format($ce['val_cop'], 2, '.', ','); ?></td>
                                <td class="text-right">$ <?php echo number_format($ce['val_pag'], 2, '.', ','); ?></td>
                                <td> <?php echo $editar .  $acciones; ?></td>

                            </tr>
                        <?php
                        }
                        ?>
                    </div>
                </tbody>
            </table>
            <div class="text-right py-3">
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
            </div>

        </div>


    </div>
    <?php
