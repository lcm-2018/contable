<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';

$datos = isset($_POST['id']) ? explode('|', $_POST['id']) : exit('Acceso no disponible');
$id_doc = $datos[0];
$id_factura = isset($datos[1]) ? $datos[1] : 0;
$objeto = $_POST['objeto'];
$vigencia = $_SESSION['vigencia'];
function pesos($valor)
{
    return number_format($valor, 2, '.', ',');
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
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `far_orden_ingreso`.`id_ingreso`
                , SUM(`far_orden_ingreso_detalle`.`cantidad` * `far_orden_ingreso_detalle`.`valor_sin_iva`) AS `val_base`
                , SUM((`far_orden_ingreso_detalle`.`valor_sin_iva` * `far_orden_ingreso_detalle`.`iva`/100)* `far_orden_ingreso_detalle`.`cantidad`) AS `val_iva`
                , `far_orden_ingreso`.`id_ctb_doc`
            FROM
                `ctb_doc`
                INNER JOIN `pto_crp` 
                ON (`ctb_doc`.`id_crp` = `pto_crp`.`id_pto_crp`)
                INNER JOIN `ctt_adquisiciones` 
                ON (`pto_crp`.`id_cdp` = `ctt_adquisiciones`.`id_cdp`)
                INNER JOIN `tb_tipo_bien_servicio` 
                ON (`ctt_adquisiciones`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `far_alm_pedido` 
                ON (`ctt_adquisiciones`.`id_orden` = `far_alm_pedido`.`id_pedido`)
                INNER JOIN `far_orden_ingreso` 
                ON (`far_orden_ingreso`.`id_pedido` = `far_alm_pedido`.`id_pedido`)
                INNER JOIN `far_orden_ingreso_detalle` 
                ON (`far_orden_ingreso_detalle`.`id_ingreso` = `far_orden_ingreso`.`id_ingreso`)
            WHERE `ctb_doc`.`id_ctb_doc` = $id_doc AND `far_orden_ingreso`.`estado` = 2
                AND (`tb_tipo_bien_servicio`.`filtro_adq` = 1 OR `tb_tipo_bien_servicio`.`filtro_adq` = 2)
            GROUP BY `far_orden_ingreso`.`id_ingreso`";
    $rs = $cmd->query($sql);
    $ingresos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_cta_factura`
                , `id_tipo_doc`
                , `num_doc`
                , DATE_FORMAT(`fecha_fact`, '%Y-%m-%d') AS `fecha_fact`
                , DATE_FORMAT(`fecha_ven`, '%Y-%m-%d') AS `fecha_ven`
                , `valor_pago`
                , `valor_base`
                , `valor_iva`
                , `detalle`
            FROM
                `ctb_factura`
            WHERE (`id_cta_factura` = $id_factura)";
    $rs = $cmd->query($sql);
    $detalle = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el tipo de documentos en ctb_tipo_doc
try {
    $sql = "SELECT `id_ctb_tipodoc`, `tipo` FROM `ctb_tipo_doc` ORDER BY `tipo` ASC";
    $rs = $cmd->query($sql);
    $tipodoc = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha_doc = date('Y-m-d');
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 5, $cmd);
$fecha = fechaSesion($_SESSION['vigencia'], $_SESSION['id_user'], $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$fecha_fact = $fecha;
$fecha_ven = strtotime('+30 day', strtotime($fecha_doc));
$fecha_ven = date('Y-m-d', $fecha_ven);
$cmd = null;
if (empty($detalle)) {
    $detalle = [
        'id_cta_factura' => 0,
        'id_tipo_doc' => 0,
        'num_doc' => '',
        'fecha_fact' => date('Y-m-d', strtotime($fecha_fact)),
        'fecha_ven' => date('Y-m-d', strtotime($fecha_ven)),
        'valor_pago' => 0,
        'valor_base' => 0,
        'valor_iva' => 0,
        'detalle' => $objeto
    ];
}
?>
<script>
    $('#tablaFacturasCXP').DataTable({
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
            targets: [1, 8]
        }],
    });
    $('#tablaFacturasCXP').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE FACTURAS DE CUENTA POR PAGAR </h5>
        </div>
        <div class="px-3 py-2">
            <input type="hidden" id="totFactura" value="<?php echo '0' ?>">
            <?php
            if (!empty($ingresos)) {
            ?>
                <form id="formIngCausa">
                    <table id="tableIngresosCausa" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 50%;">
                        <thead>
                            <tr>
                                <th>INGRESO</th>
                                <th>VALOR BASE</th>
                                <th>VALOR IVA</th>
                                <th>VALOR TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($ingresos as $ingreso) {
                                if ($ingreso['id_ctb_doc'] == $id_doc || $ingreso['id_ctb_doc'] == '') {
                                    $checked = $ingreso['id_ctb_doc'] == $id_doc ? 'checked' : '';
                                    echo '<tr>';
                                    echo '<td>
                                            <input type="checkbox" name="ingreso[]" onclick="PasaValoresFactura(this)"  value="' . $ingreso['id_ingreso'] . '" ' . $checked . '>
                                        </td>';
                                    echo '<td class="text-right base">' . pesos($ingreso['val_base']) . '</td>';
                                    echo '<td class="text-right iva">' . pesos($ingreso['val_iva']) . '</td>';
                                    echo '<td class="text-right total">' . pesos($ingreso['val_base'] + $ingreso['val_iva']) . '</td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </form>
            <?php
            }
            ?>
            <form id="formFacturaCXP">
                <input type="hidden" name="id_cta_factura" id="id_cta_factura" value="<?php echo $detalle['id_cta_factura'] ?>">
                <div class="form-row">
                    <div class="col-md-2 text-right d-flex align-items-center">
                        <span class="small">DOCUMENTO:</span>
                    </div>
                    <div class="form-group col-md-10 mb-1">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="tipoDoc" class="small">Tipo</label>
                                <select class="form-control form-control-sm" id="tipoDoc" name="tipoDoc" onchange="consecutivoDocumento(value);">
                                    <option value="0" <?php echo $detalle['id_tipo_doc'] == 0 ? 'selected' : '' ?>>-- Selecionar --</option>
                                    <?php foreach ($tipodoc as $tipo) {
                                        $slc = $detalle['id_tipo_doc'] == $tipo['id_ctb_tipodoc'] ? 'selected' : '';
                                        echo '<option value="' . $tipo['id_ctb_tipodoc'] . '" ' . $slc . '>' . $tipo['tipo'] . '</option>';
                                    } ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="numFac" class="small">Número</label>
                                <input type="text" name="numFac" id="numFac" class="form-control form-control-sm text-right" value="<?php echo $detalle['num_doc'] ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="fechaDoc" class="small">Fecha factura</label>
                                <input type="date" name="fechaDoc" id="fechaDoc" class="form-control form-control-sm" value="<?php echo $detalle['fecha_fact'] ?>" min="<?= $vigencia . '-01-01'; ?>" max="<?= $vigencia . '-12-31'; ?>">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="fechaVen" class="small">Fecha vencimiento</label>
                                <input type="date" name="fechaVen" id="fechaVen" class="form-control form-control-sm" value="<?php echo $detalle['fecha_ven'] ?>" min="<?= $vigencia . '-01-01'; ?>" max="<?= $vigencia . '-12-31'; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-2 text-right d-flex align-items-center">
                        <span class="small">VALOR FACTURA:</span>
                    </div>
                    <div class="form-group col-md-10 mb-1">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="valor_pagar" class="small">VALOR</label>
                                <input type="text" value="<?php echo pesos($detalle['valor_pago']) ?>" name="valor_pagar" id="valor_pagar" class="form-control form-control-sm text-right" onkeyup="valorMiles(id)">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="valor_iva" class="small">iva</label>
                                <input type="text" value="<?php echo pesos($detalle['valor_iva']) ?>" name="valor_iva" id="valor_iva" class="form-control form-control-sm text-right" onkeyup="valorMiles(id)" onchange="calculoValorBase();" ondblclick="calculoIva();">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="valor_base" class="small">BASE</label>
                                <input type="text" value="<?php echo pesos($detalle['valor_base']) ?>" name="valor_base" id="valor_base" class="form-control form-control-sm text-right" onkeyup="valorMiles(id)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-2 text-right d-flex align-items-center">
                        <span class="small">DETALLE:</span>
                    </div>
                    <div class="form-group col-md-10 mb-1">
                        <textarea name="detalle" id="detalle" class="form-control form-control-sm caps" rows="2"><?php echo $detalle['detalle'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="p-3">
        <table id="tablaFacturasCXP" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Vence</th>
                    <th>Valor</th>
                    <th>IVA</th>
                    <th>Base</th>
                    <th>Detalle</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($facturas as $factura) {
                    $id_detalle = $factura['id_cta_factura'];
                    $val = base64_encode($id_doc . '|' . $id_detalle);
                    $editar = '<button text="' . $val . '" onclick="editarFactura(this)" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar Factura"><span class="fas fa-pencil-alt fa-lg"></span></button>';
                    $borrar = '<button text="' . $val . '" onclick="eliminarFactura(this)" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar Factura"><span class="fas fa-trash-alt fa-lg"></span></button>';
                    echo '<tr>';
                    echo '<td>' . $id_detalle . '</td>';
                    echo '<td>' . $factura['tipo'] . '</td>';
                    echo '<td>' . $factura['num_doc'] . '</td>';
                    echo '<td>' . date('Y-m-d', strtotime($factura['fecha_fact'])) . '</td>';
                    echo '<td>' . date('Y-m-d', strtotime($factura['fecha_ven'])) . '</td>';
                    echo '<td class="text-right">' . pesos($factura['valor_pago']) . '</td>';
                    echo '<td class="text-right">' . pesos($factura['valor_iva']) . '</td>';
                    echo '<td class="text-right">' . pesos($factura['valor_base']) . '</td>';
                    echo '<td  class="text-left">' . $factura['detalle'] . '</td>';
                    echo '<td class="text-center">' . $editar . $borrar . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<div class="text-right">
    <button text="<?php echo $id_doc; ?>" class="btn btn-success btn-sm" onclick="ProcesaFacturas(this)">Guardar</button>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
</div>
</div>
<?php
