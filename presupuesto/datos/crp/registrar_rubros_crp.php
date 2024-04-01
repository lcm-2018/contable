<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $id_crpp = $_POST['crpp'];
    $num = $_POST['num'];
    $datos = $_POST['datos'];
    $obj = json_decode($datos, true);
    $tipo_mov = 'CRP';
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    include '../../../conexion.php';

    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        if (empty($_POST['id_pto_crp'])) {
            $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc, tipo_mov, rubro, valor,id_auto_dep) VALUES (?, ?, ?, ?, ?)");
            $query->bindParam(1, $id_crpp, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
            $query->bindParam(3, $rubro, PDO::PARAM_STR);
            $query->bindParam(4, $valore, PDO::PARAM_STR);
            $query->bindParam(5, $id_auto, PDO::PARAM_INT);
            foreach ($obj as $key => $value) {
                // Realizo la consulta para obtener el rubro
                $valore = str_replace(",", "", $value);
                $sql = "SELECT id_pto_doc,rubro FROM pto_documento_detalles WHERE id_pto_mvto = $key";
                $query_rubro = $cmd->prepare($sql);
                $query_rubro->execute();
                $row_rubro = $query_rubro->fetch();
                $rubro = $row_rubro['rubro'];
                $id_auto = $row_rubro['id_pto_doc'];
                $query->execute();
                if ($cmd->lastInsertId() > 0) {
                    $id = $cmd->lastInsertId();
                    $response[] = array("value" => 'ok', "id" => $id);
                } else {
                    print_r($query_rubro->errorInfo()[2]);
                }
            }
            $cmd = null;
        } else {
            $id = $_POST['id_pto_doc'];
            $query = $cmd->prepare("UPDATE pto_documento SET id_manu = :id_manu, fecha = :fecha, objeto =:objeto, id_usuer_act=:id_usuer_act,fec_act=:fec_act WHERE id_pto_doc = :id_pto_doc");
            $query->bindParam(":id_manu", $id_manu);
            $query->bindParam(":fecha", $fecha);
            $query->bindParam(":objeto", $objeto);
            $query->bindParam(":id_usuer_act", $iduser);
            $query->bindParam(":fec_act", $date);
            $query->bindParam(":id_pto_doc", $id);
            $query->execute();
            $cmd = null;
            $response[] = array("value" => 'modificado', "id" => $id);
        }
        echo json_encode($response);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
