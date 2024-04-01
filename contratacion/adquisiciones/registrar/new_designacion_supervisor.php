<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
if (!isset($_POST['id_con_final']) || $_POST['id_con_final'] == '0') {
    $id_contrato = NULL;
} else {
    $id_contrato = $_POST['id_con_final'] = $_POST['id_contrato'];
}
$id_tercero = $_POST['id_ter_sup'];
$id_adquisicion = $_POST['id_adquisicion'];
$fec_desig = $_POST['datFecDesigSup'];
$memorando = $_POST['numMemorando'];
$observacion = $_POST['txtaObservaciones'];
$iduser = $_SESSION['id_user'];
$tipouser = 'user';
$data = [
    "id_contrato" => $id_contrato,
    "id_tercero" => $id_tercero,
    "fec_desig" => $fec_desig,
    "memorando" => $memorando,
    "observacion" => $observacion,
    "iduser" => $iduser,
    "tipouser" => $tipouser,
];
//API URL
$url = $api . 'terceros/datos/res/nuevo/contrato/designa_supervisor';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resp = curl_exec($ch);
curl_close($ch);
$res = json_decode($resp, true);
if ($res['status'] == 1) {
    try {
        $id_spvr = $res['msg'];
        $estado = 8;
        $date = new DateTime('now', new DateTimeZone('America/Bogota'));
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `ctt_adquisiciones` SET `id_supervision` = ?, `estado`= ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_spvr, PDO::PARAM_INT);
        $sql->bindParam(2, $estado, PDO::PARAM_INT);
        $sql->bindParam(3, $iduser, PDO::PARAM_INT);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(5, $id_adquisicion, PDO::PARAM_INT);
        $sql->execute();
        if (!($sql->rowCount() > 0)) {
            print_r($sql->errorInfo()[2]);
        } else {
            echo 1;
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo $res['msg'];
}
