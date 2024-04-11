<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// Consultar si la cuenta existe en la tabla ctb_pgcp
$response = [];
try {
    $sql = "SELECT `id_pgcp`,`fecha`,`cuenta`,`nombre`,`tipo_dato`,`estado` 
            FROM `ctb_pgcp` 
            WHERE `cuenta` = '{$_post['codigo']}'";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetch();
    // Verificar si la consulta trajo datos
    if (empty($cuentas)) {
        $response[] = array("datos" => 'vacio');
    } else {
        if ($cuentas['tipo_dato'] == 'M') {
            // Buscar la ultima cuenta o cuenta vacia de la tabla ctb_pgcp
            $sql = "SELECT MAX(cuenta) FROM ctb_pgcp WHERE cuenta LIKE  '{$_post['codigo']}%';";
            $rs = $cmd->query($sql);
            $cuentas = $rs->fetch();
            $response[] = array("datos" => "ok", "cuenta" => $cuentas[0] + 1);
        } else {
            $response[] = array("datos" => 'Auxiliar');
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
$cmd = null;
