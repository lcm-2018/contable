<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$datos = isset($_POST['ids']) ? explode("&", $_POST['ids']) : exit('Acción no permitida');
$id_c = $datos[0];
$entrega = $datos[1];
//API URL
$url = $api . 'terceros/datos/res/lista/compra_entregado/' . $id_c;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result = curl_exec($ch);
curl_close($ch);
$compra_entregada = json_decode($result, true);
//print_r($compra_entregada);
$recibido = $compra_entregada['entregas'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA ENTREGA # <?php echo $entrega ?></h5>
        </div>
        <div class="px-4 pt-4">
            <table id="tableLisTerCot" class="table table-striped table-bordered table-sm nowrap shadow" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th>Cantidad Recibida</th>
                        <th>Fecha Recibida</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($compra_entregada['listado'] as $ce) {
                        $j = '1';
                        echo '<tr>';
                        echo '<td>' . $ce['id_val_cot'] . '</td>';
                        echo '<td>' . $ce['bien_servicio'] . '</td>';
                        foreach ($recibido as $rc) {
                            if ($rc['id_val_cot'] == $ce['id_val_cot']) {
                                if ($j == $entrega) {
                                    $fec_recb = $rc['cantidad_entrega'] == 0 ? '' : $rc['fec_act'];
                                    echo '<td>' . $rc['cantidad_entrega'] . '</td>';
                                    echo '<td>' . $fec_recb . '</td>';
                                }
                                $j++;
                            }
                        }
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="form-row px-4 pt-2">
            <div class="text-center pb-3">
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cerrar</a>
            </div>
        </div>
    </div>
</div>