<?php
$id = $_POST['id'];
include '../../../conexion.php';
$response['status'] = 'error';
try {
    $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_arqueo FROM tes_ids_arqueo WHERE id_causa = ?";
    $query = $pdo->prepare($sql);
    $query->bindParam(1, $id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    $query = $pdo->prepare("DELETE FROM tes_causa_arqueo WHERE id_causa_arqueo = ?");
    $query->bindParam(1, $id);
    $query->execute();
    if ($query->rowCount() > 0) {
        $up = "UPDATE `fac_arqueo` SET `estado` = 2 WHERE `id_arqueo` = ?";
        $up = $pdo->prepare($up);
        $up->bindParam(1, $id_arqueo, PDO::PARAM_INT);
        foreach ($result as $row) {
            $id_arqueo = $row['id_arqueo'];
            $up->execute();
        }
        include '../../../financiero/reg_logs.php';
        $ruta = '../../../log';
        $consulta = "DELETE FROM tes_causa_arqueo WHERE id_causa_arqueo = $id";
        RegistraLogs($ruta, $consulta);
        $response['status'] = 'ok';
        $response['id'] = $id;
    } else {
        $response['msg'] = $query->errorInfo()[2];
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
