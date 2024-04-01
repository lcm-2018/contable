<?php
if (isset($_POST)) {
    $id_pto_cdp = $_POST['id_cdp_doc'];
    $id_pto_doc = $_POST['id_doc_neo'];
    $tipo_mov = 'LCD';
    $estado = 0;
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    // Consultar rubro de acuerdo a la Id recibida
    // dividir una variable que biene separada por _
    $partes = explode("_", $_POST['dato']);
    $id_pto_mov = $partes[0];
    $etiqueta = "valor" . $partes[1];
    $valorCdp = str_replace(",", "", $_POST[$etiqueta]);
    $valorCdp = $valorCdp * -1;
    try {
        $sql = "SELECT rubro FROM `pto_documento_detalles` WHERE id_pto_mvto = $id_pto_mov";
        $res = $cmd->query($sql);
        $rubro = $res->fetch(PDO::FETCH_ASSOC);
        $rubro_afec = $rubro['rubro'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    if (empty($_POST['editarRubro'])) {
        $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc, tipo_mov, rubro, valor,id_auto_dep,estado) VALUES (?, ?, ?, ?,?,?)");
        $query->bindParam(1, $id_pto_doc, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
        $query->bindParam(3, $rubro_afec, PDO::PARAM_STR);
        $query->bindParam(4, $valorCdp, PDO::PARAM_STR);
        $query->bindParam(5, $id_pto_cdp, PDO::PARAM_STR);
        $query->bindParam(6, $estado, PDO::PARAM_INT);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();

            // Consultar el saldo disponible del rubro
            try {
                $sql = "SELECT
                            SUM(`valor`) as valor
                        FROM
                            `pto_documento_detalles`
                        WHERE (`rubro` ='$rubro_afec'
                            AND `id_pto_doc` =$id_pto_cdp);";
                $res = $cmd->query($sql);
                $cdp = $res->fetch(PDO::FETCH_ASSOC);
                $valor_cdp = $cdp['valor'];
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }
            // Consultar el saldo disponible del rubro
            try {
                $sql = "SELECT
                            SUM(`valor`) as valor
                        FROM
                            `pto_documento_detalles`
                        WHERE (`rubro` ='$rubro_afec'
                            AND `id_auto_dep` =$id_pto_cdp);";
                $res = $cmd->query($sql);
                $liquidado = $res->fetch(PDO::FETCH_ASSOC);
                $liq = $liquidado['valor'];
                $saldo = $valor_cdp + $liq;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }
            $response[] = array("value" => 'ok', "id" => $id, "valor" => $saldo);
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
