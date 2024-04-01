<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
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
if ($presupuesto == 1) {
    $ingreso = $_POST['ingreso'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        if ($ingreso == 0) {
            $sql = "INSERT INTO `pto_homologa_ingresos`
                (`id_cargue`, `id_cgr`, `id_cpc`, `id_fuente`, `id_tercero`, `id_politica`, `id_siho`, `id_sia`, `id_situacion`, `id_vigencia`, `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ? , ?, ?, ?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE `pto_homologa_ingresos` 
                SET `id_cargue` = ?, `id_cgr` = ?, `id_cpc` = ?, `id_fuente` = ?, `id_tercero` = ?, `id_politica` = ?, `id_siho` = ?, `id_sia` = ?, `id_situacion` = ?, `id_vigencia` = ?
                WHERE `id_homologacion` = ?";
        }
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_ppto, PDO::PARAM_INT);
        $sql->bindParam(2, $cgr, PDO::PARAM_STR);
        $sql->bindParam(3, $cpc, PDO::PARAM_STR);
        $sql->bindParam(4, $fte, PDO::PARAM_STR);
        $sql->bindParam(5, $tercer, PDO::PARAM_STR);
        $sql->bindParam(6, $polit, PDO::PARAM_STR);
        $sql->bindParam(7, $siho, PDO::PARAM_STR);
        $sql->bindParam(8, $sia, PDO::PARAM_STR);
        $sql->bindParam(9, $situa, PDO::PARAM_STR);
        $sql->bindParam(10, $vig, PDO::PARAM_STR);
        if ($ingreso == 0) {
            $sql->bindParam(11, $iduser, PDO::PARAM_INT);
            $sql->bindValue(12, $date->format('Y-m-d H:i:s'));
        } else {
            $sql->bindParam(11, $idHom, PDO::PARAM_INT);
        }
        foreach ($codCgrs as $key => $value) {
            $id_ppto = $key;
            $cgr = $value;
            $cpc = $codCpc[$key];
            $fte = $codFuente[$key];
            $tercer = $codTercero[$key];
            $polit = $codPolitica[$key];
            $siho = $codSiho[$key];
            $sia = $codSia[$key];
            $situa = $codSituacion[$key];
            $vig = $codVigencia[$key];
            if ($ingreso == 1) {
                $idHom = $idsHomolgacion[$key];
            }
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $suma++;
                if ($ingreso == 1) {
                    $con = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $query = "UPDATE `pto_homologa_ingresos` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_homologacion` = ?";
                    $query = $con->prepare($query);
                    $query->bindParam(1, $iduser, PDO::PARAM_INT);
                    $query->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $query->bindParam(3, $idHom, PDO::PARAM_INT);
                    $query->execute();
                    $con = null;
                }
            } else {
                echo $sql->errorInfo()[2];
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
        if ($gasto == 0) {
            $sql = "INSERT INTO `pto_homologa_gastos`
                        (`id_cargue`, `id_cgr`, `id_cpc`, `id_fuente`, `id_tercero`, `id_politica`, `id_siho`, `id_sia`, `id_situacion`, `id_vigencia`, `id_seccion`, `id_sector`, `id_csia`, `id_user_reg`, `fec_reg`)
                    VALUES (?, ?, ?, ? , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE `pto_homologa_gastos` 
                    SET `id_cargue` = ?, `id_cgr` = ?, `id_cpc` = ?, `id_fuente` = ?, `id_tercero` = ?, `id_politica` = ?, `id_siho` = ?, `id_sia` = ?, `id_situacion` = ?, `id_vigencia` = ?, `id_seccion` = ?, `id_sector` = ?, `id_csia` = ?
                    WHERE `id_homologacion` = ?";
        }
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_ppto, PDO::PARAM_INT);
        $sql->bindParam(2, $cgr, PDO::PARAM_STR);
        $sql->bindParam(3, $cpc, PDO::PARAM_STR);
        $sql->bindParam(4, $fte, PDO::PARAM_STR);
        $sql->bindParam(5, $tercer, PDO::PARAM_STR);
        $sql->bindParam(6, $polit, PDO::PARAM_STR);
        $sql->bindParam(7, $siho, PDO::PARAM_STR);
        $sql->bindParam(8, $sia, PDO::PARAM_STR);
        $sql->bindParam(9, $situa, PDO::PARAM_STR);
        $sql->bindParam(10, $vig, PDO::PARAM_STR);
        $sql->bindParam(11, $secc, PDO::PARAM_STR);
        $sql->bindParam(12, $sect, PDO::PARAM_STR);
        $sql->bindParam(13, $csia, PDO::PARAM_STR);
        if ($gasto == 0) {
            $sql->bindParam(14, $iduser, PDO::PARAM_INT);
            $sql->bindValue(15, $date->format('Y-m-d H:i:s'));
        } else {
            $sql->bindParam(14, $idHom, PDO::PARAM_INT);
        }
        foreach ($codCgrs as $key => $value) {
            $id_ppto = $key;
            $cgr = $value;
            $cpc = $codCpc[$key];
            $fte = $codFuente[$key];
            $tercer = $codTercero[$key];
            $polit = $codPolitica[$key];
            $siho = $codSiho[$key];
            $sia = $codSia[$key];
            $situa = $codSituacion[$key];
            $vig = $codVigencia[$key];
            $secc = $codSeccion[$key];
            $sect = $codSector[$key];
            $csia = $codClaseSia[$key];
            if ($gasto == 1) {
                $idHom = $idsHomolgacion[$key];
            }
            $sql->execute();
            if ($sql->rowCount() > 0) {
                $suma++;
                if ($gasto == 1) {
                    $con = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $query = "UPDATE `pto_homologa_gastos` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_homologacion` = ?";
                    $query = $con->prepare($query);
                    $query->bindParam(1, $iduser, PDO::PARAM_INT);
                    $query->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $query->bindParam(3, $idHom, PDO::PARAM_INT);
                    $query->execute();
                    $con = null;
                }
            } else {
                echo $sql->errorInfo()[2];
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
    echo 'No se realizó ninguna modificación';
}
