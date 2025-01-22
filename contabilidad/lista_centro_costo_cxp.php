<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';

$id_doc = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');
$id_detalle = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_causa_costos`.`id`
                , `ctb_causa_costos`.`id_ctb_doc`
                , `ctb_causa_costos`.`valor`
                , `tb_sedes`.`nom_sede`
                , `tb_municipios`.`nom_municipio`
                , `far_centrocosto_area`.`nom_area` AS `descripcion`
            FROM
                `ctb_causa_costos`
                INNER JOIN `far_centrocosto_area` 
                    ON (`ctb_causa_costos`.`id_area_cc` = `far_centrocosto_area`.`id_area`)
                INNER JOIN `tb_sedes` 
                    ON (`far_centrocosto_area`.`id_sede` = `tb_sedes`.`id_sede`)
                INNER JOIN `tb_municipios` 
                    ON (`tb_sedes`.`id_municipio` = `tb_municipios`.`id_municipio`)
            WHERE (`ctb_causa_costos`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT SUM(`valor_pago`) AS `valor_pago` FROM `ctb_factura` WHERE (`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $valor_factura = $rs->fetch();
    $valor_max = !empty($valor_factura) ? $valor_factura['valor_pago'] : 0;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$val_cc = 0;
foreach ($rubros as $r) {
    $val_cc += $r['valor'];
}
$min = 0;
$max = $valor_max - $val_cc;
$max = $max < 0 ? 0 : $max;
?>
<script>
    $('#tableCausacionCostos').DataTable({
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
    $('#tableCausacionCostos').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow ">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE CENTROS DE COSTO DE CUENTA POR PAGAR </h5>
        </div>
        <div class="px-4">
            <form id="formGuardaCentroCosto" class="mb-3">
                <input type="hidden" name="id_doc" id="id_doc" value="<?php echo $id_doc; ?>">
                <input type="hidden" name="id_detalle" id="id_detalle" value="<?php echo $id_detalle; ?>">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="municipio" class="small">MUNICIPIO</label>
                        <input type="text" name="municipio" id="municipio" class="form-control form-control-sm" value="" onchange="mostrarSedes();" required>
                        <input type="hidden" name="id_municipio" id="id_municipio" value="0">
                    </div>
                    <div class="form-group col-md-3" id="divSede">
                        <label for="id_sede" class="small">SEDE</label>
                        <select type="text" name="id_sede" id="id_sede" class="form-control form-control-sm">
                            <option value="0">--Seleccione--</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3" id="divCosto">
                        <label for="id_cc" class="small">CENTRO DE COSTO</label>
                        <select type="text" name="id_cc" id="id_cc" class="form-control form-control-sm">
                            <option value="0">--Seleccione--</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="valor_cc" class="small">VALOR CC</label>
                        <input type="text" name="valor_cc" id="valor_cc" min="<?= $min; ?>" max="<?= $max; ?>" class="form-control form-control-sm" required style="text-align: right;" onkeyup="valorMiles(id)" value="<?= $max; ?>">
                    </div>
                </div>
            </form>
            <table id="tableCausacionCostos" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 30%;">Municipio</th>
                        <th style="width: 35%;">Sede</th>
                        <th style="width: 20%;">Centro de costo</th>
                        <th style="width: 20%;">Valor</th>
                        <th style="width: 15%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        foreach ($rubros as $ce) {
                            $id_doc = $ce['id_ctb_doc'];
                            $id = $ce['id'];
                            $editar = null;
                            $detalles = null;
                            if (true) {
                                $eliminar = '<a value="' . $id_doc . '" onclick="eliminarCentroCosto(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
                                $editar = '<a value="' . $id_doc . '" onclick="editarCentroCosto(' . $id . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
                                $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                            ...
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Historial</a>
                            </div>';
                            }
                            $valor = number_format($ce['valor'], 2, '.', ',');
                        ?>
                            <tr id="<?php echo $id; ?>">
                                <td class="text-left"><?php echo $ce['nom_municipio']; ?></td>
                                <td class="text-left"><?php echo $ce['nom_sede']; ?></td>
                                <td class="text-left"> <?php echo $ce['descripcion'];; ?></td>
                                <td class="text-right"> <?php echo number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td class="text-center"> <?php echo $editar . $eliminar .  $acciones; ?></td>

                            </tr>
                        <?php
                        }
                        ?>
                    </div>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-primary btn-sm" onclick="guardarCostos()">Guardar</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>
<?php
