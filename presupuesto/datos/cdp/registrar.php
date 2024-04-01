<?php
if (isset($_POST)) {
    $id_pto_cdp = $_POST['id_pto_cdp'];
    $rubro = $_POST['id_rubroCod'];
    $valorCdp = str_replace(",", "", $_POST['valorCdp']);
    $tipo_mov = 'CDP';
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if (empty($_POST['id_pto_mvto'])) {
        $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc, tipo_mov, rubro, valor,estado) VALUES (:id, :tipo, :rubro, :valor, :estado)");
        $query->bindParam(":id", $id_pto_cdp);
        $query->bindParam(":tipo", $tipo_mov);
        $query->bindParam(":rubro", $rubro);
        $query->bindParam(":valor", $valorCdp);
        $query->bindParam(":estado", 0);
        $query->execute();
        $cmd = null;
        echo "ok";
    } else {
        $id = $_POST['id_pto_mvto'];
        $query = $cmd->prepare("UPDATE pto_documento_detalles SET id_pto_doc = :id_pto, tipo_mov = :tipo, rubro =:rubro, valor = :valor WHERE id_pto_mvto = :id");
        $query->bindParam(":id_pto", $id_pto_cdp);
        $query->bindParam(":tipo", $tipo_mov);
        $query->bindParam(":rubro", $rubro);
        $query->bindParam(":valor", $valorCdp);
        $query->bindParam("id", $id);
        $query->execute();
        $cmd = null;
        echo "modificado";
    }
}
