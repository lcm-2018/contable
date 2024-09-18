<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
include 'up_terceros.php';
$idter = isset($_POST['idTercero']) ? $_POST['idTercero'] : exit('Acción no permitida');
$tipotercero = $_POST['slcTipoTercero'];
$fecInicio = date('Y-m-d', strtotime($_POST['datFecInicio']));
$genero = $_POST['slcGenero'];
$fecNacimiento = date('Y-m-d', strtotime($_POST['datFecNacimiento']));
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
$iduser = $_SESSION['id_user'];
$tipouser = 'user';
$nit_act = $_SESSION['nit_emp'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
//API URL
$url = $api . 'terceros/datos/res/modificar/tercero/' . $idter;
$ch = curl_init($url);
$data = [
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
    "tipuser" => $tipouser,
    "nit_emp" => $nit_act
];
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$res = json_decode($result, true);
if ($res == '1' || $res == '0' || $fecInicio != '') {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $respuesta = UpTercerosEmpresa($api, [$idter], $cmd, $fecInicio);
    if ($respuesta == 'ok' || $res == '1') {
        echo 'ok';
    } else {
        echo $respuesta;
    }
} else {
    echo 'Respuesta: ' . $res;
}
