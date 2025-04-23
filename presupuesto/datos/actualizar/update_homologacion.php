<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idsHomolgacion = $_POST['idHomol'];
$codCgrs = $_POST['codCgr'];
$codCpc = $_POST['cpc'];
$codFuente = $_POST['fuente'];
$codTercero = $_POST['tercero'];
$codPolitica = $_POST['polPub'];
$codSiho = $_POST['siho'];
$codSia = $_POST['sia'];
$codSituacion = $_POST['situacion'];
$codVigencia = $_POST['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$suma = 0;
$presupuesto = $_POST['id_pto_tipo'];
$error = '';
if ($presupuesto == 1) {
    $ingreso = $_POST['ingreso'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $sqlI = "INSERT INTO `pto_homologa_ingresos`
                    (`id_cargue`, `id_cgr`, `id_cpc`, `id_fuente`, `id_tercero`, `id_politica`, `id_siho`, `id_sia`, `id_situacion`, `id_vigencia`, `id_user_reg`, `fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $sqlU = "UPDATE `pto_homologa_ingresos`
                    SET `id_cargue` = ?, `id_cgr` = ?, `id_cpc` = ?, `id_fuente` = ?, `id_tercero` = ?, `id_politica` = ?, `id_siho` = ?, `id_sia` = ?, `id_situacion` = ?, `id_vigencia` = ?
                WHERE `id_homologacion` = ?";

        $insert = $cmd->prepare($sqlI);
        $update = $cmd->prepare($sqlU);
        foreach ($codCgrs as $key => $value) {
            if ($codCpc[$key] > 0) {
                $params = [
                    (int) $key,
                    $value,
                    $codCpc[$key],
                    $codFuente[$key],
                    $codTercero[$key],
                    $codPolitica[$key],
                    $codSiho[$key],
                    $codSia[$key],
                    $codSituacion[$key],
                    $codVigencia[$key],
                    (int) $iduser,
                    $date->format('Y-m-d H:i:s')
                ];
                $idHom = $idsHomolgacion[$key];

                if ($idHom == 0) {
                    $insert->execute($params);
                    if ($insert->rowCount() > 0) {
                        $suma++;
                    } else {
                        $error .= $insert->errorInfo()[2];
                    }
                } else {
                    $paramsUpdate = array_slice($params, 0, 10); // Solo los 10 primeros
                    $paramsUpdate[] = (int) $idHom;
                    $update->execute($paramsUpdate);
                    if ($update->rowCount() > 0) {
                        $suma++;
                        $con = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $query = "UPDATE `pto_homologa_ingresos` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_homologacion` = ?";
                        $query = $con->prepare($query);
                        $query->bindParam(1, $iduser, PDO::PARAM_INT);
                        $query->bindValue(2, $date->format('Y-m-d H:i:s'));
                        $query->bindParam(3, $idHom, PDO::PARAM_INT);
                        $query->execute();
                        $con = null;
                    } else {
                        $error .= $insert->errorInfo()[2];
                    }
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
} else if ($presupuesto == 2) {
    $gasto = $_POST['gasto'];
    $codSeccion = $_POST['seccion'];
    $codSector = $_POST['sector'];
    $codClaseSia = $_POST['csia'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sqlI = "INSERT INTO `pto_homologa_gastos`
                    (`id_cargue`, `id_cgr`, `id_cpc`, `id_fuente`, `id_tercero`, `id_politica`, `id_siho`, `id_sia`, `id_situacion`, `id_vigencia`, `id_seccion`, `id_sector`, `id_csia`, `id_user_reg`, `fec_reg`)
                VALUES (?, ?, ?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlU = "UPDATE `pto_homologa_gastos` 
                    SET `id_cargue` = ?, `id_cgr` = ?, `id_cpc` = ?, `id_fuente` = ?, `id_tercero` = ?, `id_politica` = ?, `id_siho` = ?, `id_sia` = ?, `id_situacion` = ?, `id_vigencia` = ?, `id_seccion` = ?, `id_sector` = ?, `id_csia` = ?
                WHERE `id_homologacion` = ?";
        $insert = $cmd->prepare($sqlI);
        $update = $cmd->prepare($sqlU);
        foreach ($codCgrs as $key => $value) {
            if ($codCpc[$key] > 0) {
                $params = [
                    (int) $key,
                    $value,
                    $codCpc[$key],
                    $codFuente[$key],
                    $codTercero[$key],
                    $codPolitica[$key],
                    $codSiho[$key],
                    $codSia[$key],
                    $codSituacion[$key],
                    $codVigencia[$key],
                    $codSeccion[$key],
                    $codSector[$key],
                    $codClaseSia[$key],
                    (int) $iduser,
                    $date->format('Y-m-d H:i:s')
                ];
                $idHom = $idsHomolgacion[$key];

                if ($idHom == 0) {
                    $insert->execute($params);
                    if ($insert->rowCount() > 0) {
                        $suma++;
                    } else {
                        $error .= $insert->errorInfo()[2];
                    }
                } else {
                    $paramsUpdate = array_slice($params, 0, 13);
                    $paramsUpdate[] = (int) $idHom;
                    $update->execute($paramsUpdate);
                    if ($update->rowCount() > 0) {
                        $suma++;
                        $con = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $query = "UPDATE `pto_homologa_gastos` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_homologacion` = ?";
                        $query = $con->prepare($query);
                        $query->bindParam(1, $iduser, PDO::PARAM_INT);
                        $query->bindValue(2, $date->format('Y-m-d H:i:s'));
                        $query->bindParam(3, $idHom, PDO::PARAM_INT);
                        $query->execute();
                        $con = null;
                    } else {
                        $error .= $insert->errorInfo()[2];
                    }
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
if ($suma > 0) {
    echo 'ok';
} else {
    echo 'No se realizó ninguna modificación' . $error;
}
