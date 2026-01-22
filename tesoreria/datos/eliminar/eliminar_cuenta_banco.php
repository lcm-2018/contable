<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];
include '../../../conexion.php';
// consulto si el id de la cuenta fue utilizado en seg_fin_chequera_cont
$response['status'] = 'error';
try {
    $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = "SELECT `id_tes_cuenta` FROM `tes_detalle_pago` WHERE `id_tes_cuenta` = ?";
    $query = $pdo->prepare($query);
    $query->bindParam(1, $id);
    $query->execute();
    // consulto cuantos registros genera la sentencia
    if ($query->rowCount() > 0) {
        $response['msg'] = 'La cuenta no se puede eliminar porque tiene registros asociados';
    } else {
        try {
            $sql = "DELETE FROM `tes_cuentas` WHERE `id_tes_cuenta` = ?";
            $sql = $pdo->prepare($sql);
            $sql->bindParam(1, $id);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                include '../../../financiero/reg_logs.php';
                $ruta = '../../../log';
                $consulta = "DELETE FROM `tes_cuentas` WHERE `id_tes_cuenta` = $id";
                RegistraLogs($ruta, $consulta);
                $response['status'] = 'ok';
            } else {
                $response['msg'] = $pdo->errorInfo()[2];
            }
            $pdo = null;
        } catch (PDOException $e) {
            $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
    }
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
