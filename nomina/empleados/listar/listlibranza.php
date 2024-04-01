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
$idlib = isset($_POST['idlib']) ? $_POST['idlib'] : exit('Acción no permitida');
$cont = 1;
$resp = "";
try {
  $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
  $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  $sql = "SELECT val_mes_lib, mes_lib, nom_mes, anio_lib, CONCAT(anio_lib, mes_lib)AS ordenar  
            FROM nom_liq_libranza,nom_meses 
            WHERE nom_meses.codigo = nom_liq_libranza.mes_lib AND id_libranza = '$idlib' 
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
      <th scope="col">Valor Libranza</th>
      <th scope="col">Mes</th>
      <th scope="col">Año</th>
    </tr>
  </thead>
  <tbody>';
foreach ($obj as $o) {
  $resp .= '<tr>
    <th scope="row">' . $cont . '</th>
    <td>' . pesos($o["val_mes_lib"]) . '</td>
    <td>' . $o["nom_mes"] . '</td>
    <td>' . $o["anio_lib"] . '</td>
    </tr>';
  $cont++;
}
$resp .= '</tbody>
</table>
<div class="form-row px-4">
  <div class="text-center col-md-12 pt-1">
    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cerrar</a>
  </div>
</div>';
echo $resp;
