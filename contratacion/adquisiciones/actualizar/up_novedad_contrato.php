<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$novedad = isset($_POST['slcTipoNovedad']) ? $_POST['slcTipoNovedad'] : exit('Accion no permitida');
$id_novedad = $_POST['id_novendad'];
$observacion = $_POST['txtAObservaNov'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
switch ($novedad) {
    case 1:
        $val_adicion = $_POST['numValAdicion'];
        $fec_adicion = $_POST['datFecAdicion'];
        $fini_pro = NULL;
        $ffin_pro = NULL;
        try {
            $sql = "UPDATE `ctt_novedad_adicion_prorroga` SET
                        `id_tip_nov` = ?, `val_adicion` = ?, `fec_adcion` = ?, `fec_ini_prorroga` = ?, `fec_fin_prorroga` = ?, `observacion` = ?
                    WHERE `id_nov_con` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $novedad, PDO::PARAM_INT);
            $sql->bindParam(2, $val_adicion, PDO::PARAM_STR);
            $sql->bindParam(3, $fec_adicion);
            $sql->bindParam(4, $fini_pro);
            $sql->bindParam(5, $ffin_pro);
            $sql->bindParam(6, $observacion, PDO::PARAM_STR);
            $sql->bindParam(7, $id_novedad, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `ctt_novedad_adicion_prorroga` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_nov_con` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '1';
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo 'No se registró ningún nuevo dato';
                }
            }
            $cmd = NULL;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        break;
    case 2:
        $val_adicion = NULL;
        $fec_adicion = NULL;
        $fini_pro = isset($_POST['datFecIniProrroga']) ? $_POST['datFecIniProrroga'] : NULL;
        $ffin_pro = isset($_POST['datFecFinProrroga']) ? $_POST['datFecFinProrroga'] : NULL;
        try {
            $sql = "UPDATE `ctt_novedad_adicion_prorroga` SET
                        `id_tip_nov` = ?, `val_adicion` = ?, `fec_adcion` = ?, `fec_ini_prorroga` = ?, `fec_fin_prorroga` = ?, `observacion` = ?
                    WHERE `id_nov_con` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $novedad, PDO::PARAM_INT);
            $sql->bindParam(2, $val_adicion, PDO::PARAM_STR);
            $sql->bindParam(3, $fec_adicion, PDO::PARAM_STR);
            $sql->bindParam(4, $fini_pro, PDO::PARAM_STR);
            $sql->bindParam(5, $ffin_pro, PDO::PARAM_STR);
            $sql->bindParam(6, $observacion, PDO::PARAM_STR);
            $sql->bindParam(7, $id_novedad, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `ctt_novedad_adicion_prorroga` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_nov_con` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '1';
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo 'No se registró ningún nuevo dato';
                }
            }
            $cmd = NULL;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        break;
    case 3:
        $val_adicion = $_POST['numValAdicion'];
        $fec_adicion = $_POST['datFecAdicion'];
        $fini_pro = $_POST['datFecIniProrroga'];
        $ffin_pro = $_POST['datFecFinProrroga'];
        try {
            $sql = "UPDATE `ctt_novedad_adicion_prorroga` SET
                        `id_tip_nov` = ?, `val_adicion` = ?, `fec_adcion` = ?, `fec_ini_prorroga` = ?, `fec_fin_prorroga` = ?, `observacion` = ?
                    WHERE `id_nov_con` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $novedad, PDO::PARAM_INT);
            $sql->bindParam(2, $val_adicion, PDO::PARAM_STR);
            $sql->bindParam(3, $fec_adicion, PDO::PARAM_STR);
            $sql->bindParam(4, $fini_pro, PDO::PARAM_STR);
            $sql->bindParam(5, $ffin_pro, PDO::PARAM_STR);
            $sql->bindParam(6, $observacion, PDO::PARAM_STR);
            $sql->bindParam(7, $id_novedad, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `ctt_novedad_adicion_prorroga` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_nov_con` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '1';
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo 'No se registró ningún nuevo dato';
                }
            }
            $cmd = NULL;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        break;
    case 4:
        $fec_cesion = $_POST['datFecCesion'];
        $id_tercero = $_POST['id_tercero'];
        try {
            $sql = "UPDATE `ctt_novedad_cesion` SET
                        `id_tercero` = ?, `fec_cesion` = ?, `observacion` = ?
                    WHERE `id_cesion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
            $sql->bindParam(2, $fec_cesion, PDO::PARAM_STR);
            $sql->bindParam(3, $observacion, PDO::PARAM_STR);
            $sql->bindParam(4, $id_novedad, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `ctt_novedad_cesion` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_cesion` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '1';
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo 'No se registró ningún nuevo dato';
                }
            }
            $cmd = NULL;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        break;
    case 5:
        $fini_susp = $_POST['datFecIniSuspencion'];
        $ffin_susp = $_POST['datFecFinSuspencion'];
        try {
            $sql = "UPDATE `ctt_novedad_suspension` SET
                        `fec_inicia` = ?, `fec_fin` = ?, `observacion` = ?
                    WHERE `id_suspension` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $fini_susp, PDO::PARAM_STR);
            $sql->bindParam(2, $ffin_susp, PDO::PARAM_STR);
            $sql->bindParam(3, $observacion, PDO::PARAM_STR);
            $sql->bindParam(4, $id_novedad, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `ctt_novedad_suspension` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_suspension` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '1';
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo 'No se registró ningún nuevo dato';
                }
            }
            $cmd = NULL;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        break;
    case 6:
        $frein = $_POST['datFecReinicio'];
        try {
            $sql = "UPDATE `ctt_novedad_reinicio` SET
                        `fec_reinicia` = ?, `observacion` = ?
                    WHERE `id_reinicio` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $frein, PDO::PARAM_STR);
            $sql->bindParam(2, $observacion, PDO::PARAM_STR);
            $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `ctt_novedad_reinicio` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_reinicio` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '1';
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo 'No se registró ningún nuevo dato';
                }
            }
            $cmd = NULL;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        break;
    case 7:
        $id_tt = $_POST['slcTipTerminacion'];
        try {
            $sql = "UPDATE `ctt_novedad_terminacion` SET
                        `id_t_terminacion` = ?, `observacion` = ?
                    WHERE `id_terminacion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_tt, PDO::PARAM_STR);
            $sql->bindParam(2, $observacion, PDO::PARAM_STR);
            $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `ctt_novedad_terminacion` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_terminacion` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '1';
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo 'No se registró ningún nuevo dato';
                }
            }
            $cmd = NULL;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        break;
    case 8:
        $fec_liq = $_POST['datFecLiq'];
        $tip_liq = $_POST['slcTipLiquidacion'];
        $val_ctte = $_POST['numValFavorCtrate'];
        $val_ctta = $_POST['numValFavorCtrista'];
        try {
            $sql = "UPDATE `ctt_novedad_liquidacion` SET
                        `id_t_liq` = ?, `fec_liq` = ?, `val_cte` = ?, `val_cta` = ?, `observacion` = ?
                    WHERE `id_liquidacion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $tip_liq, PDO::PARAM_INT);
            $sql->bindParam(2, $fec_liq, PDO::PARAM_STR);
            $sql->bindParam(3, $val_ctte, PDO::PARAM_STR);
            $sql->bindParam(4, $val_ctta, PDO::PARAM_STR);
            $sql->bindParam(5, $observacion, PDO::PARAM_STR);
            $sql->bindParam(6, $id_novedad, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
                exit();
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `ctt_novedad_liquidacion` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_liquidacion` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_novedad, PDO::PARAM_INT);
                    $sql->execute();
                    if ($sql->rowCount() > 0) {
                        echo '1';
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                } else {
                    echo 'No se registró ningún nuevo dato';
                }
            }
            $cmd = NULL;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        break;
    default:
        exit('Acción no permitida');
        break;
}
        



/*$observacion = $_POST['txtAObservaNov'];
$iduser = $_SESSION['id_user'];
$tipouser = 'user';
$endp = 'adicion_prorroga';
$val_adicion = NULL;
$fec_adicion = NULL;
$cdp = NULL;
$fini_pro = NULL;
$ffin_pro = NULL;
switch ($novedad) {
    case '1':
        $val_adicion = $_POST['numValAdicion'];
        $fec_adicion = $_POST['datFecAdicion'];
        //$cdp = $_POST['slcCDP'];
        break;
    case '2':
        $fini_pro = $_POST['datFecIniProrroga'];
        $ffin_pro = $_POST['datFecFinProrroga'];
        break;
    case '3':
        $val_adicion = $_POST['numValAdicion'];
        $fec_adicion = $_POST['datFecAdicion'];
        $cdp = $_POST['slcCDP'];
        $fini_pro = $_POST['datFecIniProrroga'];
        $ffin_pro = $_POST['datFecFinProrroga'];
        break;
    case '4':
        $fec_cesion = $_POST['datFecCesion'];
        $id_tercero = $_POST['id_tercero'];
        $id_contrato = $_POST['id_contrato'];
        $endp = 'cesion';
        $data = [
            "id_contrato" => $id_contrato,
            "id_novedad" => $id_novedad,
            "fec_cesion" => $fec_cesion,
            "id_tercero" => $id_tercero,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
    case '5':
        $fini_susp = $_POST['datFecIniSuspencion'];
        $ffin_susp = $_POST['datFecFinSuspencion'];
        $id_contrato = $_POST['id_contrato'];
        $endp = 'suspension';
        $data = [
            "id_contrato" => $id_contrato,
            "id_novedad" => $id_novedad,
            "fini_susp" => $fini_susp,
            "ffin_susp" => $ffin_susp,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
    case '6':
        $frein = $_POST['datFecReinicio'];
        $endp = 'reinicio';
        $data = [
            "id_novedad" => $id_novedad,
            "frein" => $frein,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
    case '7':
        $id_tt = $_POST['slcTipTerminacion'];
        $endp = 'terminacion';
        $data = [
            "id_novedad" => $id_novedad,
            "id_tt" => $id_tt,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
    case '8':
        $fec_liq = $_POST['datFecLiq'];
        $tip_liq = $_POST['slcTipLiquidacion'];
        $val_ctte = $_POST['numValFavorCtrate'];
        $val_ctta = $_POST['numValFavorCtrista'];
        $endp = 'liquidacion';
        $data = [
            "id_novedad" => $id_novedad,
            "fec_liq" => $fec_liq,
            "tip_liq" => $tip_liq,
            "val_ctte" => $val_ctte,
            "val_ctta" => $val_ctta,
            "observacion" => $observacion,
            "iduser" => $iduser,
            "tipouser" => $tipouser,
        ];
        break;
}
if ($novedad == '1' || $novedad == '2' || $novedad == '3') {
    $data = [
        "id_novedad" => $id_novedad,
        "tip_novedad" => $novedad,
        "val_adicion" => $val_adicion,
        "fec_adicion" => $fec_adicion,
        "cdp" => $cdp,
        "fini_pro" => $fini_pro,
        "ffin_pro" => $ffin_pro,
        "observacion" => $observacion,
        "iduser" => $iduser,
        "tipouser" => $tipouser,
    ];
}
//API URL
$url = $api . 'terceros/datos/res/actualizar/novedad/' . $endp;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
echo json_decode($res, true);*/
