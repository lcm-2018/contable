<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$_post = json_decode(file_get_contents('php://input'), true);
$id_cargue = intval($_post['rubro']);
$vigencia = $_SESSION['vigencia'];
$fecha_fin = $vigencia . '-12-31';
try {
    $sql = "SELECT `cod_pptal`, `nom_rubro`, `valor_aprobado`, `id_pto` 
            FROM `pto_cargue` 
            WHERE `id_cargue` = $id_cargue";
    $res = $cmd->query($sql);
    $row = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $sql = "SELECT `id_tmvto`,`nombre` FROM `pto_tipo_mvto`";
    $res = $cmd->query($sql);
    $movimientos = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (empty($row)) {
    $row['cod_pptal'] = '';
    $row['nom_rubro'] = '';
    $row['valor_aprobado'] = 0;
    $row['id_pto'] = 0;
}
$saldos = saldoRubroGastos($vigencia, $id_cargue, $cmd);
$cmd = null;
?>
<div class="px-0">
    <form id="formFechaSesion">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;HISTORIAL DE EJECUCIÓN DEL RUBRO</h5>
            </div>
            <div class="pt-3 px-3">
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
                        <label for="passAnt" class="small">Presupuesto inicial:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($row['valor_aprobado'], 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <?php
                $suma = 0;
                foreach ($saldos as $s) {
                    $key = array_search($s['id_tipo_mod'], array_column($movimientos, 'id_tmvto'));
                    $nombre = $key !== false ? $movimientos[$key]['nombre'] : '';
                    $valor = $s['debito'] - $s['credito'];
                    $suma += $valor;
                ?>
                    <div class="row">
                        <div class="col-md-1"></div>
                        <div class="col-md-6 text-left">
                            <label for="passAnt" class="small"><?php echo $nombre ?>:</label>
                        </div>
                        <div class="col-md-4 text-right">
                            <label for="passAnt" class="small"><?php echo number_format($valor, 2, ',', '.'); ?></label>
                        </div>
                        <div class="col-md-1"></div>
                    </div>
                <?php
                }
                ?>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small"><strong>Saldo:</strong></label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><strong><?php echo number_format($row['valor_aprobado'] - $suma, 2, ',', '.');  ?></strong></label>
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