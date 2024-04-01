<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];
include '../../../conexion.php';
$pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto si el id de la chequera fue utilizado en seg_fin_chequera_cont
try {
    $query = $pdo->prepare("SELECT id_chequera FROM seg_fin_chequera_cont WHERE id_chequera = ?");
    $query->bindParam(1, $id);
    $query->execute();
    // consulto cuantos registros genera la sentencia
    if ($query->rowCount() > 0) {
        $response[] = array("value" => 'no');
    } else {
        try {
            $query = $pdo->prepare("DELETE FROM seg_fin_chequeras WHERE id_chequera = ? ");
            $query->bindParam(1, $id);
            $query->execute();
            $response[] = array("value" => 'ok');
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
