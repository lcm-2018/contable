<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
if (isset($_REQUEST['check'])) {
    if (isset($_POST['id_cotizacion'])) {
        $id_cotiza = $_POST['id_cotizacion'];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT
                        `ctt_adquisicion_detalles`.`id_detalle_adq`
                        , `ctt_adquisicion_detalles`.`id_adquisicion`
                        , `objeto`
                        , `ctt_adquisicion_detalles`.`id_bn_sv`
                        , `ctt_bien_servicio`.`bien_servicio`
                        , `ctt_adquisicion_detalles`.`cantidad`
                        , `ctt_adquisicion_detalles`.`val_estimado_unid`
                    FROM
                        `ctt_adquisicion_detalles`
                    INNER JOIN `ctt_adquisiciones` 
                        ON (`ctt_adquisicion_detalles`.`id_adquisicion` = `ctt_adquisiciones`.`id_adquisicion`)
                    INNER JOIN `ctt_bien_servicio` 
                        ON (`ctt_adquisicion_detalles`.`id_bn_sv` = `ctt_bien_servicio`.`id_b_s`)
                    WHERE 
                        `ctt_adquisicion_detalles`.`id_adquisicion` = '$id_cotiza'";
            $rs = $cmd->query($sql);
            $productos = $rs->fetchAll();
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        if (!empty($productos)) {
            $cotizacion = [];
            $cotizacion[] = [
                'id_cot' => $id_cotiza,
                'nit' =>  $_SESSION['nit_emp'],
                'objeto' => $productos[0]['objeto'],
            ];
            $lista = $_REQUEST['check'];
            $lis_ter = [];
            foreach ($lista as $l) {
                $lis_ter[] = $l;
            }
            $cotizacion[] = $lis_ter;
            $prods = [];
            foreach ($productos as $p) {
                $prods[] = [
                    'id_producto' => $p['id_detalle_adq'],
                    'id_bn_sv' => $p['id_bn_sv'],
                    'bien_servicio' => $p['bien_servicio'],
                    'cantidad' => $p['cantidad'],
                    'val_estimado_unid' => $p['val_estimado_unid']
                ];
            }
            $cotizacion[] = $prods;
            $json_string = json_encode($cotizacion);
            $file = 'productos.json';
            file_put_contents($file, $json_string);
            //API URL
            $url = $api . 'terceros/datos/res/nuevo/cotizacion/rsc';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res_api = curl_exec($ch);
            curl_close($ch);
            $res = json_decode($res_api, true);
            if ($res == 1) {
                $est = 4;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE ctt_adquisiciones SET estado = ? WHERE id_adquisicion = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $est, PDO::PARAM_INT);
                    $sql->bindParam(2, $id_cotiza, PDO::PARAM_INT);
                    $sql->execute();
                    $cambio = $sql->rowCount();
                    if (!($sql->execute())) {
                        print_r($sql->errorInfo()[2]);
                        exit();
                    } else {
                        if ($cambio > 0) {
                            $iduser = $_SESSION['id_user'];
                            $date = new DateTime('now', new DateTimeZone('America/Bogota'));
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "UPDATE ctt_adquisiciones SET  id_user_act = ? ,fec_act = ? WHERE id_adquisicion = ?";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                            $sql->bindParam(3, $id_cotiza, PDO::PARAM_INT);
                            $sql->execute();
                            if ($sql->rowCount() > 0) {
                                echo  1;
                            } else {
                                print_r($sql->errorInfo()[2]);
                            }
                        } else {
                            echo 'No se registró ningún nuevo dato';
                        }
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            } else {
                echo $res_api;
            }
        } else {
            echo 'Cotización actual, no tiene ningún producto asociado';
        }
    } else {
        echo 'Acción no permitida';
    }
} else {
    echo 'No se ha selecionado ningún tercero';
}
