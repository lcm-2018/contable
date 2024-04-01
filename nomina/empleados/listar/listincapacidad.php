<?php

session_start();
if (!isset($_SESSION['user'])) {
  echo '<script>window.location.replace("../../../index.php");</script>';
  exit();
}
include '../../../conexion.php';

function pesos($value)
{
  return '$' . number_format($value, 2);
}

$idincap = isset($_POST['idincap']) ? $_POST['idincap'] : exit('Acción no permitida');
$cont = 1;
$resp = "";
try {
  $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
  $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  $sql = "SELECT fec_inicio, fec_fin, dias_liq, pago_empresa, pago_eps, pago_arl, mes, anios, CONCAT(anios, mes) AS ordenar
            FROM nom_liq_incap, nom_meses
            WHERE nom_meses.codigo = nom_liq_incap.mes AND id_incapacidad = '$idincap' 
            ORDER BY ordenar ASC";
  $rs = $cmd->query($sql);
  $obj = $rs->fetchAll();
  $cmd = null;
} catch (PDOException $e) {
  echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$resp .= '<table class="table table-striped table-bordered table-sm table-hover">
  <thead>
    <tr>
      <th scope="col">No.</th>
      <th scope="col">Fec. Inicia</th>
      <th scope="col">Fec. Fin</th>
      <th scope="col">Días Liq.</th>
      <th scope="col">Liq. Empresa</th>
      <th scope="col">Liq. EPS</th>
      <th scope="col">Liq. ARL</th>
      <th scope="col">Total Liq.</th>
    </tr>
  </thead>
  <tbody>';
$tdia = '0';
$tempr = '0';
$teps = '0';
$tarl = '0';
foreach ($obj as $o) {
  $resp .= '<tr>
    <th scope="row">' . $cont . '</th>
    <td>' . $o["fec_inicio"] . '</td>
    <td>' . $o["fec_fin"] . '</td>
    <td>' . $o["dias_liq"] . '</td>
    <td>' . pesos($o["pago_empresa"]) . '</td>
    <td>' . pesos($o["pago_eps"]) . '</td>
    <td>' . pesos($o["pago_arl"]) . '</td>
    <td>' . pesos($o["pago_empresa"] + $o["pago_eps"] + $o["pago_arl"]) . '</td>
    </tr>';
  $tdia = $tdia + $o["dias_liq"];
  $tempr = $tempr + $o["pago_empresa"];
  $teps = $teps + $o["pago_eps"];
  $tarl = $tarl + $o["pago_arl"];
  $cont++;
}
$resp .= '</tbody>
    <th scope="row" colspan="3" style="text-align: center">TOTAL</th>
    <td>' . $tdia . '</td>
    <td>' . pesos($tempr) . '</td>
    <td>' . pesos($teps) . '</td>
    <td>' . pesos($tarl) . '</td>
    <td></td>
</table>';
echo $resp;
