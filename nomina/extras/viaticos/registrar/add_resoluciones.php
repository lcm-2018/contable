<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_user = $_SESSION['id_user'];
$vigencia = $_SESSION['vigencia'];
$registros = 0;
if (isset($_POST['check'])) {
    $lista_ids = $_POST['check'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $query = "SELECT `grupo` FROM `nom_consecutivo_viaticos` WHERE `id_consec` = '1'";
        $rs = $cmd->query($query);
        $consec = $rs->fetch();
        $grupo = $consec['grupo'] + 1;
        $query = "UPDATE `nom_consecutivo_viaticos` SET `grupo` = `grupo` + 1 WHERE `id_consec` = 1";
        $query = $cmd->prepare($query);
        $query->execute();
        $sql = "INSERT INTO `nom_resolucion_viaticos` (`id_empleado`, `no_resolucion`, `fec_inicia`, `fec_final`, `tot_dias`, `dias_pernocta`, `objetivo`, `destino`, `grupo`, `vigencia`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_empleado, PDO::PARAM_INT);
        $sql->bindParam(2, $no_resolucion, PDO::PARAM_INT);
        $sql->bindParam(3, $fec_inicia, PDO::PARAM_STR);
        $sql->bindParam(4, $fec_final, PDO::PARAM_STR);
        $sql->bindParam(5, $tot_dias, PDO::PARAM_INT);
        $sql->bindParam(6, $dias_pernocta, PDO::PARAM_INT);
        $sql->bindParam(7, $objetivo, PDO::PARAM_STR);
        $sql->bindParam(8, $destino, PDO::PARAM_STR);
        $sql->bindParam(9, $grupo, PDO::PARAM_INT);
        $sql->bindParam(10, $vigencia, PDO::PARAM_STR);
        $sql->bindParam(11, $id_user, PDO::PARAM_INT);
        $sql->bindValue(12, $date->format('Y-m-d H:i:s'));
        foreach ($lista_ids as $id) {
            $query = "SELECT `resolucion` FROM `nom_consecutivo_viaticos` WHERE `id_consec` = '1'";
            $rs = $cmd->query($query);
            $consec = $rs->fetch();
            $no_resolucion = $consec['resolucion'] + 1;
            $query = "UPDATE `nom_consecutivo_viaticos` SET `resolucion` = `resolucion` + 1 WHERE `id_consec` = 1";
            $query = $cmd->prepare($query);
            $query->execute();
            $id_empleado = $id;
            $fec_inicia = $_POST['fec_inicia_' . $id];
            $fec_final = $_POST['fec_final_' . $id];
            $tot_dias = $_POST['tot_dias_' . $id];
            $dias_pernocta = $_POST['dias_pernocta_' . $id];
            $objetivo = $_POST['objetivo_' . $id];
            $destino = $_POST['destino_' . $id];
            $sql->execute();
            if ((!$cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2];
            } else {
                $registros++;
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo 'No hay empleados seleccionados';
    exit();
}
if ($registros > 0) {
    echo 1;
} else {
    echo 'No se pudo generar ninguna resolución';
}
