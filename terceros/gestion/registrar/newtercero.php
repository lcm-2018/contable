<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}*/
include '../../../conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../../libs/PHPMailer/src/Exception.php';
require_once '../../../libs/PHPMailer/src/PHPMailer.php';
require_once '../../../libs/PHPMailer/src/SMTP.php';

$tipotercero = isset($_POST['slcTipoTercero']) ? $_POST['slcTipoTercero'] : exit('Acción no permitida');
$fecInicio = date('Y-m-d', strtotime($_POST['datFecInicio']));
$genero = $_POST['slcGenero'];
if ($_POST['datFecNacimiento'] == '') {
    $fecNacimiento = NULL;
} else {
    $fecNacimiento = date('Y-m-d', strtotime($_POST['datFecNacimiento']));
}
$tipodoc = $_POST['slcTipoDocEmp'];
$cc_nit = $_POST['txtCCempleado'];
$nomb1 = $_POST['txtNomb1Emp'];
$nomb2 = $_POST['txtNomb2Emp'];
$ape1 = $_POST['txtApe1Emp'];
$ape2 = $_POST['txtApe2Emp'];
$razonsoc = $_POST['txtRazonSocial'];
$pais = $_POST['slcPaisEmp'];
$dpto = $_POST['slcDptoEmp'];
$municip = $_POST['slcMunicipioEmp'];
$dir = $_POST['txtDireccion'];
$mail_persona = $_POST['mailEmp'];
$tel = $_POST['txtTelEmp'];
$es_clinic = $_POST['rdo_esasist'];
$estado = '1';
$iduser = $_SESSION['id_user'];
$tipouser = 'user';
$nit_crea = $_SESSION['nit_emp'];
$pass = $_POST['passT'];
$planilla = $_POST['rdo_planilla'];
$riesgo = isset($_POST['slcRiesgoLab']) ? $_POST['slcRiesgoLab'] : 0;
$riesgo = $planilla == 0 ? NULL : $riesgo;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
//API URL
$url = $api . 'terceros/datos/res/lista/' . $cc_nit;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
$regAtTerc = 'NO';
$res = '';
$id_ter_api = NULL;
if ($terceros != '0') {
    $regAtTerc = 'SI';
    $id_ter_api = $terceros[0]['id_tercero'];
} else {
    //API URL
    $url = $api . 'terceros/datos/res/nuevo';
    $ch = curl_init($url);
    $data = [
        "slcTipoTercero" => $tipotercero,
        "slcGenero" => $genero,
        "datFecNacimiento" => $fecNacimiento,
        "slcTipoDocEmp" => $tipodoc,
        "txtCCempleado" => $cc_nit,
        "txtNomb1Emp" => $nomb1,
        "txtNomb2Emp" => $nomb2,
        "txtApe1Emp" => $ape1,
        "txtApe2Emp" => $ape2,
        "txtRazonSocial" => $razonsoc,
        "slcPaisEmp" => $pais,
        "slcDptoEmp" => $dpto,
        "slcMunicipioEmp" => $municip,
        "txtDireccion" => $dir,
        "mailEmp" => $mail_persona,
        "txtTelEmp" => $tel,
        "id_user" => $iduser,
        "nit_emp" => $nit_crea,
        "pass" => $pass,
    ];
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($result, true);
    $id_ter_api = $res;
    if ($id_ter_api > 0) {
        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 0;  // Desactiva la depuración para evitar errores en pantalla
            $mail->isSMTP();
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            $mail->Host       = 'mail.lcm.com.co';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'mail@lcm.com.co';
            $mail->Password   = 'Lcm2021*';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom('mail@lcm.com.co', 'Info-LCM');
            $mail->addAddress($data['mailEmp']);
            $mail->isHTML(true);
            $mail->Subject = 'Registro de tercero';
            $mail->Body    = 'Usted ha sido registrado como tercero, por favor ingrese al sistema para validar su información con los siguientes datos: <br> Usuario: ' . $cc_nit . '<br> Contraseña: Corresponde al mismo numero de identificación del tercero.<br> <a href="http://200.7.102.155/suite_terceros/index.php">Ingresar</a>';
            $mail->AltBody = '';

            $mail->send();
        } catch (Exception $e) {
            // No hacer nada en caso de error para que el flujo continúe sin interrupciones
        }
    }
}
if ($res > 1 || $regAtTerc == 'SI') {
    try {
        $estado = 1;
        $nombre = trim($nomb1 . ' ' . $nomb2 . ' ' . $ape1 . ' ' . $ape2 . ' ' . $razonsoc);
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `tb_terceros`
                    (`tipo_doc`,`nom_tercero`,`nit_tercero`,`dir_tercero`,`tel_tercero`,`id_municipio`,`email`,`id_usr_crea`,`id_tercero_api`,`estado`,`fec_inicio`,`es_clinico`,`planilla`, `id_riesgo`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $tipodoc, PDO::PARAM_STR);
        $sql->bindParam(2, $nombre, PDO::PARAM_STR);
        $sql->bindParam(3, $cc_nit, PDO::PARAM_STR);
        $sql->bindParam(4, $dir, PDO::PARAM_STR);
        $sql->bindParam(5, $tel, PDO::PARAM_STR);
        $sql->bindParam(6, $municip, PDO::PARAM_INT);
        $sql->bindParam(7, $mail_persona, PDO::PARAM_STR);
        $sql->bindParam(8, $iduser, PDO::PARAM_INT);
        $sql->bindParam(9, $id_ter_api, PDO::PARAM_INT);
        $sql->bindParam(10, $estado, PDO::PARAM_INT);
        $sql->bindParam(11, $fecInicio, PDO::PARAM_STR);
        $sql->bindParam(12, $es_clinic, PDO::PARAM_INT);
        $sql->bindParam(13, $planilla, PDO::PARAM_INT);
        $sql->bindParam(14, $riesgo, PDO::PARAM_INT);
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $cmd = NULL;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = "INSERT INTO `tb_rel_tercero`
                        (`id_tercero_api`,`id_tipo_tercero`,`id_user_reg`,`fec_reg`)
                    VALUES(?, ?, ?, ?)";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $id_ter_api, PDO::PARAM_INT);
            $query->bindParam(2, $tipotercero, PDO::PARAM_STR);
            $query->bindParam(3, $iduser, PDO::PARAM_INT);
            $query->bindValue(4, $date->format('Y-m-d H:i:s'));
            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                echo 'ok';
            } else {
                echo $query->errorInfo()[2] . '-.-';
            }
        } else {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo 'No se pudo Registrar';
}
