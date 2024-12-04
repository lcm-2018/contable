<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
$id_ctb_doc = $_POST['id_doc'] ?? '';
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT
                `pto_documento_detalles`.`id_detalle`
                , CONCAT(`pto_documento_detalles`.`rubro`,' ', `pto_cargue`.`nom_rubro`) AS rubros
                , `pto_documento_detalles`.`valor`
                , `pto_documento_detalles`.`id_documento`
            FROM
                `pto_cargue`
                INNER JOIN `pto_documento_detalles` 
                    ON (`pto_cargue`.`cod_pptal` = `pto_documento_detalles`.`rubro`)
            WHERE (`pto_cargue`.`vigencia` ={$_SESSION['vigencia']}
            AND `pto_documento_detalles`.`id_ctb_doc` =$id_ctb_doc);";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el id_pto_doc
try {
    $sql = "SELECT
               `pto_documento_detalles`.`id_documento`
            FROM
                `pto_documento_detalles` 
            WHERE `pto_documento_detalles`.`id_ctb_doc` =$id_ctb_doc LIMIT 1";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $id_ctb_doc = $datos['id_pto_doc'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<script>
    $('#tableCausacionIng').DataTable({
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
    $('#tableCausacionIng').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE AFECTACION PRESUPUESTAL DE INGRESOS </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-5">
            <form id="formAddFormaIng">
                <div class="row">
                    <div class="col-8">
                        <div class="col"><label for="numDoc" class="small">RUBRO:</label></div>
                    </div>

                    <div class="col-2">
                        <div class="col"><label for="numDoc" class="small">DOCUMENTO:</label></div>
                    </div>
                    <div class="col-2">
                        <div class="col"><label for="numDoc" class="small">VALOR:</label></div>
                    </div>
                </div>
                <div class="row">

                    <div class="col-8">
                        <div class="col" id="divBanco">
                            <input type="text" name="rubroIng" id="rubroIng" class="form-control form-control-sm" value="">
                            <input type="hidden" name="id_rubroIng" id="id_rubroIng" class="form-control form-control-sm" value="">
                            <input type="hidden" name="tipoRubro" id="tipoRubro" class="form-control form-control-sm" value="">
                            <input type="hidden" name="id_pto_doc" id="id_pto_doc" class="form-control form-control-sm" value="<?php echo $id_ctb_doc; ?>">
                        </div>
                    </div>

                    <div class="col-2">
                        <div class="col" id="divCosto"><input type="text" name="documento" id="documento" class="form-control form-control-sm" value="" required></div>
                    </div>
                    <div class="col-2">
                        <div class="btn-group"><input type="text" name="valor_ing" id="valor_ing" class="form-control form-control-sm" max="" value="" required style="text-align: right;" onkeyup="valorMiles(id)" ondblclick="valorMovTeroreria('');">
                            <button type="button" class="btn btn-primary btn-sm" onclick="registrarPresupuestoIng()">+</button>
                        </div>
                    </div>
                </div>

            </form> <br>
            <table id="tableCausacionIng" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-70">Rubro</th>
                        <th class="w-20">Valor</th>
                        <th class="w-10">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        if (!empty($rubros)) {
                            foreach ($rubros as $ce) {
                                //$id_doc = $ce['id_ctb_doc'];
                                $id = $ce['id_pto_mvto'];
                                if ((intval($permisos['editar'])) === 1) {
                                    $editar = '<a value="' . $id . '" onclick="eliminaRubroIng(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                                    $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            ...
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a value="' . $id . '" class="dropdown-item sombra carga" href="#">Historial</a>
                            </div>';
                                } else {
                                    $editar = null;
                                    $detalles = null;
                                }

                        ?>
                                <tr id="<?php echo $id; ?>">
                                    <td class="text-left"><?php echo $ce['rubros']; ?></td>
                                    <td class="text-right"> <?php echo number_format($ce['valor'], 2, '.', ','); ?></td>
                                    <td> <?php echo $editar .  $acciones; ?></td>

                                </tr>
                        <?php
                            }
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
