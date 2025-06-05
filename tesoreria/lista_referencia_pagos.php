<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';

$vigencia = $_SESSION['vigencia'];
$inicio = $vigencia . '-01-01';
$fin = $vigencia . '-12-31';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Consultar el valor a de los descuentos realizados a la cuenta de ctb_causa_retencion
try {
    $sql = "SELECT
                `tes_referencia`.`id_referencia`
                , `tes_referencia`.`numero`
                ,  DATE_FORMAT(`tes_referencia`.`fec_reg`, '%Y-%m-%d') AS `fec_reg`
                , `tes_referencia`.`estado`
                , SUM(`t1`.`valor`) AS `valor`
            FROM
                `tes_referencia`
                LEFT JOIN `ctb_doc` 
                    ON (`ctb_doc`.`id_ref` = `tes_referencia`.`id_referencia` AND `ctb_doc`.`estado` = 2)
                LEFT JOIN 
                (SELECT `id_ctb_doc`, SUM(`credito`) AS `valor`
                FROM `ctb_libaux`
                GROUP BY `id_ctb_doc`)AS`t1` 
                    ON (`t1`.`id_ctb_doc` = `ctb_doc`.`id_ctb_doc`)
            WHERE (DATE_FORMAT(`tes_referencia`.`fec_reg`, '%Y-%m-%d') BETWEEN '$inicio' AND '$fin')
            GROUP BY `tes_referencia`.`id_referencia`";
    $rs = $cmd->query($sql);
    $referencias = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<script>
    $('#tableReferenciasPagos').DataTable({
        dom: setdom,
        language: setIdioma,
        buttons: [{
            text: ' <span class="fas fa-plus-circle fa-lg"></span>',
            action: function(e, dt, node, config) {
                GetFormRefPago(0);
            },
        }, ],
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableReferenciasPagos').wrap('<div class="overflow" />');
    // remover de tableReferenciasPagos_filter el elemento label
    $('#tableReferenciasPagos_filter #verAnulados').remove();
    $('#tableReferenciasPagos_filter label label').remove();

    function editarReferenciaPago(id) {
        GetFormRefPago(id);
    }
    GetFormRefPago = function(id) {
        $.post("datos/registrar/form_referencia_pago.php", {
            id: id
        }, function(he) {
            $("#divTamModalAux").removeClass("modal-lg");
            $("#divTamModalAux").removeClass("modal-xl");
            $("#divTamModalAux").addClass("modal-sm");
            $("#divModalAux").modal("show");
            $("#divFormsAux").html(he);
        });
    }
</script>
<div class="px-0 text-left">

    <div class="shadow">
        <div class="card-header text-center" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE REFERENCIAS DE PAGO </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">
            <table id="tableReferenciasPagos" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Número</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Valor</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($referencias)) {
                        foreach ($referencias as $ce) {
                            $id = $ce['id_referencia'];
                            if ((PermisosUsuario($permisos, 5601, 3) || PermisosUsuario($permisos, 5602, 3) || PermisosUsuario($permisos, 5603, 3) || PermisosUsuario($permisos, 5604, 3) || $id_rol == 1)) {
                                $editar = '<a value="' . $id . '" onclick="editarReferenciaPago(' . $id . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                            }
                            if ((PermisosUsuario($permisos, 5601, 4) || PermisosUsuario($permisos, 5602, 4) || PermisosUsuario($permisos, 5603, 4) || PermisosUsuario($permisos, 5604, 4) || $id_rol == 1)) {
                                $eliminar = '<a value="' . $id . '" onclick="eliminarReferenciaPago(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                            }
                            if ((PermisosUsuario($permisos, 5601, 6) || PermisosUsuario($permisos, 5602, 6) || PermisosUsuario($permisos, 5603, 6) || PermisosUsuario($permisos, 5604, 6) || $id_rol == 1)) {
                                $imprimir = '<a value="' . $id . '" onclick="imprimirReferenciaPago(' . $id . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb " title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
                            }

                            if ($ce['estado'] == '1') {
                                $estado = '<button onclick="CambiaEstadoReferencia(' . $id . ',0)" class="btn-estado btn btn-outline-success btn-sm btn-circle estado" title="Activo"><span class="fas fa-toggle-on fa-lg" aria-hidden="true"></span></button>';
                            } else {
                                $estado = '<button onclick="CambiaEstadoReferencia(' . $id . ',1)" class="btn-estado btn btn-outline-secondary btn-sm btn-circle estado" title="Inactivo"><span class="fas fa-toggle-off fa-lg" aria-hidden="true"></span></button>';
                            }
                            if ($ce['estado'] == '0') {
                                $editar =  $eliminar = '';
                            } else {
                                $id = '<span>' . $id . '</span>';
                            }
                    ?>
                            <tr id="<?= $id; ?>" class="text-center">
                                <td><?= $id; ?></td>
                                <td><?= $ce['numero']; ?></td>
                                <td><?= $ce['fec_reg']; ?></td>
                                <td> <?= $estado; ?></td>
                                <td> <?= number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td> <?= $editar . $imprimir .  $eliminar; ?></td>

                            </tr>
                    <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
            <div class="text-right py-3">
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
            </div>

        </div>


    </div>
    <?php
