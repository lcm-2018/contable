<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];
include '../../../conexion.php';
$pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto si el id de la cuenta fue utilizado en seg_fin_chequera_cont
try {
    // consulto la cuenta con el id recibido en la tabla seg_ctb_pgcp
    $query = $pdo->prepare("SELECT cuenta FROM seg_ctb_pgcp WHERE id_pgcp = ?");
    $query->bindParam(1, $id);
    $query->execute();
    $res = $query->fetch();
    $cuenta = $res['cuenta'];
    $query = $pdo->prepare("SELECT id_ctb_libaux FROM ctb_libaux WHERE cuenta = ?");
    $query->bindParam(1, $cuenta);
    $query->execute();
    // consulto cuantos registros genera la sentencia
    if ($query->rowCount() > 0) {
        $response[] = array("value" => 'no');
    } else {
        // Consulto si la cuenta tiene dependientes
        $query = $pdo->prepare("SELECT id_ctb_libaux FROM ctb_libaux WHERE cuenta LIKE CONCAT(?, '%')");
        $query->bindParam(1, $cuenta);
        $query->execute();
        // consulto cuantos registros genera la sentencia
        if ($query->rowCount() > 0) {
            $response[] = array("value" => 'my');
        } else {
            try {
                $query = $pdo->prepare("DELETE FROM seg_ctb_pgcp WHERE id_pgcp = ? ");
                $query->bindParam(1, $id);
                $query->execute();
                $response[] = array("value" => 'ok', "id" => $cuenta);
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
