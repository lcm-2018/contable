<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$opcion = isset($_POST['opcion']) ? $_POST['opcion'] : exit('Accion no permitida');
$id_contrato = $_POST['id_compra'];
$tip_nov = $_POST['slcTipoNovedad'];
$observacion = $_POST['txtAObservaNov'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
switch ($opcion) {
    case '1':
        $val_adicion = NULL;
        $fec_adicion = NULL;
        $fini_pro = NULL;
        $ffin_pro = NULL;
        switch ($tip_nov) {
            case '1':
                $val_adicion = $_POST['numValAdicion'];
                $fec_adicion = $_POST['datFecAdicion'];
                break;
            case '2':
                $fini_pro = $_POST['datFecIniProrroga'];
                $ffin_pro = $_POST['datFecFinProrroga'];
                break;
            case '3':
                $val_adicion = $_POST['numValAdicion'];
                $fec_adicion = $_POST['datFecAdicion'];
                $fini_pro = $_POST['datFecIniProrroga'];
                $ffin_pro = $_POST['datFecFinProrroga'];
                break;
        }
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `ctt_novedad_adicion_prorroga`(`id_tip_nov`,`id_adq`,`val_adicion`,`fec_adcion`,`fec_ini_prorroga`,`fec_fin_prorroga`,`observacion`,`id_user_reg`,`fec_reg`)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $tip_nov, PDO::PARAM_INT);
            $sql->bindParam(2, $id_contrato, PDO::PARAM_INT);
            $sql->bindParam(3, $val_adicion, PDO::PARAM_STR);
            $sql->bindParam(4, $fec_adicion, PDO::PARAM_STR);
            $sql->bindParam(5, $fini_pro, PDO::PARAM_STR);
            $sql->bindParam(6, $ffin_pro, PDO::PARAM_STR);
            $sql->bindParam(7, $observacion, PDO::PARAM_STR);
            $sql->bindParam(8, $iduser, PDO::PARAM_INT);
            $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
        }
        break;
    case '2':
        $fec_cesion = $_POST['datFecCesion'];
        $id_tercero = $_POST['id_tercero'];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `ctt_novedad_cesion`(`id_adq`,`id_tipo_nov`,`id_tercero`,`fec_cesion`,`observacion`,`id_user_reg`,`fec_reg`)
                    VALUES(?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_contrato, PDO::PARAM_INT);
            $sql->bindParam(2, $tip_nov, PDO::PARAM_INT);
            $sql->bindParam(3, $id_tercero, PDO::PARAM_INT);
            $sql->bindParam(4, $fec_cesion, PDO::PARAM_STR);
            $sql->bindParam(5, $observacion, PDO::PARAM_STR);
            $sql->bindParam(6, $iduser, PDO::PARAM_INT);
            $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
        }
        break;
    case '3':
        $fini_susp = $_POST['datFecIniSuspencion'];
        $ffin_susp = $_POST['datFecFinSuspencion'];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `ctt_novedad_suspension`(`id_adq`,`id_tipo_nov`,`fec_inicia`,`fec_fin`,`observacion`,`id_user_reg`,`fec_reg`)
                    VALUES(?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_contrato, PDO::PARAM_INT);
            $sql->bindParam(2, $tip_nov, PDO::PARAM_INT);
            $sql->bindParam(3, $fini_susp, PDO::PARAM_STR);
            $sql->bindParam(4, $ffin_susp, PDO::PARAM_STR);
            $sql->bindParam(5, $observacion, PDO::PARAM_STR);
            $sql->bindParam(6, $iduser, PDO::PARAM_INT);
            $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
        }
        break;
    case '4':
        $frein = $_POST['datFecReinicio'];
        $id_suspension = isset($_POST['id_suspension']) ? $_POST['id_suspension'] : NULL;
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `ctt_novedad_reinicio`(`id_tipo_nov`,`id_suspension`,`fec_reinicia`,`observacion`,`id_user_reg`,`fec_reg`)
                    VALUES(?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $tip_nov, PDO::PARAM_INT);
            $sql->bindParam(2, $id_suspension, PDO::PARAM_INT);
            $sql->bindParam(3, $frein, PDO::PARAM_STR);
            $sql->bindParam(4, $observacion, PDO::PARAM_STR);
            $sql->bindParam(5, $iduser, PDO::PARAM_INT);
            $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
        }
        break;
    case '5':
        $id_tt = $_POST['slcTipTerminacion'];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `ctt_novedad_terminacion`(`id_tipo_nov`,`id_t_terminacion`,`id_adq`,`observacion`,`id_user_reg`,`fec_reg`)
                    VALUES(?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $tip_nov, PDO::PARAM_INT);
            $sql->bindParam(2, $id_tt, PDO::PARAM_INT);
            $sql->bindParam(3, $id_contrato, PDO::PARAM_STR);
            $sql->bindParam(4, $observacion, PDO::PARAM_STR);
            $sql->bindParam(5, $iduser, PDO::PARAM_INT);
            $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
        }
        break;
    case '6':
        $fec_liq = $_POST['datFecLiq'];
        $tip_liq = $_POST['slcTipLiquidacion'];
        $val_ctte = $_POST['numValFavorCtrate'];
        $val_ctta = $_POST['numValFavorCtrista'];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `ctt_novedad_liquidacion`(`id_tipo_nov`,`id_t_liq`,`id_adq`,`fec_liq`,`val_cte`,`val_cta`,`observacion`,`id_user_reg`,`fec_reg`)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $tip_nov, PDO::PARAM_INT);
            $sql->bindParam(2, $tip_liq, PDO::PARAM_INT);
            $sql->bindParam(3, $id_contrato, PDO::PARAM_INT);
            $sql->bindParam(4, $fec_liq, PDO::PARAM_STR);
            $sql->bindParam(5, $val_ctte, PDO::PARAM_STR);
            $sql->bindParam(6, $val_ctta, PDO::PARAM_STR);
            $sql->bindParam(7, $observacion, PDO::PARAM_STR);
            $sql->bindParam(8, $iduser, PDO::PARAM_INT);
            $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
        } catch (PDOException $e) {
            echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
        }
        break;
}
$sql->execute();
if ($cmd->lastInsertId() > 0) {
    echo 1;
} else {
    echo $sql->errorInfo()[2];
}
/*
//API URL
$url = $api . 'terceros/datos/res/nuevo/novedad/' . $endp;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$res = curl_exec($ch);
curl_close($ch);
echo json_decode($res, true);*/
