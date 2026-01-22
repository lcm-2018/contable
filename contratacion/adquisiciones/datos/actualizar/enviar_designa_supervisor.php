<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';

if (isset($_FILES['fileSup'])) {
    $id_superv = $_POST['id_supervision'];
    $id_compra = $_POST['id_compra'];
    $iduser = $_SESSION['id_user'];
    $tipuser = 'user';
    $nom_archivo = 'ads' . '_' . date('YmdGis') . '_' . $_FILES['fileSup']['name'];
    $nom_archivo = strlen($nom_archivo) >= 101 ? substr($nom_archivo, 0, 100) : $nom_archivo;
    $temporal = file_get_contents($_FILES['fileSup']['tmp_name']);
    $temporal = base64_encode($temporal);
    //API URL
    $url = $api . 'terceros/datos/res/nuevo/documento/designacion_supervision';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = [
        "id_superv" => $id_superv,
        "iduser" => $iduser,
        "tipuser" => $tipuser,
        "nom_archivo" => $nom_archivo,
        "temporal" => $temporal,
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
    if ($res['estado'] == 1) {
        try {
            $estado = 10;
            $date = new DateTime('now', new DateTimeZone('America/Bogota'));
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `ctt_adquisiciones` SET `estado`= ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $estado, PDO::PARAM_INT);
            $sql->bindParam(2, $iduser, PDO::PARAM_INT);
            $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(4, $id_compra, PDO::PARAM_INT);
            $sql->execute();
            if (!($sql->rowCount() > 0)) {
                echo $sql->errorInfo()[2];
            } else {
                echo 1;
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        echo $res['response'];
    }
} else {
    echo 'No se ha adjuntado ningún archivo';
}
