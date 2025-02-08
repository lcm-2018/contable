<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';

$id_ret = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');
$base = $_POST['base'];
$id_vigencia = $_SESSION['id_vigencia'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    $sql = "SELECT `tarifa` 
            FROM `ctb_retencion_rango` 
            WHERE `id_retencion` = $id_ret AND `id_vigencia` = $id_vigencia";
    $rs = $cmd->query($sql);
    $tarifa = $rs->fetch();
    $tf = !empty($tarifa) ? $tarifa['tarifa'] : 0;
    $valor = $base * $tf;
    $response = [
        'status' => 'ok',
        'pesos' => number_format($valor, 2, '.', ',')
    ];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
