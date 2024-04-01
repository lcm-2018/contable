<?php
if (isset($_POST)) {
    $id_pto_cdp = $_POST['id_pto_cdp'];
    $rubro = $_POST['id_rubroCdp'];
    $valorCdp = str_replace(",", "", $_POST['valorCdp']);
    $tipo_mov = 'CDP';
    $estado = 0;
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if (empty($_POST['editarRubro'])) {
        $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc, tipo_mov, rubro, valor,estado) VALUES (?, ?, ?, ?,?)");
        $query->bindParam(1, $id_pto_cdp, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
        $query->bindParam(3, $rubro, PDO::PARAM_STR);
        $query->bindParam(4, $valorCdp, PDO::PARAM_STR);
        $query->bindParam(5, $estado, PDO::PARAM_INT);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $response[] = array("value" => 'ok', "id" => $id);
        } else {
            print_r($query->errorInfo()[2]);
        }
        $cmd = null;
    } else {
        $id = $_POST['editarRubro'];
        $query = $cmd->prepare("UPDATE pto_documento_detalles SET rubro =?, valor = ? WHERE id_pto_mvto = ?");
        $query->bindParam(1, $rubro);
        $query->bindParam(2, $valorCdp);
        $query->bindParam(3, $id);
        $query->execute();
        $cmd = null;
        $response[] = array("value" => 'modificado', "id" => $id);
    }
    echo json_encode($response);
}
