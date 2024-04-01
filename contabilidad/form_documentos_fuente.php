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
    <form id="formNuevaCuentaContable">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;GESTION DE PLAN DE CUENTAS CONTABLE <?php echo ''; ?></h5>
            </div>
            <div class="pt-3 px-3">
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">CUENTA CONTABLE: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="btn-group">
                            <div class="mr-1"><input type="text" id="cuentas" name="cuentas" class="form-control  form-control-sm" value="<?php echo ''; ?>" onkeydown="soloNumeros(event)" onkeyup="buscaCuentaPgcp(value);" onchange="verificarNivel(value);"></div>
                            <div><a class="btn btn-outline-success btn-sm btn-circle shadow-gb" onclick="buscarCuentaPlan();"><span class="fas fa-search-plus fa-lg"></span></a></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="nombre" class="small">NOMBRE: </label></div>
                    </div>
                    <div class="col-9">
                        <div class="col" id="divBanco">
                            <input type="text" id="nombre" name="nombre" class="form-control form-control-sm" value="<?php echo ''; ?>">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="num" class="small">TIPO CUENTA: </label></div>
                    </div>
                    <div class="col-3">
                        <div class="col">
                            <select id="tipo" name="tipo" class="form-control form-control-sm" required>
                                <option value="">Seleccione...</option>
                                <option value="M">M - Mayor</option>
                                <option value="D">D - Detalle</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">NIVEL: </label></div>
                    </div>
                    <div class="col-2">
                        <div class="col">
                            <input type="number" id="numero" name="numero" class="form-control form-control-sm" value="">

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
            <button type="button" class="btn btn-primary btn-sm" onclick="guardarPlanCuentas()">Enviar</button>
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>
<!-- Este es un cambio para probar el git -->