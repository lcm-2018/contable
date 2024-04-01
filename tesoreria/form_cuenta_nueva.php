<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
$id = $_POST['id'] ?? '';
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
// Estabelcer zona horaria bogota
date_default_timezone_set('America/Bogota');
// insertar fecha actual
$fecha = date("Y-m-d");
$id_banco = '';
$id_pgcp = '';
$numero = '';
$nombre = '';
$cta_contable = '';
// consultar la fecha de cierre del periodo del módulo de presupuesto 
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
if (isset($_POST['id'])) {
    try {
        $sql = "SELECT
          seg_tes_cuentas.id_banco
        , seg_tes_cuentas.id_tipo_cuenta
        , seg_tes_cuentas.cta_contable
        , seg_tes_cuentas.nombre
        , seg_tes_cuentas.numero
        , seg_tes_cuentas.estado
        , seg_tes_cuentas.id_tes_cuenta
        , seg_tes_cuentas.id_pgcp

    FROM
        seg_tes_cuentas
    WHERE id_tes_cuenta =$id;";
        $rs = $cmd->query($sql);
        $cuentas = $rs->fetch();
        $id_banco = $cuentas['id_banco'];
        $id_tipo_cuenta = $cuentas['id_tipo_cuenta'];
        $id_pgcp = $cuentas['id_pgcp'];
        $cta_contable = $cuentas['cta_contable'];
        $nombre = $cuentas['nombre'];
        $numero = $cuentas['numero'];
        $estado = $cuentas['estado'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
// Consultar el listado de bancos de la tabla tb_bancos
try {
    $sql = "SELECT id_banco, nom_banco FROM tb_bancos ORDER BY nom_banco ASC";
    $rs = $cmd->query($sql);
    $listabancos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el listado de tipo de cuenta de la tabla seg_tes_tipocuenta
try {
    $sql = "SELECT id_tipo_cuenta, tipo_cuenta FROM seg_tes_tipocuenta ORDER BY tipo_cuenta ASC";
    $rs = $cmd->query($sql);
    $listatipocuenta = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <form id="formNuevaCuenta">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;GESTION DE CUENTAS BANCARIAS <?php echo ''; ?></h5>
            </div>
            <div class="pt-3 px-3">
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">BANCO: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="col">
                            <select id="banco" name="banco" class="form-control form-control-sm" required onchange="mostrarCuentasPendiente(value);">
                                <option value="">Seleccione...</option>
                                <?php foreach ($listabancos as $lb) {
                                    if ($lb['id_banco'] == $id_banco) { ?>
                                        <option value=" <?php echo $lb['id_banco']; ?>" selected><?php echo $lb['nom_banco']; ?></option>
                                    <?php } else { ?>

                                        <option value=" <?php echo $lb['id_banco']; ?>"><?php echo $lb['nom_banco']; ?></option>

                                <?php }
                                } ?>
                            </select>
                            <input type="hidden" id="id_cuenta" name="id_cuenta" value="<?php echo $id; ?>">
                            <input type="hidden" id="id_pgcp" name="id_pgcp" value="<?php echo $id_pgcp; ?>">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">CUENTA CONTABLE: </label></div>
                    </div>
                    <div class="col-8">
                        <div class="col" id="divBanco">
                            <input type="text" id="cuentas" name="cuentas" class="form-control form-control-sm" value="<?php echo $nombre; ?>">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="num" class="small">TIPO CUENTA: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="col">
                            <!-- Realizar un select con $listatipocuenta -->
                            <select id="tipo_cuenta" name="tipo_cuenta" class="form-control form-control-sm" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($listatipocuenta as $lb) {
                                    if ($lb['id_tipo_cuenta'] == $id_tipo_cuenta) { ?>
                                        <option value=" <?php echo $lb['id_tipo_cuenta']; ?>" selected><?php echo $lb['tipo_cuenta']; ?></option>
                                    <?php } else { ?>

                                        <option value=" <?php echo $lb['id_tipo_cuenta']; ?>"><?php echo $lb['tipo_cuenta']; ?></option>
                                <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">NUMERO DE CUENTA: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="col">
                            <input type="text" id="numero" name="numero" class="form-control form-control-sm" value="<?php echo $numero; ?>">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-12">
                    </div>
                </div>



            </div>
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-primary btn-sm" onclick="guardarCuentaBanco()">Enviar</button>
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>