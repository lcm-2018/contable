<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$tercero = $_POST['tercero'];
$fecha_ini = $_POST['fecha_ini'];
$fecha_fin = $_POST['fecha_fin'];
$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT  
                `tb_terceros`.`id_tercero_api`, `tr`.`valor_copago`,`tr`.`val_copago_anulado`
            FROM 
                (SELECT 
                    `num_documento`, SUM(`valor`) AS `valor_copago`,SUM(`valor_anulado`) AS `val_copago_anulado` 
                FROM
                    (SELECT
                        `seg_usuarios_sistema`.`num_documento`   
                        , `fac_facturacion`.`val_copago` AS `valor`
                        , CASE `fac_facturacion`.`estado` WHEN 0 THEN `fac_facturacion`.`val_copago` ELSE 0 END  AS `valor_anulado`    
                    FROM `fac_facturacion` 
                        INNER JOIN `seg_usuarios_sistema` 
                            ON (`fac_facturacion`.`id_usr_crea` = `seg_usuarios_sistema`.`id_usuario`)
                        INNER JOIN `adm_ingresos` 
                            ON (`fac_facturacion`.`id_ingreso`=`adm_ingresos`.`id_ingreso`)
                        INNER JOIN `adm_tipo_atencion` 
                            ON (`adm_ingresos`.`id_tipo_atencion`=`adm_tipo_atencion`.`id_tipo`)
                    WHERE `fac_facturacion`.`val_copago` > 0 AND `fac_facturacion`.`estado` <> 1 AND `fac_facturacion`.`fec_factura` BETWEEN '$fecha_ini' AND '$fecha_fin'
                    UNION ALL
                    SELECT
                        `seg_usuarios_sistema`.`num_documento`   
                        , `far_ventas`.`val_factura` AS `valor`
                        , CASE `far_ventas`.`estado` WHEN 0 THEN `far_ventas`.`val_factura` ELSE 0 END `valor_anulado`     
                    FROM
                        `far_ventas` 
                        INNER JOIN `seg_usuarios_sistema`  
                            ON (`far_ventas`.`id_usr_crea` = `seg_usuarios_sistema`.`id_usuario`)
                    WHERE `far_ventas`.`estado` <> 1 AND `far_ventas`.`fec_venta` BETWEEN '$fecha_ini' AND '$fecha_fin' 
                    UNION ALL
                    SELECT
                        `seg_usuarios_sistema`.`num_documento`   
                        , `fac_otros`.`val_factura` AS `valor`
                        , CASE `fac_otros`.`estado` WHEN 0 THEN `fac_otros`.`val_factura` ELSE 0 END `valor_anulado`    
                    FROM
                        `fac_otros` 
                        INNER JOIN `seg_usuarios_sistema` 
                            ON (`fac_otros`.`id_usr_crea` = `seg_usuarios_sistema`.`id_usuario`)
                    WHERE `fac_otros`.`id_eps` = 1 AND `fac_otros`.`estado` <> 1 AND `fac_otros`.`val_factura` > 0 AND `fac_otros`.`fec_factura` BETWEEN '$fecha_ini' AND '$fecha_fin' ) AS `t`
                GROUP BY `num_documento`) AS `tr` 
                LEFT JOIN `tb_terceros` 
                    ON (`tr`.`num_documento` = `tb_terceros`.`nit_tercero`)
            WHERE `tb_terceros`.`id_tercero_api` = $tercero";
    $rs = $cmd->query($sql);
    $valores = $rs->fetch(PDO::FETCH_ASSOC);
    if (!empty($valores)) {
        $response['status'] = 'ok';
        $response['facturado'] = $valores['valor_copago'] - $valores['val_copago_anulado'];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
exit(json_encode($response));
