<?php
$_post = json_decode(file_get_contents('php://input'), true);
include '../../../conexion.php';
$id = $_post['id'];
$pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$response['value'] = 'error';
// consulto si el id de la cuenta fue utilizado en seg_fin_chequera_cont
try {
    // consulto la cuenta con el id recibido en la tabla ctb_pgcp
    $query = "SELECT `id_ctb_libaux` FROM `ctb_libaux` WHERE `id_cuenta` = ?";
    $query = $pdo->prepare($query);
    $query->bindParam(1, $id);
    $query->execute();
    // consulto cuantos registros genera la sentencia
    if ($query->rowCount() > 0) {
        $response['msg'] = 'Cuenta contiene registros asociados';
    } else {
        $query = $pdo->prepare("DELETE FROM ctb_pgcp WHERE id_pgcp = ? ");
        $query->bindParam(1, $id);
        $query->execute();
        if ($query->rowCount() > 0) {
            include '../../../financiero/reg_logs.php';
            $ruta = '../../../log';
            $consulta = "DELETE FROM ctb_pgcp WHERE id_pgcp = $id";
            RegistraLogs($ruta, $consulta);
            $response['value'] = 'ok';
            $response['msg'] = 'Cuenta eliminada correctamente';
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
