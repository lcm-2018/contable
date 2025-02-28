<?php
$data = file_get_contents("php://input");
include '../../../conexion.php';
// Inicio conexion a la base de datos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
} catch (Exception $e) {
    die("No se pudo conectar: " . $e->getMessage());
}
// Inicio transaccion 
try {
    $query = $cmd->prepare("DELETE FROM `pto_crp` WHERE `id_pto_crp` = ?");
    $query->bindParam(1, $data);
    $query->execute();
    if ($query->rowCount() > 0) {
        include '../../../financiero/reg_logs.php';
            $ruta = '../../../log';
            $consulta = "DELETE FROM `pto_crp` WHERE `id_pto_crp` = $data";
            RegistraLogs($ruta, $consulta);
        echo 'ok';
    } else {
        echo 'error:' . $query->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
