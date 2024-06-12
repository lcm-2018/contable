<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$mes = isset($_POST['mes']) ? $_POST['mes'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
$nextm = $mes != '12' ? intval($mes) + 1 : 12;
$endday = $mes != '12' ? '01' : '30';
$fecha_n = $vigencia . '-' . str_pad($nextm, 2, '0', STR_PAD_LEFT) . '-' . $endday;
$fecha_r = date('Y-m-d', strtotime($fecha_n . ' -1 day'));
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$res = [];
$res['satus'] = 'error';
$res['msg'] = '';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_serial`  FROM `nom_liq_depreciacion` WHERE `mes` = '$mes' AND `anio` = '$vigencia'";
    $rs = $cmd->query($sql);
    $depmesliq = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    $res['msg'] .= $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_serial`,`fec_inicia`,`fec_termina`,`val_deterioro` FROM `seg_mantenimiento_acfijo`
            WHERE `id_mmto` IN (SELECT MAX(`id_mmto`) FROM `seg_mantenimiento_acfijo` GROUP BY `id_serial`)";
    $rs = $cmd->query($sql);
    $matenimiento = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    $res['msg'] .= $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_num_serie`,`fec_inicia`,`vida_util`, `valor_residual`, `val_unit`, `tot_depreciado`
            FROM
                (SELECT
                    `seg_depreciacion`.`id_num_serie`
                    , `seg_depreciacion`.`fec_inicia`
                    , `seg_depreciacion`.`vida_util`
                    , IFNULL(`seg_depreciacion`.`valor_residual`,0) AS `valor_residual`
                    , `seg_entra_detalle_activos_fijos`.`val_unit`
                FROM
                    `seg_depreciacion`
                    INNER JOIN `seg_num_serial` 
                        ON (`seg_depreciacion`.`id_num_serie` = `seg_num_serial`.`id_serial`)
                    INNER JOIN `seg_entra_detalle_activos_fijos` 
                        ON (`seg_num_serial`.`id_activo_fijo` = `seg_entra_detalle_activos_fijos`.`id_acfijo`)) AS `t1`
            LEFT JOIN
                (SELECT
                    `id_serial`
                    , SUM(`val_depreciado`) AS `tot_depreciado`
                FROM
                    `nom_liq_depreciacion`
                GROUP BY `id_serial`) AS `t2`
                ON (`t1`.`id_num_serie`=`t2`.`id_serial`)
            WHERE `val_unit` > `tot_depreciado` AND `vida_util` > 0";
    $rs = $cmd->query($sql);
    $acfijos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    $res['msg'] .= $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$depreciados = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `nom_liq_depreciacion` (`id_serial`, `dias`, `val_depreciado`, `fecha`, `mes`, `anio`,  `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_serial, PDO::PARAM_INT);
    $sql->bindParam(2, $dias, PDO::PARAM_INT);
    $sql->bindParam(3, $valor, PDO::PARAM_STR);
    $sql->bindParam(4, $fecha_r, PDO::PARAM_STR);
    $sql->bindParam(5, $mes, PDO::PARAM_STR);
    $sql->bindParam(6, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(7, $id_user, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
    foreach ($acfijos as $row) {
        $id_serial = $row['id_num_serie'];
        $fecha_ini = $row['fec_inicia'];
        $key = array_search($id_serial, array_column($depmesliq, 'id_serial'));
        if ($key === false && $fecha_ini <= $fecha_n) {
            $diasmmto = 0;
            $depreciacion = ($row['val_unit'] - $row['valor_residual']) / $row['vida_util'];
            $valdiadp = ($depreciacion / 30);
            $key = array_search($id_serial, array_column($matenimiento, 'id_serial'));
            if ($key !== false) {
                $mimmto = explode('-', $matenimiento[$key]['fec_inicia'])[1] == $mes ? true : false;
                if ($mimmto) {
                    $fecha1 = new DateTime($matenimiento[$key]['fec_inicia']);
                    $fecha2 = new DateTime($matenimiento[$key]['fec_termina']);
                    $intervalo = $fecha1->diff($fecha2);
                    $diasmmto = $intervalo->format('%a');
                }
            }
            $dias = 30 - $diasmmto;
            $valor = $valdiadp * $dias;
            $validar = $row['val_unit'] - $row['tot_depreciado'];
            $diferencia = $validar - $valor;
            if ($diferencia < 0) {
                $valor = $validar;
            }
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $depreciados++;
            } else {
                $res['msg'] .= $sql->errorInfo()[2];
            }
        }
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['msg'] .= $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($depreciados > 0) {
    $res['status'] = 'ok';
    $res['msg'] .= "Se depreciaron $depreciados activos fijos";
}
echo json_encode($res);
