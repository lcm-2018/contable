<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../../conexion.php';
$id_doc = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no disponible');
$id_detalle = $_POST['op'];
$val_fact = str_replace(",", "", $_POST['valor_fact']);
$val_arq = str_replace(",", "", $_POST['valor_arq']);
$fecha_ini = $_POST['fecha_arqueo_ini'];
$fecha_fin = $_POST['fecha_arqueo_fin'];
$id_tercero_api = $_POST['id_facturador'];
$observaciones = $_POST['observaciones'];
$arqueos = $_POST['arqueo'] ?? [];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
$response['status'] = 'error';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

try {
    if ($id_detalle == 0) {
        $query = "INSERT INTO `tes_causa_arqueo`
                    (`id_ctb_doc`,`fecha_ini`,`fecha_fin`,`id_tercero`,`valor_fac`,`valor_arq`,`observaciones`,`id_user_reg`,`fecha_reg`)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->bindParam(2, $fecha_ini, PDO::PARAM_STR);
        $query->bindParam(3, $fecha_fin, PDO::PARAM_STR);
        $query->bindParam(4, $id_tercero_api, PDO::PARAM_INT);
        $query->bindParam(5, $val_fact, PDO::PARAM_STR);
        $query->bindParam(6, $val_arq, PDO::PARAM_STR);
        $query->bindParam(7, $observaciones, PDO::PARAM_STR);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2, PDO::PARAM_STR);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $sq = "INSERT INTO `tes_ids_arqueo` (`id_causa`, `id_arqueo`, `id_user_reg`,`fec_reg`)
                    VALUES (?, ?, ?, ?)";
            $sq = $cmd->prepare($sq);

            $sq->bindParam(1, $id, PDO::PARAM_INT);
            $sq->bindParam(2, $id_arqueo, PDO::PARAM_INT);
            $sq->bindParam(3, $iduser, PDO::PARAM_INT);
            $sq->bindParam(4, $fecha2, PDO::PARAM_STR);

            $up = "UPDATE `fac_arqueo` SET `estado` = 3 WHERE `id_arqueo` = ?";
            $up = $cmd->prepare($up);
            $up->bindParam(1, $id_arqueo, PDO::PARAM_INT);

            foreach ($arqueos as $key => $value) {
                $id_arqueo = $key;
                $sq->execute();
                $up->execute();
            }
            $query = "SELECT SUM(`valor_arq`) AS `valor` FROM `tes_causa_arqueo` WHERE `id_ctb_doc` = $id_doc";
            $rs = $cmd->query($query);
            $valor = $rs->fetch();
            $response['status'] = 'ok';
            $response['valor'] = $valor['valor'];
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `tes_causa_arqueo`
                    SET `fecha_ini` = ?
                        , `fecha_fin` = ?
                        , `id_tercero` = ?
                        , `valor_fac` = ?
                        , `valor_arq` = ?
                        , `observaciones` = ?
                    WHERE `id_causa_arqueo` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $fecha_ini, PDO::PARAM_STR);
        $query->bindParam(2, $fecha_fin, PDO::PARAM_STR);
        $query->bindParam(3, $id_tercero_api, PDO::PARAM_INT);
        $query->bindParam(4, $val_fact, PDO::PARAM_STR);
        $query->bindParam(5, $val_arq, PDO::PARAM_STR);
        $query->bindParam(6, $observaciones, PDO::PARAM_STR);
        $query->bindParam(7, $id_detalle, PDO::PARAM_INT);
        if (!($query->execute())) {
            $response['msg'] = $query->errorInfo()[2] . $query->queryString;
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `tes_causa_arqueo` SET `id_user_act` = ?, `fecha_act` = ? WHERE (`id_causa_arqueo` = ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $iduser, PDO::PARAM_INT);
                $query->bindParam(2, $fecha2, PDO::PARAM_STR);
                $query->bindParam(3, $id_detalle, PDO::PARAM_INT);
                $query->execute();
                $query = "SELECT SUM(`valor_arq`) AS `valor` FROM `tes_causa_arqueo` WHERE `id_ctb_doc` = $id_doc";
                $rs = $cmd->query($query);
                $valor = $rs->fetch();
                $response['status'] = 'ok';
                $response['valor'] = $valor['valor'];
            } else {
                $response['msg'] = 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

exit(json_encode($response));
