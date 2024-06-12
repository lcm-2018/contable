<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
//API URL
$cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : exit('Acción no permitida');
$idProd = $_POST['id_acfijo'];
$idtentrada = $_POST['tipoEntrada'];
$id_acfi_do = $_POST['id_acfi_det'];
$valunit = $_POST['numValUnita'];
$mantenimiento = $_POST['mantenimiento'];
$depresiacion = $_POST['slcDepresiacion'];
$marca = $_POST['txtMarca'];
$modelo = $_POST['txtModelo'];
$tipo_activo = $_POST['slcTipoActivo'];
$series =  $_POST['serieUp'];
$observacion = $_POST['txtObservaActFijo'];
$iduser = $_SESSION['id_user'];
$tentra = 'DO';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$recibido = 0;
$buscar_serial = '0';
foreach ($series as $s) {
    $buscar_serial .= ",'" . $s . "'";
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `num_serial` FROM  `seg_num_serial` WHERE `num_serial` IN ($buscar_serial)";
    $rs = $cmd->query($sql);
    $lseriales = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($lseriales)) {
    $lseriales_exist = '';
    foreach ($lseriales as $serial) {
        $lseriales_exist .= $serial['num_serial'] . '<br>';
    }
    echo 'Serial(es) ya existentes : <br>' . $lseriales_exist;
    exit();
} else {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_entra_detalle_activos_fijos` (`id_prod`, `id_entra_acfijo_do`, `mantenimiento`, `depreciable`, `marca`, `modelo`, `val_unit`, `cantidad`, `descripcion`, `id_tipo_activo`, `id_user_reg`, `fec_reg`)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idProd, PDO::PARAM_INT);
        $sql->bindParam(2, $id_acfi_do, PDO::PARAM_INT);
        $sql->bindParam(3, $mantenimiento, PDO::PARAM_INT);
        $sql->bindParam(4, $depresiacion, PDO::PARAM_INT);
        $sql->bindParam(5, $marca, PDO::PARAM_STR);
        $sql->bindParam(6, $modelo, PDO::PARAM_STR);
        $sql->bindParam(7, $valunit, PDO::PARAM_STR);
        $sql->bindParam(8, $cantidad, PDO::PARAM_INT);
        $sql->bindParam(9, $observacion, PDO::PARAM_STR);
        $sql->bindParam(10, $tipo_activo, PDO::PARAM_INT);
        $sql->bindParam(11, $iduser, PDO::PARAM_INT);
        $sql->bindValue(12, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        $id_reg = $cmd->lastInsertId();
        if ($id_reg > 0) {
            $sql = "SELECT
                        `tb_tipo_compra`.`id_tipo` AS `id_tipo_compra`
                        , `tb_tipo_compra`.`tipo_compra`
                        , `tb_tipo_contratacion`.`id_tipo` AS `id_tipo_contrato`
                        , `tb_tipo_contratacion`.`tipo_contrato`
                        , `tb_tipo_bien_servicio`.`id_tipo_b_s`
                        , `tb_tipo_bien_servicio`.`tipo_bn_sv`
                        , `ctt_bien_servicio`.`id_b_s`
                        , `ctt_bien_servicio`.`bien_servicio`
                    FROM
                        `ctt_bien_servicio`
                        INNER JOIN `tb_tipo_bien_servicio` 
                            ON (`ctt_bien_servicio`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
                        INNER JOIN `tb_tipo_contratacion` 
                            ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
                        INNER JOIN `tb_tipo_compra` 
                            ON (`tb_tipo_contratacion`.`id_tipo_compra` = `tb_tipo_compra`.`id_tipo`)
                    WHERE `ctt_bien_servicio`.`id_b_s` = $idProd";
            $rs = $cmd->query($sql);
            $placa_gen = $rs->fetch();
            $baseP = str_pad($placa_gen['id_tipo_compra'], 2, '0', STR_PAD_LEFT) . str_pad($placa_gen['id_tipo_contrato'], 2, '0', STR_PAD_LEFT) . str_pad($placa_gen['id_tipo_b_s'], 3, '0', STR_PAD_LEFT) . str_pad($placa_gen['id_b_s'], 4, '0', STR_PAD_LEFT);
            foreach ($series as $serie) {
                if ($serie != '0') {
                    $sql = "INSERT INTO `seg_num_serial` (`id_activo_fijo`, `num_serial`, `tipo_entra`, `id_user_reg`, `fec_reg`)
                            VALUES (?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id_reg, PDO::PARAM_INT);
                    $sql->bindParam(2, $serie, PDO::PARAM_STR);
                    $sql->bindParam(3, $tentra, PDO::PARAM_STR);
                    $sql->bindParam(4, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    $id_serpl = $cmd->lastInsertId();
                    if ($id_serpl > 0) {
                        $placa = $baseP . str_pad($id_serpl, 4, '0', STR_PAD_LEFT);
                        $sql = "UPDATE `seg_num_serial` SET `placa` = ? WHERE `id_serial` = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $placa, PDO::PARAM_STR);
                        $sql->bindParam(2, $id_serpl, PDO::PARAM_INT);
                        $sql->execute();
                        $recibido++;
                        $centro_costo = 1;
                        $estado_af = 1;
                        $sql = "INSERT INTO `seg_ubica_traslado_centro_costo` (`id_serial`, `id_centro_costo`, `fecha`, `estado`, `id_user_reg`, `fec_reg`) 
                                VALUES (? , ? , ? , ? , ? , ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $id_serpl, PDO::PARAM_INT);
                        $sql->bindParam(2, $centro_costo, PDO::PARAM_INT);
                        $sql->bindValue(3, $date->format('Y-m-d'));
                        $sql->bindParam(4, $estado_af, PDO::PARAM_INT);
                        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
                        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                        $sql->execute();
                    } else {
                        echo $sql->errorInfo()[2] . '<br> Serial: ' . $serie . '<br>';
                    }
                }
            }
        } else {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
if ($recibido > 0) {
    $estado  = 2;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `acf_entrada` SET `estado` = ? , `id_user_act` = ? ,`fec_act` = ?  WHERE `id_entra_af` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $iduser, PDO::PARAM_INT);
    $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(4, $id_acfi_do, PDO::PARAM_INT);
    $sql->execute();
    echo '1';
} else {
    echo 'No se registro ninun serial';
}
