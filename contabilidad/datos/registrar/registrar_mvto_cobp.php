<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $id_crrp = $_POST['id_crrp'];
    $id_ctb_doc = $_POST['id_ctb_doc'];
    $datos = $_POST['datos'];
    $obj = json_decode($datos, true);
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    $tipo_mov = "COP";
    $estado = 3;
    $total = 0;
    //
    include '../../../conexion.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        // consulto id_auto_dep de la tabla pto_mov segun el id_pto_doc = id_crpp
        $sql = "SELECT id_auto_dep FROM pto_documento_detalles WHERE id_pto_doc = '$id_crrp' AND tipo_mov = 'CRP'";
        $rs = $cmd->query($sql);
        $idCdp = $rs->fetch();
        $id_auto_cdp = $idCdp['id_auto_dep'];
        if (empty($_POST['id'])) {
            $query = $cmd->prepare("INSERT INTO pto_documento_detalles (id_pto_doc,id_ctb_doc,id_auto_dep,tipo_mov, rubro, valor,estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $query->bindParam(1, $id_crrp, PDO::PARAM_INT);
            $query->bindParam(2, $id_ctb_doc, PDO::PARAM_INT);
            $query->bindParam(3, $id_auto_cdp, PDO::PARAM_INT);
            $query->bindParam(4, $tipo_mov, PDO::PARAM_STR);
            $query->bindParam(5, $rubro, PDO::PARAM_STR);
            $query->bindParam(6, $valore, PDO::PARAM_STR);
            $query->bindParam(7, $estado, PDO::PARAM_INT);
            foreach ($obj as $key => $value) {
                // Realizo la consulta para obtener el rubro
                $valore = str_replace(",", "", $value);
                $id_rubro = str_replace("rub_", "", $key);
                // Consultar rubro de acuerdo a id_rubro
                $sql = "SELECT rubro FROM pto_documento_detalles WHERE id_pto_mvto = '$id_rubro'";
                $query2 = $cmd->prepare($sql);
                $query2->execute();
                $row = $query2->fetch();
                $rubro = $row['rubro'];
                // consultar saldo del rubro, si saldo es suficiente realizar el registro sino eliminar documento y 
                $sq2 = "SELECT sum(valor) as obligado FROM pto_documento_detalles WHERE rubro = '$rubro' AND id_ctb_doc = $id_ctb_doc  AND tipo_mov ='COP'";
                $rs2 = $cmd->query($sq2);
                $obligado = $rs2->fetch();
                $valor_obligado = $obligado['obligado'];
                $sq3 = "SELECT sum(valor) as comprometido FROM pto_documento_detalles WHERE rubro = '$rubro' AND id_pto_doc = $id_crrp AND tipo_mov ='CRP'";
                $rs3 = $cmd->query($sq3);
                $comprometido = $rs3->fetch();
                $valor_comprometido = $comprometido['comprometido'];
                $saldo = $valor_comprometido - $valor_obligado;
                if ($valore > 0) {
                    if ($saldo >= $valore) {
                        $query->execute();
                        if ($cmd->lastInsertId() > 0) {
                            $id = $cmd->lastInsertId();
                            $total = $total + $valore;
                        }
                    } else {
                        $response[] = array("value" => 'er', "total" => $total);
                    }
                }
            }
            // Espacio para response
            $response[] = array("value" => 'ok', "total" => $total);
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
    echo json_encode($response);
}
