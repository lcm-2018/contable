<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no permitido');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_referencia`.`id_ctb_referencia`
                , `ctb_referencia`.`id_cuenta`
                , `ctb_referencia`.`nombre`
                , `ctb_referencia`.`accion`
                , `ctb_referencia`.`estado`
                , IF(`pgcp`.`nombre` IS NULL, `ctb_pgcp`.`cuenta`,CONCAT(`ctb_pgcp`.`cuenta`,' - ', `pgcp`.`cuenta`)) AS `nom_cuenta`
            FROM `ctb_referencia`
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_referencia`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                LEFT JOIN `ctb_pgcp` AS `pgcp` 
                    ON (`ctb_referencia`.`id_cta_credito` = `pgcp`.`id_pgcp`)
            WHERE `ctb_referencia`.`id_ctb_fuente` = $id_doc";
    $rs = $cmd->query($sql);
    $referencias = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<script>
    function FormDocReferencia(id_doc_ref, id_ctb_ref) {
        $.post("datos/registrar/form_doc_referencia.php", {
            id_doc_ref: id_doc_ref,
            id_ctb_ref: id_ctb_ref
        }, function(he) {
            $("#divTamModalReg").removeClass("modal-xl");
            $("#divTamModalReg").removeClass("modal-sm");
            $("#divTamModalReg").removeClass("modal-lg");
            $("#divModalReg").modal("show");
            $("#divFormsReg").html(he);
        });
    }
    $('#tableDocRefs').DataTable({
        language: setIdioma,
        dom: setdom,
        buttons: [{
            text: ' <span class="fas fa-plus-circle fa-lg"></span>',
            action: function(e, dt, node, config) {
                FormDocReferencia($('#id_doc_ft').val(), 0);
            },
        }, ],
        "pageLength": 10,
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableDocRefs').wrap('<div class="overflow" />');

    function EditarDocReferencia(id_ctb_ref) {
        FormDocReferencia($('#id_doc_ft').val(), id_ctb_ref);
    }
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">DOCUMENTOS DE REFERENCIA</b></h5>
        </div>
        <input type="hidden" name="id_doc_ft" id="id_doc_ft" value="<?php echo $id_doc; ?>">
        <div class="px-3 py-3 text-left">
            <table id="tableDocRefs" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">Cuenta</th>
                        <th class="text-center">Nombre</th>
                        <th class="text-center">Acción</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($referencias)) {
                        foreach ($referencias as $ref) {
                            $cerrar = $editar = $borrar = null;
                            $id_ctb = $ref['id_ctb_referencia'];
                            if ($ref['estado'] == 1) {
                                $estado = '<span class="badge badge-success">Activa</span>';
                            } else {
                                $estado = '<span class="badge badge-secondary">Inactiva</span>';
                            }
                            if ($ref['accion'] == '1') {
                                $accion = '<span class="badge badge-primary">Ingreso</span>';
                            } else {
                                $accion = '<span class="badge badge-warning">Gasto</span>';
                            }
                            if ($ref['estado'] == 1) {
                                $cerrar = '<button value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb" onclick="cerrarReferencia(' . $id_ctb . ')" title="Desactivar Referencia"><span class="fas fa-unlock fa-lg"></span></button>';
                            } else {
                                $cerrar = '<button value="' . $id_ctb . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb" onclick="abrirReferencia(' . $id_ctb . ')" title="Activar Referencia"><span class="fas fa-lock fa-lg"></span></button>';
                            }
                            if ((PermisosUsuario($permisos, 5505, 3) || $id_rol == 1) && ($ref['estado'] == 1)) {
                                $editar = '<button onclick="EditarDocReferencia(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar Referencia"><span class="fas fa-pencil-alt fa-lg"></span></button>';
                            }
                            if (PermisosUsuario($permisos, 5505, 4) || $id_rol == 1 && ($ref['estado'] == 1)) {
                                $borrar = '<button value="' . $id_ctb . '" onclick="eliminarReferencia(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
                            }
                            echo '<tr>
                                <td class="text-right">' . $ref['nom_cuenta'] . '</td>
                                <td>' . $ref['nombre'] . '</td>
                                <td class="text-center">' . $accion . '</td>
                                <td class="text-center">' . $estado . '</td>
                                <td class="text-center">' . $editar . $cerrar . $borrar . '</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="text-right pb-3 px-4 w-100">
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>