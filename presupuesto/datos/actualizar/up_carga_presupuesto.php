<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_cargue = $_POST['id_cargue'];
$nomCod = $_POST['nomCod'];
$tipoDato = $_POST['tipoDato'];
$vigencia = $_SESSION['vigencia'];
$nomRubro = $tipoDato == '0' ? strtoupper($_POST['nomRubro']) : $_POST['nomRubro'];
$valorAprob = isset($_POST['valorAprob']) && $_POST['valorAprob'] > 0 ? $_POST['valorAprob'] : 0;
$valorAprob = str_replace(',', '', $valorAprob);
$tipoRecurso = isset($_POST['tipoRecurso']) ? $_POST['tipoRecurso'] : $tipoRecurso = '';
$tipoPto = $_POST['tipoPresupuesto'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `pto_cargue` SET 
                `cod_pptal` = ?, `nom_rubro` = ?, `tipo_dato` = ?, `valor_aprobado` = ?, `id_tipo_recurso` = ?, `tipo_pto` = ? 
            WHERE `id_cargue` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $nomCod, PDO::PARAM_STR);
    $sql->bindParam(2, $nomRubro, PDO::PARAM_STR);
    $sql->bindParam(3, $tipoDato, PDO::PARAM_STR);
    $sql->bindParam(4, $valorAprob, PDO::PARAM_INT);
    $sql->bindParam(5, $tipoRecurso, PDO::PARAM_INT);
    $sql->bindParam(6, $tipoPto, PDO::PARAM_INT);
    $sql->bindParam(7, $id_cargue, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $sql = "UPDATE `pto_cargue` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_cargue` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_cargue, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo 'ok';
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo 'No se realizo ninguna actualización';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
