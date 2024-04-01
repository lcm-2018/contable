<?php
session_start();
/*if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}*/
include '../../../conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../../libs/PHPMailer/src/Exception.php';
require_once '../../../libs/PHPMailer/src/PHPMailer.php';
require_once '../../../libs/PHPMailer/src/SMTP.php';
/*
$empleados = [
    "8201345" => "robertoramirez076@gmail.com",
    "9654581" => "roldanperezj41@gmail.com",
    "17388640" => "jdcpedroantoniodaza@hotmail.com",
    "21202533" => "nataliacontreras4240@gmail.com",
    "23790065" => "rosavargas0364@gmail.com",
    "24228221" => "candelariapabon69@gmail.com",
    "24228291" => "nebepe15@yahoo.es",
    "24231359" => "morenoanadelia19@hotmail.com",
    "33645761" => "lialgil@yahoo.es",
    "39948824" => "socorrobarrera2275@hotmail.com",
    "40328118" => "pscarol_hernandez83@hotmail.com",
    "43796165" => "sorany2505@gmail.com",
    "47426733" => "carmenalarcon1204@hotmail.com",
    "50900069" => "noramendozab@hotmail.com",
    "51696157" => "emma_sc5169@hotmail.com",
    "74750661" => "beller.rivera2762@gmail.com  ",
    "74752929" => "YOMAHECHA@HOTMAIL.COM",
    "74754406" => "espinosaotalora@gmail.com",
    "74755059" => "chaparro0527123@gmail.com",
    "1006423713" => "gijany2010@hotmail.com",
    "1006460651" => "nvelosa51@uan.edu.co",
    "1006599186" => "DEISYJULIEX@GMAIL.COM",
    "1052407777" => "laurita.x96@hotmail.com ",
    "1116542865" => "vanesa1986@outlook.es",
    "1116550309" => "andreadiaz1029@hotmail.com",
    "1116552466" => "gisseld32@gmail.com",
    "1121822830" => "leidyguzmanperez@gmail.com",
    "1143406963" => "kmql@outlook.com",
    "74859481" => "esanabriagomez@yahoo.com",
    "1116553418" => "tatisvela1776@gmail.com",
    "4295312" => "echeverria631@hotmail.com",
    "47430618" => "mirirubi1972@gmail.com",
    "1002603299" => "linarojas172003@gmail.com"
];
$cont = 1;

foreach ($empleados as $key => $em) {
    $consecutivo = $key;
    $to = $em;
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];                                           //Send using SMTP
    $mail->Host       = 'mail.lcm.com.co';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'mail@lcm.com.co';                     //SMTP username
    $mail->Password   = 'Lcm2021*';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('mail@lcm.com.co', 'Info-LCM');
    $mail->addAddress($to);     //Add a recipient
    // $mail->addAddress('ellen@example.com');               //Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    $mail->addAttachment($consecutivo . '.pdf');         //Add attachments
    $mail->addAttachment($consecutivo . '.pdf', $consecutivo . '.dpf');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Documento soporte nomina electrónica ' . $consecutivo;
    $mail->Body    = 'Se adjunta documento soporte de nomina electrónica correpondiente al mes de enero de 2023 del Hospital de Aguazul Juan Hernandi Urrego.';
    $mail->AltBody = '';

    $mail->send();
    echo 'Message has been sent ' . $cont;
    $cont++;
}

exit();*/

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
$mail = $_POST['mailEmp'];
$tel = $_POST['txtTelEmp'];
$estado = '1';
$iduser = $_SESSION['id_user'];
$tipouser = 'user';
$nit_crea = $_SESSION['nit_emp'];
$pass = $_POST['passT'];
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
        "mailEmp" => $mail,
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
        $mail = new PHPMailer(true);
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
    }
}
if ($res > 1 || $regAtTerc == 'SI') {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO seg_terceros(`tipo_doc`, `id_tercero_api`, `no_doc`, `estado`, `fec_inicio`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $tipodoc, PDO::PARAM_INT);
        $sql->bindParam(2, $id_ter_api, PDO::PARAM_INT);
        $sql->bindParam(3, $cc_nit, PDO::PARAM_STR);
        $sql->bindParam(4, $estado, PDO::PARAM_STR);
        $sql->bindParam(5, $fecInicio, PDO::PARAM_STR);
        $sql->bindParam(6, $iduser, PDO::PARAM_INT);
        $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $sql = "INSERT INTO `tb_rel_tercero` (`id_tercero_api`, `id_tipo_tercero`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_ter_api, PDO::PARAM_INT);
            $sql->bindParam(2, $tipotercero, PDO::PARAM_STR);
            $sql->bindParam(3, $iduser, PDO::PARAM_INT);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                echo '1';
            } else {
                print_r($sql->errorInfo()[2]);
            }
        } else {
            print_r($sql->errorInfo()[2]);
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo 'No se pudo Registrar';
}
