<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : 0;
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
// Estabelcer zona horaria bogota
date_default_timezone_set('America/Bogota');
// insertar fecha actual
$fecha = date("Y-m-d");
$id_banco = '';
$cuenta = '';
$numero = '';
$inicial = '';
$maximo = '';
$id_chequera = null;

// consultar la fecha de cierre del periodo del módulo de presupuesto 
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `tb_bancos`.`id_banco`
                , `tb_bancos`.`nom_banco`
                , `fin_chequeras`.`fecha`
                , `fin_chequeras`.`id_chequera`
                , `tes_cuentas`.`nombre`
                , `fin_chequeras`.`id_cuenta`
                , `fin_chequeras`.`numero`
                , `fin_chequeras`.`inicial` AS `en_uso`
                , `fin_chequeras`.`maximo` AS `final`
            FROM
                `tes_cuentas`
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
                INNER JOIN `fin_chequeras` 
                    ON (`fin_chequeras`.`id_cuenta` = `tes_cuentas`.`id_tes_cuenta`)
            WHERE (`fin_chequeras`.`id_chequera` = $id)";
    $rs = $cmd->query($sql);
    $chequera = $rs->fetch(PDO::FETCH_ASSOC);
    print_r($chequera);
    if (!empty($chequera)) {
        $id_chequera = $chequera['id_chequera'];
        $id_banco = $chequera['id_banco'];
        $cuenta = $chequera['id_cuenta'];
        $numero = $chequera['numero'];
        $fecha = $chequera['fecha'];
        $fecha = date("Y-m-d", strtotime($fecha));
        $inicial = $chequera['en_uso'];
        $maximo = $chequera['final'];
    } else {
        $id_chequera = 0;
        $id_banco = 0;
        $cuenta = 0;
        $numero = '';
        $fecha = date("Y-m-d", strtotime($fecha));
        $fecha = date("Y-m-d", strtotime($fecha));
        $inicial = '';
        $maximo = '';
    }
    echo 'cuenta: ' . $cuenta;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consultar el listado de bancos de la tabla tb_bancos
try {
    $sql = "SELECT
                `tb_bancos`.`id_banco`
                , `tb_bancos`.`nom_banco`
            FROM
                `tes_cuentas`
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
            GROUP BY `tb_bancos`.`id_banco`
            ORDER BY `tb_bancos`.`nom_banco` ASC";
    $rs = $cmd->query($sql);
    $listabancos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tes_cuentas`.`id_tes_cuenta`
                , `tes_cuentas`.`nombre`
                , `tes_cuentas`.`id_banco`
            FROM
                `tes_cuentas`
                INNER JOIN `tb_bancos` 
                    ON (`tes_cuentas`.`id_banco` = `tb_bancos`.`id_banco`)
            WHERE (`tes_cuentas`.`id_banco` = $id_banco AND `tes_cuentas`.`estado` = 1)
            ORDER BY `tes_cuentas`.`nombre` ASC;";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <form id="formNuevaChequera">
        <input type="hidden" id="id_chequera" name="id_chequera" value="<?php echo $id_chequera; ?>">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;GESTION DE DATOS DE CHEQUERAS <?php echo ''; ?></h5>
            </div>
            <div class="py-3 px-3">
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="banco" class="small">BANCO: </label>
                    </div>
                    <div class="col-8">
                        <select id="banco" name="banco" class="form-control form-control-sm" required onchange="mostrarCuentas(value);">
                            <option value="0">--Seleccione--</option>
                            <?php foreach ($listabancos as $lb) {
                                $scl = $lb['id_banco'] == $id_banco ? 'selected' : '';
                                echo '<option value="' . $lb['id_banco'] . '" ' . $scl . '>' . $lb['nom_banco'] . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="cuentas" class="small">CUENTA: </label>
                    </div>
                    <div class="col-8" id="divBanco">
                        <select id="cuentas" name="cuentas" class="form-control form-control-sm">
                            <option value="0">--Seleccionar--</option>
                            <?php foreach ($retenciones as $ret) {
                                $scl = $ret['id_tes_cuenta'] == $cuenta ? 'selected' : '';
                                echo '<option value="' . $ret['id_tes_cuenta'] . '" ' . $scl . '>' . $ret['nombre'] . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="num_chequera" class="small">No. chequera: </label>
                    </div>
                    <div class="col-8">
                        <input type="text" id="num_chequera" name="num_chequera" class="form-control form-control-sm" value="<?php echo $numero; ?>">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="fecha" class="small">FECHA: </label>
                    </div>
                    <div class="col-8">
                        <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" required value="<?php echo $fecha; ?>">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="inicial" class="small">INICIAL: </label>
                    </div>
                    <div class="col-8">
                        <input type="text" id="inicial" name="inicial" class="form-control form-control-sm" value="<?php echo $inicial; ?>">
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-3 text-right">
                        <label for="maximo" class="small">FINAL: </label>
                    </div>
                    <div class="col-8">
                        <input type="text" id="maximo" name="maximo" class="form-control form-control-sm" value="<?php echo $maximo; ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-primary btn-sm" onclick="GuardarChequera()">Guardar</button>
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>