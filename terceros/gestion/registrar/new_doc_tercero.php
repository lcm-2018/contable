<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

include '../../../conexion.php';

$idt = $_POST['idTercero'];
$ruta = '../../../uploads/terceros/docs/' . $idt . '/';
$idsoporte = $_POST['slcTipoDocs'];
$fini = date('Y-m-d', strtotime($_POST['datFecInicio']));
$fvig = date('Y-m-d', strtotime($_POST['datFecVigencia']));
$iduser =  $_SESSION['id_user'];
$id_banco = isset($_POST['slcBanco']) ? $_POST['slcBanco'] : NULL;
$tp_cta = isset($_POST['slcTipoCta']) ? $_POST['slcTipoCta'] : NULL;
$num_cta = isset($_POST['numCuenta']) ? $_POST['numCuenta'] : NULL;

$perfil = isset($_POST['slcPerfil']) ? $_POST['slcPerfil'] : NULL;
$perfil = $perfil == '0' ? NULL : $perfil;
$cargo = isset($_POST['txtCargo']) ? $_POST['txtCargo'] : NULL;

$date = new DateTime('now', new DateTimeZone('America/Bogota'));

if (isset($_FILES['fileDoc'])) {
    if (!file_exists($ruta)) {
        $ruta = mkdir('../../../uploads/terceros/docs/' . $idt . '/', 0777, true);
        $ruta = $ruta = '../../../uploads/terceros/docs/' . $idt . '/';
    }

    $nom_archivo = $idsoporte . '_' . date('YmdGis') . '_' . $_FILES['fileDoc']['name'];
    $nom_archivo = strlen($nom_archivo) >= 101 ? substr($nom_archivo, 0, 100) : $nom_archivo;
    $temporal = $_FILES['fileDoc']['tmp_name'];
    if (move_uploaded_file($temporal, $ruta . $nom_archivo)) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        // iniciar transacción
        $cmd->beginTransaction();
        $sql = "INSERT INTO `ctt_documentos`
                    (`id_tercero`,`id_soporte`,`fec_inicio`,`fec_vig`,`ruta_doc`,`nombre_doc`,`id_user_reg`,`fec_reg`,`perfil`,`cargo`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idt, PDO::PARAM_INT);
        $sql->bindParam(2, $idsoporte, PDO::PARAM_INT);
        $sql->bindParam(3, $fini, PDO::PARAM_STR);
        $sql->bindParam(4, $fvig, PDO::PARAM_STR);
        $sql->bindParam(5, $ruta, PDO::PARAM_STR);
        $sql->bindParam(6, $nom_archivo, PDO::PARAM_STR);
        $sql->bindParam(7, $iduser, PDO::PARAM_INT);
        $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(9, $perfil, PDO::PARAM_STR);
        $sql->bindParam(10, $cargo, PDO::PARAM_STR);
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            // 23 Certificación Bancaria
            $id_doc = $cmd->lastInsertId();
            if ($idsoporte == 23) {
                $sql = "INSERT INTO `ctt_cuenta_bancaria`
                            (`id_tercero`,`id_banco`,`tipo_cuenta`,`num_cuenta`,`id_user_reg`,`fec_reg`)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $idt, PDO::PARAM_INT);
                $sql->bindParam(2, $id_banco, PDO::PARAM_INT);
                $sql->bindParam(3, $tp_cta, PDO::PARAM_STR);
                $sql->bindParam(4, $num_cta, PDO::PARAM_STR);
                $sql->bindParam(5, $iduser, PDO::PARAM_INT);
                $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    $cmd->rollBack();
                    echo $sql->errorInfo()[2];
                    exit();
                }
            }
            $cmd->commit();
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        $cmd->rollBack();
        echo 'No se pudo adjuntar el archivo';
    }
} else {
    echo 'No se ha adjuntado ningún archivo';
}
