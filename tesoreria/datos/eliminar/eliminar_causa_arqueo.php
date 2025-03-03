<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];
include '../../../conexion.php';
try {
    $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = $pdo->prepare("DELETE FROM tes_causa_arqueo WHERE id_causa_arqueo = ?");
    $query->bindParam(1, $id);
    $query->execute();
    if ($query->rowCount() > 0) {
        include '../../../financiero/reg_logs.php';
        $ruta = '../../../log';
        $consulta = "DELETE FROM tes_causa_arqueo WHERE id_causa_arqueo = $id";
        RegistraLogs($ruta, $consulta);
        $response[] = array("value" => 'ok', "id" => $id);
    } else {
        print_r($query->errorInfo()[2]);
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
