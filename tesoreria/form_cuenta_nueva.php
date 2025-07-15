<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
$id_tes_cuenta = isset($_POST['id_tes_cuenta']) ? $_POST['id_tes_cuenta'] : 0;
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
// Estabelcer zona horaria bogota
date_default_timezone_set('America/Bogota');
// insertar fecha actual
$fecha = date("Y-m-d");

// consultar la fecha de cierre del periodo del módulo de presupuesto 
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
if ($id_tes_cuenta > 0) {
    try {
        $sql = "SELECT
                    `tes_cuentas`.`id_banco`
                    , `tes_cuentas`.`id_tipo_cuenta`
                    , `ctb_pgcp`.`cuenta` AS `cta_contable`
                    , `tes_cuentas`.`nombre`
                    , `tes_cuentas`.`numero`
                    , `tes_cuentas`.`estado`
                    , `tes_cuentas`.`id_tes_cuenta`
                    , `tes_cuentas`.`id_fte`
                    , `ctb_pgcp`.`id_pgcp`
                FROM
                    `tes_cuentas`
                    LEFT JOIN `ctb_pgcp` 
                        ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                WHERE (`tes_cuentas`.`id_tes_cuenta` = $id_tes_cuenta)";
        $rs = $cmd->query($sql);
        $cuentas = $rs->fetch();
        $id_banco = $cuentas['id_banco'];
        $id_tipo_cuenta = $cuentas['id_tipo_cuenta'];
        $id_pgcp = $cuentas['id_pgcp'];
        $cta_contable = $cuentas['cta_contable'];
        $nombre = $cuentas['nombre'];
        $numero = $cuentas['numero'];
        $estado = $cuentas['estado'];
        $id_fte = $cuentas['id_fte'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
} else {
    $id_banco = 0;
    $id_tipo_cuenta = 0;
    $id_pgcp = 0;
    $cta_contable = '';
    $nombre = '';
    $numero = '';
    $estado = 0;
    $id_fte = 0;
}
// Consultar el listado de bancos de la tabla tb_bancos
$cuentas = [];
if ($id_tes_cuenta  > 0) {
    $union = "UNION ALL
            SELECT
                `ctb_pgcp`.`id_pgcp`
                , `ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`
                , `tes_cuentas`.`id_banco`
                , `tes_cuentas`.`id_tes_cuenta`
            FROM
                `tes_cuentas`
                INNER JOIN `ctb_pgcp` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
            WHERE (`tes_cuentas`.`id_tes_cuenta` = $id_tes_cuenta)";
} else {
    $union = '';
}
try {
    $sql = "SELECT
                    `ctb_pgcp`.`id_pgcp`
                    , `ctb_pgcp`.`cuenta`
                    , `ctb_pgcp`.`nombre`
                    , `tb_bancos`.`id_banco`
                    , `tes_cuentas`.`id_tes_cuenta`
                FROM
                    `ctb_pgcp`
                    LEFT JOIN `tes_cuentas` 
                        ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
                    LEFT JOIN `tb_bancos` 
                        ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                WHERE (`ctb_pgcp`.`cuenta` LIKE '1110%' OR `ctb_pgcp`.`cuenta` LIKE '1132%'  OR `ctb_pgcp`.`cuenta` LIKE '110106%')
                    AND `ctb_pgcp`.`tipo_dato` = 'D'
                    AND `tes_cuentas`.`id_tes_cuenta` IS NULL
                $union";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT `id_banco`, `nom_banco` FROM `tb_bancos` ORDER BY `nom_banco` ASC";
    $rs = $cmd->query($sql);
    $listabancos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el listado de tipo de cuenta de la tabla tes_tipo_cuenta
try {
    $sql = "SELECT `id_tipo_cuenta` , `tipo_cuenta` FROM `tes_tipo_cuenta` ORDER BY `tipo_cuenta` ASC";
    $rs = $cmd->query($sql);
    $listatipocuenta = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT `id`,`codigo`,`nombre` FROM `fin_cod_fuente` ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $fuente = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <form id="formGestionCuenta">
        <input type="hidden" id="id_tes_cuenta" name="id_tes_cuenta" value="<?php echo $id_tes_cuenta ?>">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;GESTION DE CUENTAS BANCARIAS <?php echo ''; ?></h5>
            </div>
            <div class="py-3 px-3">
                <div class="row mb-1">
                    <div class="col-3">
                        <label for="banco" class="small">BANCO: </label>
                    </div>
                    <div class="col-9">
                        <select id="banco" name="banco" class="form-control form-control-sm" required onchange="mostrarCuentasPendiente(value);">
                            <option value="0" <?php echo $id_banco == 0 ? 'selected' : '' ?>>--Seleccione--</option>
                            <?php foreach ($listabancos as $lb) {
                                $slc = $lb['id_banco'] == $id_banco ? 'selected' : '';
                                echo '<option value="' . $lb['id_banco'] . '" ' . $slc . '>' . $lb['nom_banco'] . '</option>';
                            } ?>
                        </select>
                        <input type="hidden" id="id_cuenta" name="id_cuenta" value="<?php echo $id_tes_cuenta; ?>">
                        <input type="hidden" id="id_pgcp" name="id_pgcp" value="<?php echo $id_pgcp; ?>">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3">
                        <label for="cuentas" class="small">CUENTA CONTABLE: </label>
                    </div>
                    <div class="col-9">
                        <div id="divBanco">
                            <select id="cuentas" name="cuentas" class="form-control form-control-sm">
                                <option value="0">-- Seleccionar --</option>
                                <?php
                                foreach ($cuentas as $cuenta) {
                                    $value = base64_encode($cuenta['id_pgcp'] . '|' . $cuenta['nombre']);
                                    $selected = $cuenta['id_tes_cuenta'] == $id_tes_cuenta ? 'selected' : '';
                                    echo '<option value="' . $value . '" ' . $selected . '>' . $cuenta['cuenta'] . ' | ' . $cuenta['nombre'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3">
                        <label for="tipo_cuenta" class="small">TIPO CUENTA: </label>
                    </div>
                    <div class="col-9">
                        <select id="tipo_cuenta" name="tipo_cuenta" class="form-control form-control-sm" required>
                            <option value="0" <?php echo $id_tipo_cuenta == 0 ? 'selected' : '' ?>>--Seleccione--</option>
                            <?php foreach ($listatipocuenta as $lb) {
                                $slc = $lb['id_tipo_cuenta'] == $id_tipo_cuenta ? 'selected' : '';
                                echo '<option value="' . $lb['id_tipo_cuenta'] . '" ' . $slc . '>' . $lb['tipo_cuenta'] . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3">
                        <label for="numero" class="small">NUMERO DE CUENTA: </label>
                    </div>
                    <div class="col-9">
                        <input type="text" id="numero" name="numero" class="form-control form-control-sm" value="<?php echo $numero; ?>">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3">
                        <label for="codigo_fuente" class="small">CÓD. FUENTE: </label>
                    </div>
                    <div class="col-9">
                        <select id="codigo_fuente" name="codigo_fuente" class="form-control form-control-sm">
                            <option value="0">-- Seleccionar --</option>
                            <?php foreach ($fuente as $f) {
                                $slc = $f['id'] == $id_fte ? 'selected' : '';
                                echo '<option value="' . $f['id'] . '" ' . $slc . '>' . $f['nombre'] . ' | ' . $f['codigo'] . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="text-right">
        <button type="button" class="btn btn-primary btn-sm" onclick="guardarCuentaBanco(this)">Guardar</button>
        <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
    </div>
</div>