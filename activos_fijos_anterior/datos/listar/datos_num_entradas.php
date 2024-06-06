<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_c = isset($_POST['ids']) ? $_POST['ids'] : exit('Acción no permitida');
$datas = explode('|', $id_c);
$id_adq = $datas[2];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_adquisicion`, `entregas` FROM `ctt_adquisiciones` WHERE `id_adquisicion` = '$id_adq'";
    $rs = $cmd->query($sql);
    $cant_entregas = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
//API URL
$url = $api . 'terceros/datos/res/lista/compra_entregado/' . $id_c;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$compra_entregada = json_decode($result, true);
if (!empty($compra_entregada)) { ?>
    <div class="px-0">
        <div class="shadow">
            <div class="card-header mb-3" style="background-color: #16a085 !important;">
                <h5 style="color: white;">ENTRADAS PENDIENTES</h5>
            </div>
            <table class="table-striped table-bordered table-sm nowrap table-hover shadow" width="100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>DESCRIPCIÓN</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>
                <tbody id="detallesXEntrega">
                    <?php
                    for ($i = 1; $i <= $compra_entregada['num_entregas']['entregas']; $i++) {
                        echo '<tr>';
                        echo '<td>' . $i . ' </td>';
                        echo '<td>Entrega #' . $i . ' </td>';
                        $detalles = $i > $cant_entregas['entregas'] ? '<a value="' . $id_c . '|' . $i . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb genera_recepcion" title="Iniciar Proceso de Entrada"><span class="fas fa-newspaper fa-lg"></span></a>' : 'REGISTRADO';
                        echo '<td><div clasS="text-center">' . $detalles . '</div></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="text-center pt-3">
            <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
        </div>
    </div>
<?php
} else {
    echo 'Error al intentar obtener entregas';
}
