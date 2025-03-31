<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include_once '../conexion.php';
include_once '../permisos.php';

$id_doc = $_POST['id_doc'] ?? 0;
$id_cop = $_POST['id_cop'] ?? 0;
$id_fp = $_POST['id_fp'] ?? 0;
$valor_pago = $_POST['valor'] ?? 0;

$valor_descuento = 0;
// Consulta tipo de presupuesto
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT
                `tes_detalle_pago`.`id_detalle_pago`
                ,`tb_bancos`.`nom_banco`
                , `tes_cuentas`.`nombre`
                , `tes_forma_pago`.`forma_pago`
                , `tes_detalle_pago`.`documento`
                , `tes_detalle_pago`.`valor`
            FROM
                `tes_detalle_pago`
                INNER JOIN `tes_forma_pago` 
                    ON (`tes_detalle_pago`.`id_forma_pago` = `tes_forma_pago`.`id_forma_pago`)
                INNER JOIN `tes_cuentas` 
                    ON (`tes_detalle_pago`.`id_tes_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
            WHERE (`tes_detalle_pago`.`id_ctb_doc` = $id_doc);";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar id bancos de tb_bancos
try {
    $sql = "SELECT 
                `tb_bancos`.`id_banco`, `tb_bancos`.`nom_banco`
            FROM `tb_bancos` 
            LEFT JOIN `tes_cuentas`
                ON (`tb_bancos`.`id_banco` = `tes_cuentas`.`id_banco`)
            WHERE `tes_cuentas`.`id_banco` IS NOT NULL
            GROUP BY `tb_bancos`.`id_banco`
            ORDER BY `nom_banco` ASC";
    $rs = $cmd->query($sql);
    $bancos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar forma de pago de tes_forma_pago
try {
    $sql = "SELECT `id_forma_pago`, `forma_pago` FROM `tes_forma_pago` ORDER BY `forma_pago` ASC";
    $rs = $cmd->query($sql);
    $formas_pago = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los documentos que estan relacionados con el pago
// Consultar el valor a de los descuentos realizados a la cuenta de ctb_causa_retencion
/*
try {
    $sql = "SELECT
                `id_pto_cop_det`
            FROM
                `pto_cop_detalle`
            WHERE (`id_ctb_doc` = $id_doc)
            GROUP BY `id_pto_cop_det`";

    $rs = $cmd->query($sql);
    $des_documentos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
    */
// Consultar el valor a de los descuentos realizados a la cuenta de ctb_causa_retencion de acuerdo a los documentos relacionados
// recorro los documentos relacionados
try {
    $sql = "SELECT SUM(`valor_retencion`) AS `valor` FROM `ctb_causa_retencion` WHERE `id_ctb_doc` = $id_cop";
    $rs = $cmd->query($sql);
    $descuentos = $rs->fetch();
    $valor_descuento = $valor_descuento + $descuentos['valor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar el valor registrado en seg_test_detalle_pago para el id_ctb_doc
try {
    $sql = "SELECT SUM(`valor`) AS `valor` FROM `tes_detalle_pago` WHERE `id_ctb_doc` = $id_doc";
    $rs = $cmd->query($sql);
    $pagos = $rs->fetch();
    $valor_programado = $pagos['valor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$valor_pagar = $valor_pago - $valor_descuento - $valor_programado;

?>
<script>
    $('#tableCausacionPagos').DataTable({
        dom: "<'row'<'col-md-2'l><'col-md-10'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: setIdioma,
        "order": [
            [0, "desc"]
        ]
    });
    $('#tableCausacionPagos').wrap('<div class="overflow" />');
</script>
<div class="px-0">

    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE CUENTAS BANCARIAS Y FORMA DE PAGO <?php echo "valor a pagar " . $valor_programado; ?></h5>
        </div>
        <div class="px-4 mt-3">
            <form id="formAddFormaPago">
                <input type="hidden" name="id_doc" id="id_doc" value="<?php echo $id_doc; ?>">
                <input type="hidden" name="id_pto_cop" id="id_pto_cop" value="<?php echo $id_cop; ?>">
                <div class="form-row">
                    <div class="col-md-3 form-group">
                        <label for="banco" class="small">BANCO</label>
                        <select name="banco" id="banco" class="form-control form-control-sm" required onclick="mostrarCuentas(value);">
                            <option value="0">--Seleccione--</option>
                            <?php foreach ($bancos as $banco) : ?>
                                <option value="<?php echo $banco['id_banco']; ?>"><?php echo $banco['nom_banco']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="numDoc" class="small">CUENTA</label>
                        <div id="divBanco">
                            <select name="cuenta" id="cuenta" class="form-control form-control-sm">
                                <option value="0">--Seleccione--</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="numDoc" class="small">FORMA DE PAGO</label>
                        <div id="divForma">
                            <select name="forma_pago_det" id="forma_pago_det" class="form-control form-control-sm" required onchange="buscarCheque(value);">
                                <option value="0">--Seleccione--</option>
                                <?php foreach ($formas_pago as $forma_pago) : ?>
                                    <option value="<?php echo $forma_pago['id_forma_pago']; ?>"><?php echo $forma_pago['forma_pago']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="numDoc" class="small">DOCUMENTO</label>
                        <div id="divCosto"><input type="text" name="documento" id="documento" class="form-control form-control-sm" value="" required></div>
                    </div>
                    <div class="col-md-2">
                        <label for="numDoc" class="small">VALOR</label>
                        <div class="btn-group"><input type="text" name="valor_pag" id="valor_pag" class="form-control form-control-sm" max="<?php echo $valor_pagar; ?>" value="<?php echo $valor_pagar; ?>" required style="text-align: right;" onkeyup="valorMiles(id)" ondblclick="valorMovTeroreria('');">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-3 form-group">
                        <label for="banco" class="small">SALDO</label>
                        <div id="divSaldoDisp" class="form-control form-control-sm text-right" readonly></div>
                        <input type="hidden" name="numSaldoDips" id="numSaldoDips" value="0">
                    </div>
                </div>
            </form>
            <table id="tableCausacionPagos" class="table table-striped table-bordered table-sm table-hover shadow" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="w-15">Banco</th>
                        <th class="w-30">Cuenta</th>
                        <th class="w-5">Forma de pago</th>
                        <th class="w-5">Documento</th>
                        <th class="w-10">Valor</th>
                        <th class="w-5">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <div id="datostabla">
                        <?php
                        foreach ($rubros as $ce) {
                            //$id_doc = $ce['id_ctb_doc'];
                            $id = $ce['id_detalle_pago'];
                            if (PermisosUsuario($permisos, 5601, 3) || $id_rol == 1) {
                                $editar = '<a value="' . $id_doc . '" onclick="eliminarFormaPago(' . $id . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb editar" title="Modificar"><span class="fas fa-trash-alt fa-lg"></span></a>';
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
                            $acciones = null;
                            $valor = number_format($ce['valor'], 2, '.', ',');
                        ?>
                            <tr id="<?php echo $id; ?>">
                                <td class="text-left"><?php echo $ce['nom_banco']; ?></td>
                                <td class="text-left"><?php echo $ce['nombre']; ?></td>
                                <td> <?php echo $ce['forma_pago']; ?></td>
                                <td> <?php echo $ce['documento']; ?></td>
                                <td> <?php echo number_format($ce['valor'], 2, '.', ','); ?></td>
                                <td> <?php echo $editar .  $acciones; ?></td>

                            </tr>
                        <?php
                        }
                        ?>
                    </div>
                </tbody>
            </table>
            <div class="text-right py-3">
                <a type="button" class="btn btn-success btn-sm" onclick="GuardaFormaPago(this)">Guardar</a>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
            </div>
        </div>


    </div>