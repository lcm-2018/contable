<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
include '../financiero/consultas.php';
include '../terceros.php';

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
                `id_tercero_api`
            FROM
                `tes_facturador`
            WHERE (`estado` = 1)";
    $rs = $cmd->query($sql);
    $facturador = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
$terceros = [];
if (!empty($facturador)) {
    foreach ($facturador as $fact) {
        $id_t[] = $fact['id_tercero_api'];
    }
    $ids = implode(',', $id_t);
    $terceros = getTerceros($ids, $cmd);
}
// consultar los conceptos asociados al recuado del arqueo
try {
    $sql = "SELECT `id_concepto_arq`,`concepto` FROM `tes_concepto_arqueo` WHERE `estado` = 1";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar los arqueos registrados en seg_tes_arqueo_caja
try {
    $sql = "SELECT
                `tes_causa_arqueo`.`id_ctb_doc`
                , `tes_causa_arqueo`.`id_causa_arqueo`
                , `tes_causa_arqueo`.`fecha`
                , `tes_causa_arqueo`.`id_tercero`
                , `tes_causa_arqueo`.`valor_arq`
                , `tes_causa_arqueo`.`valor_fac`
                , `tes_causa_arqueo`.`observaciones`
            FROM
                `tes_facturador`
                INNER JOIN `tes_causa_arqueo` 
                    ON (`tes_facturador`.`id_tercero_api` = `tes_causa_arqueo`.`id_tercero`)
            WHERE (`tes_causa_arqueo`.`id_ctb_doc` = $id_doc)";
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
        language: setIdioma,
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
        <div class="px-3 pt-2">
            <form id="formAddFacturador">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label for="id_facturador" class="small">TIPO DE GASTO</label>
                        <div id="divBanco">
                            <select name="id_facturador" id="id_facturador" class="form-control form-control-sm" required onchange="calcularCopagos2(this)">
                                <option value="0">--Seleccione--</option>
                                <?php foreach ($facturador as $fact) {
                                    $key = array_search($fact['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
                                    $nombre = $key !== false ? ltrim($terceros[$key]['nom_tercero']) : '---';
                                    $cc = $key !== false ? $terceros[$key]['cc_nit'] : '---';
                                    echo '<option value="' . $fact['id_tercero_api'] . '">' . $nombre . ' -> ' . $cc . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="valor_arq" class="small">VALOR</label>
                        <input type="text" name="valor_arq" id="valor_arq" class="form-control form-control-sm" value="<?php echo $valor_pagar; ?>" required style="text-align: right;" onkeyup="valorMiles(id)" ondblclick="copiarValor()" onchange="validarDiferencia()">
                        <button type="submit" class="btn btn-primary btn-sm" id="registrarMvtoDetalle">+</button>
                    </div>
                </div>
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
                            if (PermisosUsuario($permisos, 5601, 3) || $id_rol == 1) {
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
            <div class="text-right py-3">
                <a type="button" class="btn btn-success btn-sm" onclick="GuardarLegCajaMenor()">Cerrar</a>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
            </div>
        </div>
    </div>