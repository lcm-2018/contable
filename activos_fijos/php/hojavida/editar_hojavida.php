<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = date('Y-m-d H:i:s');
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5704, 2) && $oper == 'add' && $_POST['id_hv'] == -1) ||
        (PermisosUsuario($permisos, 5704, 3) && $oper == 'add' && $_POST['id_hv'] != -1) ||
        (PermisosUsuario($permisos, 5704, 4) && $oper == 'del') || $id_rol == 1
    ) {

        if ($oper == 'add') {
            $id_hv = $_POST['id_hv'];
        
            if ($id_hv == -1) {                
                $sql = "INSERT INTO acf_hojavida (
                    placa,num_serial,id_marca,valor,tipo_activo,id_articulo,modelo,id_sede,id_area,id_responsable,id_proveedor,lote,fecha_fabricacion,
                    reg_invima,fabricante,lugar_origen,representante,dir_representante,tel_representante,recom_fabricante,
                    id_tipo_ingreso,fecha_adquisicion,fecha_instalacion,periodo_garantia,vida_util,calif_4725,calibracion,
                    vol_min,vol_max,frec_min,frec_max,pot_min,pot_max,cor_min,cor_max,temp_min,temp_max,riesgo,uso,
                    cb_diagnostico,cb_prevencion,cb_rehabilitacion,cb_analisis_lab,cb_trat_mant,estado_general,
                    causa_est_general,fecha_fuera_servicio,id_usr_crea,fec_creacion,id_usr_actualiza,fec_actualiza,estado
                ) VALUES (
                    :placa,:num_serial,:id_marca,:valor,:tipo_activo,:id_articulo,:modelo,:id_sede,:id_area,:id_responsable,:id_proveedor,:lote,:fecha_fabricacion,
                    :reg_invima,:fabricante,:lugar_origen,:representante,:dir_representante,:tel_representante,:recom_fabricante,
                    :id_tipo_ingreso,:fecha_adquisicion,:fecha_instalacion,:periodo_garantia,:vida_util,:calif_4725,:calibracion,
                    :vol_min,:vol_max,:frec_min,:frec_max,:pot_min,:pot_max,:cor_min,:cor_max,:temp_min,:temp_max,:riesgo,:uso,
                    :cb_diagnostico,:cb_prevencion,:cb_rehabilitacion,:cb_analisis_lab,:cb_trat_mant,:estado_general,
                    :causa_est_general,:fecha_fuera_servicio,:id_usr_crea,:fec_creacion,:id_usr_actualiza,:fec_actualiza,:estado
                )";
                $sql = $cmd->prepare($sql);
                
                $data = [
                    ':placa' => $_POST['placa'],
                    ':num_serial' => $_POST['num_serial'],
                    ':id_marca' => $_POST['id_marca'],
                    ':valor' => $_POST['valor'],
                    ':tipo_activo' => $_POST['tipo_activo'] ? $_POST['tipo_activo'] : 0,
                    ':id_articulo' => $_POST['id_articulo'],
                    ':modelo' => $_POST['modelo'],
                    ':id_sede' => $_POST['id_sede'],
                    ':id_area' => $_POST['id_area'],
                    ':id_responsable' => $_POST['id_responsable'],
                    ':id_proveedor' => $_POST['id_proveedor'] ? $_POST['id_proveedor'] : 0,
                    ':lote' => $_POST['lote'],
                    ':fecha_fabricacion' => $_POST['fecha_fabricacion'] ? date('Y-m-d', strtotime($_POST['fecha_fabricacion'])) : null,
                    ':reg_invima' => $_POST['reg_invima'],
                    ':fabricante' => $_POST['fabricante'],
                    ':lugar_origen' => $_POST['lugar_origen'],
                    ':representante' => $_POST['representante'],
                    ':dir_representante' => $_POST['dir_representante'],
                    ':tel_representante' => $_POST['tel_representante'],
                    ':recom_fabricante' => $_POST['recom_fabricante'],
                    ':id_tipo_ingreso' => $_POST['id_tipo_ingreso'] ? $_POST['id_tipo_ingreso'] : null,
                    ':fecha_adquisicion' => $_POST['fecha_adquisicion'] ? date('Y-m-d', strtotime($_POST['fecha_adquisicion'])) : null,
                    ':fecha_instalacion' => $_POST['fecha_instalacion'] ? date('Y-m-d', strtotime($_POST['fecha_instalacion'])) : null,
                    ':periodo_garantia' => $_POST['periodo_garantia'],
                    ':vida_util' => $_POST['vida_util'],
                    ':calif_4725' => $_POST['calif_4725'] ? $_POST['calif_4725'] : 0,
                    ':calibracion' => $_POST['calibracion'],
                    ':vol_min' => $_POST['vol_min'] ? $_POST['vol_min'] : null,
                    ':vol_max' => $_POST['vol_max'] ? $_POST['vol_max'] : null,
                    ':frec_min' => $_POST['frec_min'] ? $_POST['frec_min'] : null,
                    ':frec_max' => $_POST['frec_max'] ? $_POST['frec_max'] : null,
                    ':pot_min' => $_POST['pot_min'] ? $_POST['pot_min'] : null,
                    ':pot_max' => $_POST['pot_max'] ? $_POST['pot_max'] : null,
                    ':cor_min' => $_POST['cor_min'] ? $_POST['cor_min'] : null,
                    ':cor_max' => $_POST['cor_max'] ? $_POST['cor_max'] : null,
                    ':temp_min' => $_POST['temp_min'] ? $_POST['temp_min'] : null,
                    ':temp_max' => $_POST['temp_max'] ? $_POST['temp_max'] : null,
                    ':riesgo' => $_POST['riesgo'] ? $_POST['riesgo'] : 0,
                    ':uso' => $_POST['uso'] ? $_POST['uso'] : 0,
                    ':cb_diagnostico' => $_POST['cb_diagnostico'],
                    ':cb_prevencion' => $_POST['cb_prevencion'],
                    ':cb_rehabilitacion' => $_POST['cb_rehabilitacion'],
                    ':cb_analisis_lab' => $_POST['cb_analisis_lab'],
                    ':cb_trat_mant' => $_POST['cb_trat_mant'],
                    ':estado_general' => $_POST['estado_general'] ? $_POST['estado_general'] : 0,
                    ':causa_est_general' => $_POST['causa_est_general'],
                    ':fecha_fuera_servicio' => $_POST['fecha_fuera_servicio'] ? date('Y-m-d', strtotime($_POST['fecha_fuera_servicio'])) : null,
                    ':id_usr_crea' => $id_usr_crea,
                    ':fec_creacion' => $fecha_crea,
                    ':id_usr_actualiza' => $id_usr_crea,
                    ':fec_actualiza' => $fecha_crea,
                    ':estado' => $_POST['estado'],
                ];                
                $inserted = $sql->execute($data);

                if ($inserted) {
                    $id_hv = $cmd->lastInsertId();
                    $res['mensaje'] = 'ok';
                    $res['id_hv'] = $id_hv;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }   
            } else {            
                $sql = "UPDATE acf_hojavida SET
                    placa = :placa,
                    num_serial = :num_serial,
                    id_marca = :id_marca,
                    valor = :valor,
                    tipo_activo = :tipo_activo,
                    id_articulo = :id_articulo,
                    modelo = :modelo,
                    id_proveedor = :id_proveedor,
                    lote = :lote,
                    fecha_fabricacion = :fecha_fabricacion,
                    reg_invima = :reg_invima,
                    fabricante = :fabricante,
                    lugar_origen = :lugar_origen,
                    representante = :representante,
                    dir_representante = :dir_representante,
                    tel_representante = :tel_representante,
                    recom_fabricante = :recom_fabricante,
                    id_tipo_ingreso = :id_tipo_ingreso,
                    fecha_adquisicion = :fecha_adquisicion,
                    fecha_instalacion = :fecha_instalacion,
                    periodo_garantia = :periodo_garantia,
                    vida_util = :vida_util,
                    calif_4725 = :calif_4725,
                    calibracion = :calibracion,
                    vol_min = :vol_min,
                    vol_max = :vol_max,
                    frec_min = :frec_min,
                    frec_max = :frec_max,
                    pot_min = :pot_min,
                    pot_max = :pot_max,
                    cor_min = :cor_min,
                    cor_max = :cor_max,
                    temp_min = :temp_min,
                    temp_max = :temp_max,
                    riesgo = :riesgo,
                    uso = :uso,
                    cb_diagnostico = :cb_diagnostico,
                    cb_prevencion = :cb_prevencion,
                    cb_rehabilitacion = :cb_rehabilitacion,
                    cb_analisis_lab = :cb_analisis_lab,
                    cb_trat_mant = :cb_trat_mant,
                    estado_general = :estado_general,
                    causa_est_general = :causa_est_general,
                    fecha_fuera_servicio = :fecha_fuera_servicio,
                    id_usr_actualiza = :id_usr_actualiza,
                    fec_actualiza = :fec_actualiza,
                    estado = :estado
                    WHERE id_activo_fijo = :id_hv";
                $sql = $cmd->prepare($sql);

                // Asignar valores utilizando bindValue
                $sql->bindValue(':placa', $_POST['placa']);
                $sql->bindValue(':num_serial', $_POST['num_serial']);
                $sql->bindValue(':id_marca', $_POST['id_marca'], PDO::PARAM_INT);
                $sql->bindValue(':valor', $_POST['valor']);
                $sql->bindValue(':tipo_activo', $_POST['tipo_activo'] ? $_POST['tipo_activo'] : 0, PDO::PARAM_INT);
                $sql->bindValue(':id_articulo', $_POST['id_articulo'], PDO::PARAM_INT);
                $sql->bindValue(':modelo', $_POST['modelo']);
                $sql->bindValue(':id_proveedor', $_POST['id_proveedor'] ? $_POST['id_proveedor'] : 0, PDO::PARAM_INT);
                $sql->bindValue(':lote', $_POST['lote']);
                $sql->bindValue(':fecha_fabricacion', $_POST['fecha_fabricacion'] ? date('Y-m-d', strtotime($_POST['fecha_fabricacion'])) : null);                
                $sql->bindValue(':reg_invima', $_POST['reg_invima']);
                $sql->bindValue(':fabricante', $_POST['fabricante']);
                $sql->bindValue(':lugar_origen', $_POST['lugar_origen']);
                $sql->bindValue(':representante', $_POST['representante']);
                $sql->bindValue(':dir_representante', $_POST['dir_representante']);
                $sql->bindValue(':tel_representante', $_POST['tel_representante']);
                $sql->bindValue(':recom_fabricante', $_POST['recom_fabricante']);
                $sql->bindValue(':id_tipo_ingreso', $_POST['id_tipo_ingreso'] ? $_POST['id_tipo_ingreso'] : null, PDO::PARAM_INT);
                $sql->bindValue(':fecha_adquisicion', $_POST['fecha_adquisicion'] ? date('Y-m-d', strtotime($_POST['fecha_adquisicion'])) : null);
                $sql->bindValue(':fecha_instalacion', $_POST['fecha_instalacion'] ? date('Y-m-d', strtotime($_POST['fecha_instalacion'])) : null);
                $sql->bindValue(':periodo_garantia', $_POST['periodo_garantia']);
                $sql->bindValue(':vida_util', $_POST['vida_util']);
                $sql->bindValue(':calif_4725', $_POST['calif_4725'] ? $_POST['calif_4725'] : 0, PDO::PARAM_INT);
                $sql->bindValue(':calibracion', $_POST['calibracion']);
                $sql->bindValue(':vol_min', $_POST['vol_min'] ? $_POST['vol_min'] : null, PDO::PARAM_INT);
                $sql->bindValue(':vol_max', $_POST['vol_max'] ? $_POST['vol_max'] : null, PDO::PARAM_INT);
                $sql->bindValue(':frec_min', $_POST['frec_min'] ? $_POST['frec_min'] : null, PDO::PARAM_INT);
                $sql->bindValue(':frec_max', $_POST['frec_max'] ? $_POST['frec_max'] : null, PDO::PARAM_INT);
                $sql->bindValue(':pot_min', $_POST['pot_min'] ? $_POST['pot_min'] : null, PDO::PARAM_INT);
                $sql->bindValue(':pot_max', $_POST['pot_max'] ? $_POST['pot_max'] : null, PDO::PARAM_INT);
                $sql->bindValue(':cor_min', $_POST['cor_min'] ? $_POST['cor_min'] : null, PDO::PARAM_INT);
                $sql->bindValue(':cor_max', $_POST['cor_max'] ? $_POST['cor_max'] : null, PDO::PARAM_INT);
                $sql->bindValue(':temp_min', $_POST['temp_min'] ? $_POST['temp_min'] : null, PDO::PARAM_INT);
                $sql->bindValue(':temp_max', $_POST['temp_max'] ? $_POST['temp_max'] : null, PDO::PARAM_INT);
                $sql->bindValue(':riesgo', $_POST['riesgo'] ? $_POST['riesgo'] : 0, PDO::PARAM_INT);
                $sql->bindValue(':uso', $_POST['uso'] ? $_POST['uso'] : 0, PDO::PARAM_INT);
                $sql->bindValue(':cb_diagnostico', $_POST['cb_diagnostico']);
                $sql->bindValue(':cb_prevencion', $_POST['cb_prevencion']);
                $sql->bindValue(':cb_rehabilitacion', $_POST['cb_rehabilitacion']);
                $sql->bindValue(':cb_analisis_lab', $_POST['cb_analisis_lab']);
                $sql->bindValue(':cb_trat_mant', $_POST['cb_trat_mant']);
                $sql->bindValue(':estado_general', $_POST['estado_general'] ? $_POST['estado_general'] : 0, PDO::PARAM_INT);
                $sql->bindValue(':causa_est_general', $_POST['causa_est_general']);
                $sql->bindValue(':fecha_fuera_servicio', $_POST['fecha_fuera_servicio'] ? date('Y-m-d', strtotime($_POST['fecha_fuera_servicio'])) : null);
                $sql->bindValue(':id_usr_actualiza', $id_usr_crea, PDO::PARAM_INT);
                $sql->bindValue(':fec_actualiza', $fecha_crea);
                $sql->bindValue(':estado', $_POST['estado'], PDO::PARAM_INT);
                $sql->bindValue(':id_hv', $id_hv, PDO::PARAM_INT);

                $updated = $sql->execute();

                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id_hv'] = $id_hv;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            }
        }    

        if ($oper == 'del') {
            $id = $_POST['id'];
            $sql = "DELETE FROM acf_hojavida WHERE id_activo_fijo=" . $id;
            $rs = $cmd->query($sql);
            if ($rs) {
                $res['mensaje'] = 'ok';
            } else {
                $res['mensaje'] = $cmd->errorInfo()[2];
            }
        }    

    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
