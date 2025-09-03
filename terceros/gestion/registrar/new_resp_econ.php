<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idt = isset($_POST['idTercero']) ? $_POST['idTercero'] : exit('Acción no permitida');
$id_resp_econ = $_POST['slcRespEcon'];
$iduser = isset($_SESSION['user']);
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $estado = 1;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_responsabilidad` FROM `ctt_resposabilidad_terceros` WHERE `id_tercero_api` = $idt AND `id_responsabilidad` = $id_resp_econ";
    $rs = $cmd->query($sql);
    $data = $rs->fetchAll();
    if (count($data) > 0) {
        echo 'La responsabilidad económica ya se encuentra registrada';
        exit();
    }
    $sql = "INSERT INTO `ctt_resposabilidad_terceros`
                (`id_tercero_api`,`id_responsabilidad`,`estado`,`id_user reg`,`fec_reg`)
            VALUES(? , ? , ? , ? , ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idt, PDO::PARAM_INT);
    $sql->bindParam(2, $id_resp_econ, PDO::PARAM_INT);
    $sql->bindParam(3, $estado, PDO::PARAM_INT);
    $sql->bindParam(4, $iduser, PDO::PARAM_INT);
    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

/* 
$url = $api . 'terceros/datos/res/nuevo/responsabilidad';
$ch = curl_init($url);
$data = [
    "id_terero" => $idt,
    "id_responsabilidad" => $id_resp_econ,
    "id_user" => $iduser,
    "tipo_user" => $tipouser,
    "nit_reg" => $doc_reg,
];
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$result = curl_exec($ch);
curl_close($ch);
$res = json_decode($result, true);
echo $res;
*/