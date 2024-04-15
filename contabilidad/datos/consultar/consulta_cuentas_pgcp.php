<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$_post = json_decode(file_get_contents('php://input'), true);
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Consultar si la cuenta existe en la tabla ctb_pgcp
$response = [];
try {
    $sql = "SELECT id_pgcp,cuenta,nombre,tipo_dato,estado FROM ctb_pgcp WHERE cuenta = '{$_post['codigo']}';";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetch();
    // Verificar si la consulta trajo datos
    if ($cuentas == false) {
        $response[] = array("datos" => 'vacio');
    } else {
        $response[] = array("datos" => "ok", "nombre" => $cuentas[2], "tipo" => $cuentas[3],);
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
$cmd = null;
