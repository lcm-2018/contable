<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../terceros.php';

$id_doc = $_POST['id_doc'] ?? '';
$valor_pago = $_POST['valor'] ?? 0;
$fecha_doc = date('Y-m-d', strtotime($_POST['fechar']));

// Consulta tipo de presupuesto
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_causa_retencion`.`id_causa_retencion`
                , `ctb_causa_retencion`.`id_ctb_doc`
                , `ctb_retencion_tipo`.`tipo`
                , `ctb_retenciones`.`nombre_retencion`
                , `ctb_causa_retencion`.`valor_base`
                , `ctb_causa_retencion`.`tarifa`
                , `ctb_causa_retencion`.`valor_retencion`
                , `ctb_causa_retencion`.`id_terceroapi`
            FROM
                `ctb_causa_retencion`
                INNER JOIN `ctb_retencion_rango` 
                    ON (`ctb_causa_retencion`.`id_rango` = `ctb_retencion_rango`.`id_rango`)
                INNER JOIN `ctb_retenciones` 
                    ON (`ctb_retencion_rango`.`id_retencion` = `ctb_retenciones`.`id_retencion`)
                INNER JOIN `ctb_retencion_tipo` 
                    ON (`ctb_retenciones`.`id_retencion_tipo` = `ctb_retencion_tipo`.`id_retencion_tipo`)
            WHERE (`ctb_causa_retencion`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
foreach ($rubros as $r) {
    if ($rubros['id_terceroapi'] != '') {
        $id_t[] = $r['id_terceroapi'];
    }
}
$ids = implode(',', $id_t);
$terceros = getTerceros($ids, $cmd);

// Consultar tipo de retenciones tabla ctb_retenciones_tipo
try {
    $sql = "SELECT `id_retencion_tipo`, `tipo` FROM `ctb_retencion_tipo` ORDER BY `tipo` ASC;";
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
        language: setIdioma,
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
                        $key = array_search($id_ter, array_column($terceros, 'id_tercero_api'));
                        if ($key === false) {
                            $tercero = '---';
                            $nit = '---';
                        } else {
                            $tercero = $terceros[$key]['nom_tercero'];
                            $nit = $terceros[$key]['nit_tercero'];
                        }
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
