<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Consultar el valor a de los descuentos realizados a la cuenta de ctb_causa_retencion
try {
    $sql = "SELECT
                tes_referencia.id_referencia
                , tes_referencia.numero
                , tes_referencia.fec_reg
                , SUM(ctb_libaux.credito) as valor
                , tes_referencia.estado
            FROM
                ctb_libaux
            INNER JOIN ctb_doc ON (ctb_libaux.id_ctb_doc = ctb_doc.id_ctb_doc)
            INNER JOIN tes_referencia ON (tes_referencia.numero = ctb_doc.id_plano)";
    $rs = $cmd->query($sql);
    $referencias = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableReferenciasPagos').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableReferenciasPagos').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE REFERENCIAS DE PAGO </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-5">
            <table id="tableReferenciasPagos" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-20">Número</th>
                        <th class="w-20">Fecha</th>
                        <th class="w-20">Estado</th>
                        <th class="w-20">Valor</th>
                        <th class="w-20">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        foreach ($referencias as $ce) {
                            //$id_doc = $ce['id_ctb_doc'];
                            $id = $ce['id_referencia'];
                            if ((intval($permisos['editar'])) === 1) {
                                $editar = '<a value="' . $id . '" onclick="eliminarReferenciaPago(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                                $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            ...
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a value="' . $id . '" class="dropdown-item sombra carga" href="#" onclick="terminarReferenciaPago(' . $id . ')">Terminar</a>
                            </div>';
                            } else {
                                $editar = null;
                                $detalles = null;
                            }
                            if ((intval($permisos['listar'])) === 1) {
                                $imprimir = '<a value="' . $id . '" onclick="imprimirReferenciaPago(' . $id . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb " title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
                            } else {
                                $imprimir = null;
                            }
                            // Establecer formato fecha a $ce[fec_act]
                            $fecha = date('Y-m-d', strtotime($ce['fec_reg']));
                        ?>
                            <tr id="<?php echo $id; ?>">
                                <td><?php echo $ce['numero']; ?></td>
                                <td><?php echo $fecha; ?></td>
                                <td> <?php echo $ce['estado']; ?></td>
                                <td> <?php echo number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td> <?php echo $imprimir . $editar .  $acciones; ?></td>

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
