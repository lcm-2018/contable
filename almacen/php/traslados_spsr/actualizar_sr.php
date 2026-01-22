<?php

if (isset($_POST['id_seddes'])) {
    session_start();

    include '../../../conexion.php';
    include '../common/funciones_generales.php';    
    include '../../../permisos.php';
    //Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir

    $idusr = $_SESSION['id_user'];
    $idrol = $_SESSION['rol'];

    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $res['mensaje'] = 'Error';
        if (PermisosUsuario($permisos, 5017, 2) || PermisosUsuario($permisos, 5017, 3) || $idrol == 1) {

            $sql = "SELECT ip_sede,bd_sede,pw_sede,us_sede,pt_http FROM tb_sedes WHERE id_sede=" . $_POST['id_seddes'] . " LIMIT 1";
            $rs = $cmd->query($sql);
            $obj_sede = $rs->fetch();
                
            $ip_pr = explode(':', $obj_sede['ip_sede']);
            $ip = $ip_pr[0];
            $port = $ip_pr[1];
            $database = $obj_sede['bd_sede'];
            $password = $obj_sede['pw_sede'];
            $user = $obj_sede['us_sede'];
            $continuar = true;

            // Verifica conexión a servicio remoto
            if (!isHostReachable($ip) && $continuar) {
                $res['mensaje'] = "Error: No hay respuesta de la IP del servidor ($ip). Verifique la red.";
                $continuar = false;
            } 
            if (!isMySQLPortOpen($ip, $port) && $continuar) {
                $res['mensaje'] = "Error: El servidor MySQL no responde en $ip:$port. Verifique el servicio.";
                $continuar = false;
            } 
            if($continuar){
                list($ok, $msg) = canConnectToDatabase($ip, $port, $user, $password, $database);
                if (!$ok) {
                    $res['mensaje'] = "Error: No se pudo conectar a la base de datos '$database' en $ip:$port.<br>Detalle: $msg";
                    $continuar = false;
                }    
            }    

            if ($continuar){
                
                $bodega = bodega_principal($cmd);
                $id_bodega_origen = $bodega['id_bodega'] ? $bodega['id_bodega'] : 0;

                $where = " WHERE id_traslado_origen IS NULL AND id_bodega_origen=$id_bodega_origen";
                if($idrol !=1){
                    $where .= " AND id_bodega_origen IN (SELECT id_bodega FROM seg_bodegas_usuario WHERE id_usuario=$idusr)";
                }
                $where .= " AND fec_traslado BETWEEN '" . $_POST['fec_ini'] . "' AND '" . $_POST['fec_fin'] . "'";
                $where .= " AND id_sede_destino='" . $_POST['id_seddes'] . "'";
                $where .= " AND id_bodega_destino='" . $_POST['id_boddes'] . "'";
                
                if (isset($_POST['id_tra']) && $_POST['id_tra']) {
                    $where .= " AND id_traslado='" . $_POST['id_tra'] . "'";
                }
                if (isset($_POST['num_tra']) && $_POST['num_tra']) {
                    $where .= " AND num_traslado='" . $_POST['num_tra'] . "'";
                }
                if (isset($_POST['estado']) && strlen($_POST['estado'])) {
                    $where .= " AND estado=" . $_POST['estado'];
                }
                if (isset($_POST['estado2']) && strlen($_POST['estado2'])) {
                    $where .= " AND estado2=" . $_POST['estado2'];
                }

                $sql = "SELECT GROUP_CONCAT(id_traslado) AS ids FROM far_traslado_r" . $where;
                $rs = $cmd->query($sql);

                if (!$rs){
                    $res['mensaje'] = 'Error consultando registros de Trasslados locales';
                } else {    
                    $obj = $rs->fetch(PDO::FETCH_ASSOC);
                    $ids = !empty($obj['ids']) ? $obj['ids'] : '';

                    if ($ids){
                        $cmd1 = new PDO("$bd_driver:host=$ip;port=$port;dbname=$database;$charset", $user, $password);
                        $res['mensaje'] = 'ok';

                        $sql = "SELECT id_traslado_origen,estado2 FROM far_traslado_r WHERE id_traslado_origen IN (" . $ids . ")";
                        $rsR = $cmd1->query($sql);
                        if (!$rsR){
                            $res['mensaje'] = 'Se perdió la conexión con el equipo remoto';
                        } else {
                            $obj_est = $rsR->fetchAll(PDO::FETCH_ASSOC);
                            $res['mensaje'] = 'ok';
                        
                            foreach ($obj_est as $est) {                                                
                                if (isset($est['id_traslado_origen']) && isset($est['estado2'])){
                                    $sql = "UPDATE far_traslado_r SET estado2=" . $est['estado2'] . " WHERE id_traslado=" . $est['id_traslado_origen'];
                                    $rs = $cmd->query($sql);
                                }    
                            }
                        }    
                                    
                    } else {
                        $res['mensaje'] = 'Debe seleccionar un registro para actualizar estado de Sede Remota';
                    }
                }    
            }
        } else {
            $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
        }   

        $cmd = null;
    } catch (PDOException $e) {
        $res['mensaje'] = $e->getCode();
    }
    echo json_encode($res);
}