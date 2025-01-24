<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../terceros.php';

$id_doc = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');

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
                LEFT JOIN `ctb_retencion_rango` 
                    ON (`ctb_causa_retencion`.`id_rango` = `ctb_retencion_rango`.`id_rango`)
                LEFT JOIN `ctb_retenciones` 
                    ON (`ctb_retencion_rango`.`id_retencion` = `ctb_retenciones`.`id_retencion`)
                LEFT JOIN `ctb_retencion_tipo` 
                    ON (`ctb_retenciones`.`id_retencion_tipo` = `ctb_retencion_tipo`.`id_retencion_tipo`)
            WHERE (`ctb_causa_retencion`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar tipo de retenciones tabla ctb_retenciones_tipo
try {
    $sql = "SELECT `id_retencion_tipo`, `tipo` FROM `ctb_retencion_tipo` ORDER BY `tipo` ASC;";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_factura`.`id_cta_factura`
                , `ctb_factura`.`id_ctb_doc`
                , `ctb_factura`.`id_tipo_doc`
                , `ctb_factura`.`num_doc`
                , `ctb_factura`.`fecha_fact`
                , `ctb_factura`.`fecha_ven`
                , `ctb_factura`.`valor_pago`
                , `ctb_factura`.`valor_iva`
                , `ctb_factura`.`valor_base`
                , `ctb_factura`.`detalle`
                , `ctb_tipo_doc`.`tipo`
                
            FROM
                `ctb_factura`
                INNER JOIN `ctb_tipo_doc` 
                    ON (`ctb_factura`.`id_tipo_doc` = `ctb_tipo_doc`.`id_ctb_tipodoc`)
            WHERE (`ctb_factura`.`id_ctb_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $facturas = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$band = !empty($facturas) ? true : false;
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
$factura = isset($_POST['fc']) ? $_POST['fc'] : '0|0';
$valores = explode('|', $factura);
$val_base = $valores[0];
$val_iva = $valores[1];
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
        ],
        columnDefs: [{
            class: 'text-wrap',
            targets: [0, 1]
        }],
    });
    $('#tableCausacionRetenciones').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE DESCUENTOS DE CUENTA POR PAGAR </h5>
        </div>
        <div class="px-3">
            <?php if ($band) { ?>
                <form id="formAddRetencioness">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="factura_des" class="small">Factura</label>
                            <select class="form-control form-control-sm py-0 sm" name="factura_des" id="factura_des" onchange="ValorBase(value);">
                                <option value="0|0" <?php echo $factura ? 'selected' : '' ?>>-- Seleccionar --</option>
                                <?php
                                foreach ($facturas as $fc) {
                                    $slc = $fc['valor_base'] . '|' . $fc['valor_iva'] ==  $factura ? 'selected' : '';
                                    echo '<option ' . $slc . ' value=' . $fc['valor_base'] . '|' . $fc['valor_iva'] . '>' . $fc['tipo'] . ' ' . str_pad($fc['num_doc'], 5, '0', STR_PAD_LEFT) . ' -> ' . pesos($fc['valor_pago']) . '</option>';
                                }
                                ?>
                            </select>
                            <input type="hidden" id="valor_base" value="<?php echo $val_base; ?>">
                            <input type="hidden" id="valor_iva" value="<?php echo $val_iva; ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="tipo_rete" class="small">Tipo retención</label>
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
                                    <input type="hidden" name="id_detalle" id="id_detalle" value="0">
                                    <input type="hidden" name="id_rango" id="id_rango" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-8">
                            <div id="divRete">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="id_rete" class="small">Retención</label>
                                        <select class="form-control form-control-sm py-0 sm" id="id_rete" name="id_rete">
                                            <option value="0">-- Seleccionar --</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="valor_rte" class="small">Valor retención</label>
                                        <input type="text" name="valor_rte" id="valor_rte" class="form-control form-control-sm text-right" onkeyup="valorMiles(id)" value="<?php echo 0; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="divRetIca">

                    </div>
                </form>
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
                            $tercero = getTerceros($id_ter, $cmd);
                            $tercero = $tercero[0]['nom_tercero'];
                            // Obtener el saldo del registro por obligar

                            if (true) {
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
            <?php } else { ?>
                <div class="alert alert-warning" role="alert">
                    Se debe registrar la(s) factura(s) para poder aplicar descuentos.
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="text-right pt-3">
        <?php if ($band) { ?>
            <a type="button" class="btn btn-primary btn-sm" onclick="GuardarRetencion()">Guardar</a>
        <?php } ?>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>