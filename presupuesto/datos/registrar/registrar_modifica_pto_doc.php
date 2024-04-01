<?php
session_start();
printf('<pre>%s</pre>', var_export($_POST, true));
if (isset($_POST)) {
    $fecha = $_POST['fecha'];
    $id_pto = $_POST['id_pto'];
    $tipo_acto = $_POST['tipo_acto'];
    $numMod = $_POST['numMod'];
    $objeto = $_POST['objeto'];
    $tipo_doc = $_POST['id_mov'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    $estado = 1;
    include '../../../conexion.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT MAX(`id_manu`) as `id_manu` FROM `pto_mod` WHERE (`id_tipo_mod`= $tipo_doc)";
        $rs = $cmd->query($sql);
        $id_m = $rs->fetch(PDO::FETCH_ASSOC);
        $id_manu = !empty($id_m) ? $id_m['id_manu'] + 1 : 1;
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if (!isset($_POST['id_pto_mod'])) {
        $query = "INSERT INTO `pto_mod`
                    (`id_pto`, `id_tipo_mod`,`id_tipo_acto`, `numero_acto`, `fecha`,`id_manu`,`objeto`,`estado`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_pto, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
        $query->bindParam(3, $tipo_acto, PDO::PARAM_INT);
        $query->bindParam(4, $numMod, PDO::PARAM_INT);
        $query->bindParam(5, $fecha, PDO::PARAM_STR);
        $query->bindParam(6, $id_manu, PDO::PARAM_INT);
        $query->bindParam(7, $objeto, PDO::PARAM_STR);
        $query->bindParam(8, $estado, PDO::PARAM_INT);
        $query->bindParam(9, $iduser, PDO::PARAM_INT);
        $query->bindParam(10, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $response[] = array("value" => 'ok', "id" => $id);
        } else {
            echo $query->errorInfo()[2];
        }
        $cmd = null;
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
        $response[] = array("value" => 'no');
    }
    echo json_encode($response);
}
