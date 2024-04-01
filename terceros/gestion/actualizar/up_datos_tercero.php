<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idter = isset($_POST['idTercero']) ? $_POST['idTercero'] : exit('Acción no permitida');
$idteremp = $_POST['idTerEmp'];
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
if ($res == '1' || $res == '0') {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE seg_terceros SET id_tipo_tercero = ?, tipo_doc = ?, no_doc = ?, fec_inicio = ? WHERE id_tercero = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $tipotercero, PDO::PARAM_INT);
        $sql->bindParam(2, $tipodoc, PDO::PARAM_INT);
        $sql->bindParam(3, $cc_nit, PDO::PARAM_STR);
        $sql->bindParam(4, $fecInicio, PDO::PARAM_STR);
        $sql->bindParam(5, $idteremp, PDO::PARAM_INT);
        $sql->execute();
        $cambio = $sql->rowCount();
        if (!($sql->execute())) {
            print_r($sql->errorInfo()[2]);
            exit();
        } else {
            if ($cambio > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE seg_terceros SET  id_user_act = ? ,fec_act = ? WHERE id_tercero = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $idteremp, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '1';
                } else {
                    print_r($sql->errorInfo()[2]);
                }
            } else {
                if ($res == '1') {
                    echo '1';
                } else {
                    echo '00';
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo $res;
}
