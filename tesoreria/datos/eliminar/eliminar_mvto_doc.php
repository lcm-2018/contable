<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];

include '../../../conexion.php';

// Incio la transaccion
$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    //---------------- eliminar pto_rad, pto_rec, pto_rad_detalle y pto_rec_detalle con id_ctb_doc
    $id_pto_rec = 0;
    $id_pto_rad = 0;

    $sql = "SELECT id_pto_rec FROM pto_rec WHERE id_ctb_doc = $id LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_id_pto_rec = $rs->fetch();

    if (!empty($obj_id_pto_rec)) {
        $id_pto_rec = $obj_id_pto_rec['id_pto_rec'];
    }

    $sql = "DELETE FROM pto_rec_detalle WHERE id_pto_rac = $id_pto_rec";
    $rs = $cmd->query($sql);

    $sql = "DELETE FROM pto_rec WHERE id_ctb_doc = $id";
    $rs = $cmd->query($sql);
 
    $sql = "SELECT id_pto_rad FROM pto_rad WHERE id_ctb_doc = $id LIMIT 1";
    $rs = $cmd->query($sql);
    $obj_id_pto_rad = $rs->fetch();

    if(!empty($obj_id_pto_rad)){
        $id_pto_rad = $obj_id_pto_rad['id_pto_rad'];
    }

    $sql = "DELETE FROM pto_rad_detalle WHERE id_pto_rad = $id_pto_rad";
    $rs = $cmd->query($sql);

    $sql = "DELETE FROM pto_rad WHERE id_ctb_doc=" . $id;
    $rs = $cmd->query($sql);
    
    if ($rs) {
        $res['mensaje'] = 'ok';
    } else {
        $res['mensaje'] = $cmd->errorInfo()[2];
    }
    //------------------------------------------------------------

    $query = "DELETE FROM `ctb_doc` WHERE `id_ctb_doc` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id);
    $query->execute();
    if ($query->rowCount() > 0) {
        include '../../../financiero/reg_logs.php';
        $ruta = '../../../log';
        $consulta = "DELETE FROM `ctb_doc` WHERE `id_ctb_doc` = $id";
        RegistraLogs($ruta, $consulta);
        $response['status'] = 'ok';
    } else {
        $response['msg'] = 'Error: ' . $query->errorInfo()[2];
    }

    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
