<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';

if (isset($_FILES['fileContrato'])) {
    $id_contrato = $_POST['id_contrato_s'];
    $id_compra = $_POST['id_compra_s'];
    $nit_empres = $_POST['nit_empresa_s'];
    $doc_tercero = $_POST['doc_tercero_s'];
    $val_contrato = $_POST['val_contrato_s'];
    $iduser = $_SESSION['id_user'];
    $tipuser = 'user';
    $nom_archivo = 'cce' . '_' . date('YmdGis') . '_' . $_FILES['fileContrato']['name'];
    $nom_archivo = strlen($nom_archivo) >= 101 ? substr($nom_archivo, 0, 100) : $nom_archivo;
    $temporal = file_get_contents($_FILES['fileContrato']['tmp_name']);
    $temporal = base64_encode($temporal);
    //API URL
    $url = $api . 'terceros/datos/res/nuevo/contrato';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = [
        "contrato" => $id_contrato,
        "compra" => $id_compra,
        "empresa" => $nit_empres,
        "tercero" => $doc_tercero,
        "valor" => $val_contrato,
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
    $result = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($result, true);
    if ($res['estado'] == 1) {
        try {
            $estado = 8;
            $id_con_api = $res['response'];
            $date = new DateTime('now', new DateTimeZone('America/Bogota'));
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `ctt_adquisiciones` SET `estado`= ?, `id_cont_api` = ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $estado, PDO::PARAM_INT);
            $sql->bindParam(2, $id_con_api, PDO::PARAM_INT);
            $sql->bindParam(3, $iduser, PDO::PARAM_INT);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $id_compra, PDO::PARAM_INT);
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
