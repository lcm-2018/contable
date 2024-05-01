<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';

$id_doc = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_factura`.`id_tipo_doc`
                , `ctb_factura`.`id_ctb_doc`
                , `ctb_factura`.`id_tipo_doc`
                , `ctb_tipo_doc`.`tipo`
                , `ctb_factura`.`num_doc`
                , `ctb_factura`.`fecha_fact`
                , `ctb_factura`.`fecha_ven`
                , `ctb_factura`.`valor_pago`
                , `ctb_factura`.`valor_iva`
                , `ctb_factura`.`valor_base`
                , `ctb_factura`.`detalle`
            FROM
                `ctb_factura`
                INNER JOIN `ctb_tipo_doc` 
                    ON (`ctb_factura`.`id_tipo_doc` = `ctb_tipo_doc`.`id_ctb_tipodoc`)
            WHERE (`ctb_factura`.`id_tipo_doc` = $id_doc)";
    $rs = $cmd->query($sql);
    $facturas = $rs->fetchAll();
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
$cmd = null;

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
        ]
    });
    $('#tablaFacturasCXP').wrap('<div class="overflow" />');
</script>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE DESCUENTOS DE CUENTA POR PAGAR </h5>
        </div>
        <div class="p-3">
            <div class="form-row">
                <div class="col-md-2 text-right">
                    <span class="small">DESCUENTOS:</span>
                </div>
                <div class="form-group col-md-10">
                    <div class="input-group input-group-sm">
                        <input type="text" name="descuentos" id="descuentos" class="form-control form-control-sm" style="text-align: right;" value="" onkeyup="valorMiles(id)">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" type="button" onclick="cargaDescuentos('<?php echo $id_doc; ?>')"><span class="fas fa-minus fa-lg"></span></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-12 text-right">
                    <button type="button" class="btn btn-primary btn-sm" onclick="generaMovimientoCxp();">Generar movimiento</button>
                </div>
            </div>
        </div>
        <div class="px-3">
            <table id="tablaFacturasCXP" class="table table-striped table-bordered nowrap table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Num</th>
                        <th>Rp</th>
                        <th>Contrato</th>
                        <th>Fecha</th>
                        <th>Terceros</th>
                        <th>Valor</th>
                        <th>Acciones</th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="text-right pt-3">
        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Guardar</a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Aceptar</a>
    </div>
</div>
<?php
