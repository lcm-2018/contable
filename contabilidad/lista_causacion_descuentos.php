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
$valor_pago = $_POST['valor'] ?? 0;
$fecha_doc = date('Y-m-d', strtotime($_POST['fechar']));

// Consulta tipo de presupuesto
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
    `seg_ctb_causa_retencion`.`id_causa_retencion`
    , `seg_ctb_causa_retencion`.`id_ctb_doc`
    , `seg_ctb_retencion_tipo`.`tipo`
    , `seg_ctb_retenciones`.`nombre_retencion`
    , `seg_ctb_causa_retencion`.`valor_base`
    , `seg_ctb_causa_retencion`.`tarifa`
    , `seg_ctb_causa_retencion`.`valor_retencion`
    ,`seg_ctb_causa_retencion`.`id_terceroapi`
FROM
    `seg_ctb_causa_retencion`
    INNER JOIN `seg_ctb_retenciones` 
        ON (`seg_ctb_causa_retencion`.`id_retencion` = `seg_ctb_retenciones`.`id_retencion`)
    INNER JOIN `seg_ctb_retencion_tipo` 
        ON (`seg_ctb_retencion_tipo`.`id_retencion_tipo` = `seg_ctb_retenciones`.`id_retencion_tipo`)
WHERE (`seg_ctb_causa_retencion`.`id_ctb_doc` =$id_doc);";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar tipo de retenciones tabla seg_ctb_retenciones_tipo
try {
    $sql = "SELECT id_retencion_tipo, tipo FROM seg_ctb_retencion_tipo ORDER BY id_retencion_tipo";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

$cmd = null;

?>
<script>
    $('#tableCausacionRetenciones').DataTable({
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
    $('#tableCausacionRetenciones').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE DESCUENTOS DE LA OBLIGACION </h5>
        </div>
        <div class="pb-3"></div>
        <div class="px-3">

            <div class="row">
                <div class="col-3">
                    <div class="col"><label for="numDoc" class="small"> campos</label></div>
                </div>

            </div>
            <form id="formAddRetencioness">
                <div class="row">
                    <div class="col-4">
                        <div class="col">
                            <select class="form-control form-control-sm py-0 sm" name="tipo_rete" id="tipo_rete" onchange="mostrarRetenciones(value);" required>
                                <option value="0">-- Seleccionar --</option>
                                <?php
                                foreach ($retenciones as $retencion) {
                                    echo "<option value='$retencion[id_retencion_tipo]'>$retencion[tipo]</option>";
                                }
                                ?>
                            </select>
                            <input type="hidden" name="id_docr" id="id_docr" value="<?php echo $id_doc; ?>">
                            <input type="hidden" name="tarifa" id="tarifa" value="">
                            <input type="hidden" name="id_terceroapi" id="id_terceroapi" value="">

                        </div>
                    </div>
                    <div class="col-5">
                        <div class="col" id="divRete">
                            <select class="form-control form-control-sm py-0 sm">
                                <option value="">-- Seleccionar --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="btn-group"><input type="text" name="valor_rte" id="valor_rte" class="form-control form-control-sm" value="<?php echo 0; ?>" required style="text-align: right;">
                            <button type="submit" class="btn btn-primary btn-sm">+</button>
                        </div>
                    </div>
                </div>
                <div class="row">&nbsp;</div>
                <div class="row" id="conDivSobre">
                    <div class="col-4">
                        <div class="col" id="divSede"></div>
                    </div>
                    <div class="col-5">
                        <div class="col btn-group" id="divSobre"></div>
                    </div>
                </div>
            </form>
            <br>
            <table id="tableCausacionRetenciones" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 15%;">Entidad</th>
                        <th style="width: 45%;">Descuento</th>
                        <th style="width: 15%;">Valor base</th>
                        <th style="width: 15%;">Valor rete</th>
                        <th style="width: 10%;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $j = 0;
                    foreach ($rubros as $ce) {
                        $id_doc = $ce['id_causa_retencion'];
                        $j++;
                        // Consulto el valor del tercero de la api
                        $id_ter = $ce['id_terceroapi'];
                        $url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $res_api = curl_exec($ch);
                        curl_close($ch);
                        $dat_ter = json_decode($res_api, true);
                        $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
                        // fin api terceros
                        // Obtener el saldo del registro por obligar

                        if ((intval($permisos['editar'])) === 1) {
                            $editar = '<a value="' . $id_doc . '" onclick="eliminarRetencion(' . $id_doc . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-trash-alt fa-lg"></span></a>';
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
                        $valor = number_format($ce['valor_base'], 2, '.', ',');
                    ?>
                        <tr id="<?php echo $id_doc; ?>">
                            <td class="text-left"> <?php echo $tercero; ?></td>
                            <td class="text-left"> <?php echo $ce['nombre_retencion']; ?></td>
                            <td class="text-right"> <?php echo number_format($ce['valor_base'], 2, '.', ','); ?></td>
                            <td class="text-right"> <?php echo number_format($ce['valor_retencion'], 2, '.', ','); ?></td>
                            <td class="text-center"> <?php echo $editar .  $acciones; ?></td>

                        </tr>
                    <?php
                    }
                    ?>

                </tbody>
            </table>
        </div>
        <div class="text-right pt-3">
            <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
        </form>
    </div>


</div>
<?php
