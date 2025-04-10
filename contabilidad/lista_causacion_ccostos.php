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
$id_doc = $_POST['id_doc'] ?? '';
// Consulta tipo de presupuesto
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
?>

<script>
    $('#tableCausacionCostos').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableCausacionCostos').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE CENTROS DE COSTO OBLIGACION</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-5">
            <form id="formAddCentroCosto">
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">MUNICIPIO:</label></div>
                    </div>
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">SEDE:</label></div>
                    </div>
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">CENTRO DE COSTO:</label></div>
                    </div>
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">VALOR CC:</label></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><input type="text" name="municipio" id="municipio" class="form-control form-control-sm" value="" onchange="mostrarSedes();" required>
                            <input type="hidden" name="id_municipio" id="id_municipio" value="">
                            <input type="hidden" name="id_doc" id="id_doc" value="<?php echo $id_doc; ?>">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="col" id="divSede"><input type="text" name="sede" id="sede" class="form-control form-control-sm" value="" required></div>
                    </div>
                    <div class="col-3">
                        <div class="col" id="divCosto"><input type="text" name="c_costo" id="c_costo" class="form-control form-control-sm" value="" required></div>
                    </div>
                    <div class="col-3">
                        <div class="btn-group"><input type="text" name="valor_cc" id="valor_cc" class="form-control form-control-sm" value="" required style="text-align: right;" onkeyup="valorMiles(id)" ondblclick="valorCostoReg('<?php echo $id_doc; ?>');">
                            <button type="submit" class="btn btn-primary btn-sm" id="registrarMvtoDetalle">+</button>
                        </div>
                    </div>
                </div>

            </form> <br>
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
                            if ((intval($permisos['editar'])) === 1) {
                                $editar = '<a value="' . $id_doc . '" onclick="eliminarCentroCosto(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
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
                            $valor = number_format($ce['valor'], 2, '.', ',');
                        ?>
                            <tr id="<?php echo $id; ?>">
                                <td class="text-left"><?php echo $ce['nom_municipio']; ?></td>
                                <td class="text-left"><?php echo $ce['nombre']; ?></td>
                                <td class="text-left"> <?php echo $ce['descripcion'];; ?></td>
                                <td class="text-right"> <?php echo number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td class="text-center"> <?php echo $editar .  $acciones; ?></td>

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
