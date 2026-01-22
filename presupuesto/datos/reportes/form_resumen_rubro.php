<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$id_cargue = isset($_POST['rubro']) ? $_POST['rubro'] : exit('Acceso no permitido');
$fecha = $_POST['fecha'];
$id_cdp = 0;

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT `cod_pptal`, `nom_rubro`, `valor_aprobado`, `id_pto` 
            FROM `pto_cargue` 
            WHERE `id_cargue` = $id_cargue";
    $res = $cmd->query($sql);
    $row = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$valores = SaldoRubro($cmd, $id_cargue, $fecha, $id_cdp);
$saldo =  $valores['valor_aprobado'] - $valores['debito_cdp'] + $valores['credito_cdp'] + $valores['debito_mod'] - $valores['credito_mod'];
$saldo = number_format($saldo, 2, '.', ',');
$cmd = null;
?>
<div class="px-0">
    <form id="formFechaSesion">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;EJECUCIÓN DEL RUBRO</h5>
            </div>
            <div class="pt-3 px-1">
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-10 text-left">
                        <label for="passAnt" class="small"><strong>Rubro : </strong><?php echo ' ' . $row['cod_pptal'] . ' - ' . $row['nom_rubro']; ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Presupuesto Definitivo:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($row['valor_aprobado'], 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small"><strong>Saldo:</strong></label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><strong><?php echo $saldo  ?></strong></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                    </div>
                </div>

            </div>
        </div>
        <div class="text-right">
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>