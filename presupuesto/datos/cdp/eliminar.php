<?php
    $data = file_get_contents("php://input");
    include '../../../conexion.php';
    try {
        $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $query = $pdo->prepare("DELETE FROM pto_documento_detalles WHERE id_pto_mvto = :id");
        $query->bindParam(":id", $data);
        $query->execute();
        include '../../../financiero/reg_logs.php';
        $ruta = '../../../log';
        $consulta =  "DELETE FROM pto_documento_detalles WHERE id_pto_mvto = $data";
        RegistraLogs($ruta, $consulta);
        echo "ok";
    }
    catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
?>