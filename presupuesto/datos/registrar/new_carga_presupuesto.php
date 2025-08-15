<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$nomCod = $_POST['nomCod'];
$tipoDato = $_POST['tipoDato'];
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$nomRubro = $tipoDato == '0' ? strtoupper($_POST['nomRubro']) : $_POST['nomRubro'];
$valorAprob = isset($_POST['valorAprob']) && $_POST['valorAprob'] > 0 ? $_POST['valorAprob'] : 0;
$valorAprob = str_replace(',', '', $valorAprob);
$tipoRecurso = isset($_POST['tipoRecurso']) ? $_POST['tipoRecurso'] : $tipoRecurso = '';
$tipoPto = $_POST['tipoPresupuesto'];
$id_pto = $_POST['id_pto'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

$padre = explode('.', $nomCod);
array_pop($padre);
$padre = implode('.', $padre);

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sq = "SELECT
                `pto_cargue`.`tipo_dato`
            FROM
                `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE (`pto_presupuestos`.`id_vigencia` = $id_vigencia AND `pto_cargue`.`cod_pptal` = '$padre')";
    $rs = $cmd->query($sq);
    $data = $rs->fetch();
    $tipoPadre = !empty($data) ? $data['tipo_dato'] : 0;
    if ($tipoPadre == 1) {
        echo "La cuenta Mayor $padre es de detalle";
        exit();
    }

    $sql = "INSERT INTO `pto_cargue` 
                (`id_pto`, `cod_pptal`, `nom_rubro`, `tipo_dato`, `valor_aprobado`, `id_tipo_recurso`, `tipo_pto`, `id_user_reg`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $nomCod, PDO::PARAM_STR);
    $sql->bindParam(3, $nomRubro, PDO::PARAM_STR);
    $sql->bindParam(4, $tipoDato, PDO::PARAM_STR);
    $sql->bindParam(5, $valorAprob, PDO::PARAM_INT);
    $sql->bindParam(6, $tipoRecurso, PDO::PARAM_INT);
    $sql->bindParam(7, $tipoPto, PDO::PARAM_INT);
    $sql->bindParam(8, $iduser, PDO::PARAM_INT);
    $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
