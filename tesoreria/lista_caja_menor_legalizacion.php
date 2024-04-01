<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
$id_doc = $_POST['id_doc'] ?? '';
$id_cop = $_POST['id_cop'] ?? '';
$valor_pago = $_POST['valor'] ?? 0;
$fecha_doc = $_POST['fecha'] ?? '';
$valor_descuento = 0;
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Control de fechas
//$fecha_doc = date('Y-m-d');
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 5, $cmd);
$fecha = fechaSesion($_SESSION['vigencia'], $_SESSION['id_user'], $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));

try {
    $sql = "SELECT
                `cc`
                , `nom1`
                , `nom2`
                , `ape1`
                , `ape2`
                , `id_tercero_api`
            FROM
                `seg_tes_facturador`
            WHERE (`estado` =1);";
    $rs = $cmd->query($sql);
    $facturador = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar los conceptos asociados al recuado del arqueo
try {
    $sql = "SELECT id_concepto_arq,concepto FROM seg_tes_concepto_arqueo WHERE estado = 0";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar los arqueos registrados en seg_tes_arqueo_caja
try {
    $sql = "SELECT
                `seg_tes_causa_arqueo`.`id_causa_arqueo`
                , `seg_tes_causa_arqueo`.`fecha`
                , `seg_tes_causa_arqueo`.`id_tercero`
                , `seg_tes_causa_arqueo`.`valor_arq`
                , `seg_tes_causa_arqueo`.`valor_fac`
                , CONCAT(`seg_tes_facturador`.`nom1`, ' ', `seg_tes_facturador`.`nom2`, ' ', `seg_tes_facturador`.`ape1`, ' ', `seg_tes_facturador`.`ape2`) AS `facturador`
                , `seg_tes_causa_arqueo`.`id_ctb_doc`
            FROM
                `seg_tes_facturador`
                INNER JOIN `seg_tes_causa_arqueo` 
                    ON (`seg_tes_facturador`.`cc` = `seg_tes_causa_arqueo`.`id_tercero`)
            WHERE (`seg_tes_causa_arqueo`.`id_ctb_doc` =$id_doc);";
    $rs = $cmd->query($sql);
    $arqueos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}


$valor_pagar = 0;

?>
<script>
    $('#tableLegalizacionCaja').DataTable({
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
    $('#tableLegalizacionCaja').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE GASTOS PARA LEGALIZACION DE CAJA MENOR</h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-5">
            <form id="formAddFacturador">
                <div class="row">
                    <div class="col-8">
                        <div class="col"><label for="numDoc" class="small">TIPO DE GASTO:</label></div>
                    </div>


                    <div class="col-4">
                        <div class="col"><label for="numDoc" class="small">VALOR:</label></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-8">
                        <div class="col" id="divBanco">
                            <select name="id_facturador" id="id_facturador" class="form-control form-control-sm" required onchange="calcularCopagos2(this)">
                                <option value="0">...Seleccione...</option>
                                <?php foreach ($facturador as $fact) : ?>
                                    <option value="<?php echo $fact['cc']; ?>"><?php echo $fact['ape1'] . ' ' . $fact['ape2'] . ' ' . $fact['nom1'] . ' ' . $fact['nom2']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="btn-group">
                            <input type="text" name="valor_arq" id="valor_arq" class="form-control form-control-sm" value="<?php echo $valor_pagar; ?>" required style="text-align: right;" onkeyup="valorMiles(id)" ondblclick="copiarValor()" onchange="validarDiferencia()">
                            <button type="submit" class="btn btn-primary btn-sm" id="registrarMvtoDetalle">+</button>
                        </div>
                    </div>
                </div>
                <!--div class="row mb-2"></div-->
            </form>
            <br>
            <table id="tableLegalizacionCaja" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-60">Tipo de gasto</th>
                        <th class="w-20">Valor</th>
                        <th class="w-20">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        foreach ($arqueos as $ce) {
                            //$id_doc = $ce['id_ctb_doc'];
                            $id = $ce['id_causa_arqueo'];
                            if ((intval($permisos['editar'])) === 1) {
                                $borrar = '<a value="' . $id_doc . '" onclick="eliminarRecaduoArqeuo(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
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
                            $fecha = date("Y-m-d", strtotime($ce['fecha']));
                        ?>
                            <tr id="<?php echo $id; ?>">
                                <td><?php echo $fecha; ?></td>
                                <td class="text-left"><?php echo $ce['facturador']; ?></td>
                                <td> <?php echo $ce['id_tercero']; ?></td>
                                <td> <?php echo number_format($ce['valor_fac'], 2, '.', ','); ?></td>
                                <td> <?php echo number_format($ce['valor_arq'], 2, '.', ','); ?></td>
                                <td> <?php echo $borrar .  $acciones; ?></td>

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