<?php
session_start();
if (isset($_POST)) {
    $id_manu = $_POST['numCdp'];
    $fecha = $_POST['fecha'];
    $objeto = $_POST['objeto'];
    $tipo_doc = 'CDP';
    $id_pto = $_POST['id_pto_presupuestos'];
    $id_adq = $_POST['id_adq'];
    $id_otro = $_POST['id_otro'];
    $num_solicitud = $_POST['solicitud'];
    $sede = 1;
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if (empty($_POST['id_pto_mvto'])) {
        $query = $cmd->prepare("INSERT INTO pto_documento (id_pto_presupuestos,id_sede, tipo_doc, id_manu, fecha, objeto,num_solicitud, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?,?,?,?)");
        $query->bindParam(1, $id_pto, PDO::PARAM_INT);
        $query->bindParam(2, $sede, PDO::PARAM_INT);
        $query->bindParam(3, $tipo_doc, PDO::PARAM_STR);
        $query->bindParam(4, $id_manu, PDO::PARAM_STR);
        $query->bindParam(5, $fecha, PDO::PARAM_STR);
        $query->bindParam(6, $objeto, PDO::PARAM_STR);
        $query->bindParam(7, $num_solicitud, PDO::PARAM_INT);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $response[] = array("value" => 'ok', "id" => $id);
            // Actualizo id_cdp en la tabla ctt_adquisiciones
            if (isset($_POST['id_adq'])) {
                $query = $cmd->prepare("UPDATE ctt_adquisiciones SET id_cdp = ? WHERE id_adquisicion = ?");
                $query->bindParam(1, $id, PDO::PARAM_INT);
                $query->bindParam(2, $id_adq, PDO::PARAM_INT);
                $query->execute();
            }
            // Actualizo el id_cdp en la tabla ctt_novedad_adicion_prorroga
            if (isset($_POST['id_otro'])) {
                $query = $cmd->prepare("UPDATE ctt_novedad_adicion_prorroga SET cdp = ? WHERE id_nov_con = ?");
                $query->bindParam(1, $id, PDO::PARAM_INT);
                $query->bindParam(2, $id_otro, PDO::PARAM_INT);
                $query->execute();
            }
        } else {
            print_r($query->errorInfo()[2]);
        }
        $cmd = null;
    } else {
        $id = $_POST['id_pto_mvto'];
        $query = $cmd->prepare("UPDATE pto_documento SET id_manu = ?, fecha = ?, objeto =?, id_user_act = ?, fec_act=?, num_solicitud=? WHERE id_pto_doc = ?");
        $query->bindParam(1, $id_manu, PDO::PARAM_STR);
        $query->bindParam(2, $fecha, PDO::PARAM_STR);
        $query->bindParam(3, $objeto, PDO::PARAM_STR);
        $query->bindParam(4, $iduser, PDO::PARAM_INT);
        $query->bindParam(5, $fecha2);
        $query->bindParam(6, $num_solicitud, PDO::PARAM_STR);
        $query->bindParam(7, $id);
        $query->execute();
        if ($query->rowCount() > 0) {
            $response[] = array("value" => 'modificado', "id" => $id);
        } else {
            print_r($query->errorInfo()[2]);
        }
        $cmd = null;
    }
    echo json_encode($response);
}
