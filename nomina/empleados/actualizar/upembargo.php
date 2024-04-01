<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$idemb = isset($_POST['numidEmbargo']) ? $_POST['numidEmbargo'] : exit('Acción no permitida');
$idjuzgado = $_POST['slcUpJuzgado'];
$temb = $_POST['numUpTipoEmbargo'];
$valtotal = str_replace(',', '', $_POST['numUpTotEmbargo']);
$max = $_POST['numUpDctoAprox'];
$valmes = $_POST['txtUpValEmbargoMes'];
$pctj = $_POST['txtUpPorcEmbMes'] / 100;
$finemb = date('Y-m-d', strtotime($_POST['datUpFecInicioEmb']));
if ($_POST['datUpFecFinEmb'] === '') {
    $ffinemb;
} else {
    $ffinemb = date('Y-m-d', strtotime($_POST['datUpFecFinEmb']));
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE nom_embargos SET id_juzgado = ?, tipo_embargo = ?, valor_total = ?, dcto_max = ?, valor_mes = ?, porcentaje = ?, fec_inicio = ?, fec_fin = ? WHERE id_embargo = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idjuzgado, PDO::PARAM_INT);
    $sql->bindParam(2, $temb, PDO::PARAM_INT);
    $sql->bindParam(3, $valtotal, PDO::PARAM_STR);
    $sql->bindParam(4, $max, PDO::PARAM_STR);
    $sql->bindParam(5, $valmes, PDO::PARAM_STR);
    $sql->bindParam(6, $pctj, PDO::PARAM_STR);
    $sql->bindParam(7, $finemb, PDO::PARAM_STR);
    $sql->bindParam(8, $ffinemb, PDO::PARAM_STR);
    $sql->bindParam(9, $idemb, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $updata = 1;
    } else {
        $updata = 0;
    }
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    }
    if ($updata > 0) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE nom_embargos SET  fec_act = ? WHERE id_embargo = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idemb, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
            print_r($sql->errorInfo()[2]);
        }
    } else {
        echo 'No se ingresó ningún dato nuevo';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
