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

    if ((PermisosUsuario($permisos, 5006, 2) && $oper == 'add' && $_POST['id_detalle'] == -1) ||
        (PermisosUsuario($permisos, 5006, 3) && $oper == 'add' && $_POST['id_detalle'] != -1) ||
        (PermisosUsuario($permisos, 5006, 4) && $oper == 'del') || $id_rol == 1
    ) {

        $id_hv = $_POST['id_hv'];

        $rs = $cmd->query($sql);
        $obj_ingreso = $rs->fetch();

        if ($oper == 'add') {
            if ($id_hv == -1) {
                
                $sql = "INSERT INTO acf_hojavida (
                    placa, serial, id_marca, valor, tipo_activo, id_articulo, modelo, id_sede, id_area, 
                    id_proveedor, lote, fecha_fabricacion, reg_invima, fabricante, lugar_origen, representante, 
                    dir_representante, tel_representante, imagen, recom_fabricante, tipo_adquisicion, fecha_adquisicion, 
                    fecha_instalacion, periodo_garantia, vida_util, calif_4725, calibracion, vol_min, vol_max, frec_min, 
                    frec_max, pot_min, pot_max, cor_min, cor_max, temp_min, temp_max, riesgo, uso, cb_diagnostico, 
                    cb_prevencion, cb_rehabilitacion, cb_analisis_lab, cb_trat_mant, estado_general, causa_est_general, 
                    fecha_fuera_servicio, id_usr_reg, fecha_reg, id_usr_act, fecha_act, estado
                ) VALUES (
                    :placa, :serial, :id_marca, :valor, :tipo_activo, :id_articulo, :modelo, :id_sede, :id_area, 
                    :id_proveedor, :lote, :fecha_fabricacion, :reg_invima, :fabricante, :lugar_origen, :representante, 
                    :dir_representante, :tel_representante, :imagen, :recom_fabricante, :tipo_adquisicion, :fecha_adquisicion, 
                    :fecha_instalacion, :periodo_garantia, :vida_util, :calif_4725, :calibracion, :vol_min, :vol_max, :frec_min, 
                    :frec_max, :pot_min, :pot_max, :cor_min, :cor_max, :temp_min, :temp_max, :riesgo, :uso, :cb_diagnostico, 
                    :cb_prevencion, :cb_rehabilitacion, :cb_analisis_lab, :cb_trat_mant, :estado_general, :causa_est_general, 
                    :fecha_fuera_servicio, :id_usr_reg, :fecha_reg, :id_usr_act, :fecha_act, :estado
                )";
        
                // Preparar la consulta
                $sql = $cmd->prepare($sql);
            
                // Datos para insertar (ejemplo)
                $fecha_fabricacion = date('Y-m-d', strtotime($_POST['fecha_fabricacion']));
                
                $data = [
                    ':placa' => $_POST['placa'],
                    ':serial' => $_POST['serial'],
                    ':id_marca' => $_POST['id_marca'],
                    ':valor' => $_POST['valor'],
                    ':tipo_activo' => $_POST['tipo_activo'],
                    ':id_articulo' => $_POST['id_articulo'],
                    ':modelo' => $_POST['modelo'],
                    ':id_sede' => $_POST['id_sede'],
                    ':id_area' => $_POST['id_area'],
                    ':id_proveedor' => $_POST['id_proveedor'],
                    ':lote' => $_POST['lote'],
                    ':fecha_fabricacion' => $fecha_fabricacion,
                    ':reg_invima' => $_POST['reg_invima'],
                    ':fabricante' => $_POST['fabricante'],
                    ':lugar_origen' => $_POST['lugar_origen'],
                    ':representante' => $_POST['representante'],
                    ':dir_representante' => $_POST['dir_representante'],
                    ':tel_representante' => $_POST['tel_representante'],
                    ':imagen' => $_POST['imagen'],
                    ':recom_fabricante' => $_POST['recom_fabricante'],
                    ':tipo_adquisicion' => $_POST['tipo_adquisicion'] ? $_POST['tipo_adquisicion'] : null,
                    ':fecha_adquisicion' => date('Y-m-d', strtotime($_POST['fecha_adquisicion'])),
                    ':fecha_instalacion' => date('Y-m-d', strtotime($_POST['fecha_instalacion'])),
                    ':periodo_garantia' => $_POST['periodo_garantia'],
                    ':vida_util' => $_POST['vida_util'],
                    ':calif_4725' => $_POST['calif_4725'] ? $_POST['calif_4725'] : null,
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
                    ':riesgo' => $_POST['riesgo'] ? $_POST['riesgo'] : null,
                    ':uso' => $_POST['uso'] ? $_POST['uso'] : null,
                    ':cb_diagnostico' => $_POST['cb_diagnostico'],
                    ':cb_prevencion' => $_POST['cb_prevencion'],
                    ':cb_rehabilitacion' => $_POST['cb_rehabilitacion'],
                    ':cb_analisis_lab' => $_POST['cb_analisis_lab'],
                    ':cb_trat_mant' => $_POST['cb_trat_mant'],
                    ':estado_general' => $_POST['estado_general'] ? $_POST['estado_general'] : null,
                    ':causa_est_general' => $_POST['causa_est_general'],
                    ':fecha_fuera_servicio' => date('Y-m-d', strtotime($_POST['fecha_fuera_servicio'])),
                    ':id_usr_reg' => $id_usr_crea,
                    ':fecha_reg' => $fecha_crea,
                    ':id_usr_act' => $id_usr_crea,
                    ':fecha_act' => $fecha_crea,
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
                    serial = :serial,
                    id_marca = :id_marca,
                    valor = :valor,
                    tipo_activo = :tipo_activo,
                    id_articulo = :id_articulo,
                    modelo = :modelo,
                    id_sede = :id_sede,
                    id_area = :id_area,
                    id_proveedor = :id_proveedor,
                    lote = :lote,
                    fecha_fabricacion = :fecha_fabricacion,
                    reg_invima = :reg_invima,
                    fabricante = :fabricante,
                    lugar_origen = :lugar_origen,
                    representante = :representante,
                    dir_representante = :dir_representante,
                    tel_representante = :tel_representante,
                    imagen = :imagen,
                    recom_fabricante = :recom_fabricante,
                    tipo_adquisicion = :tipo_adquisicion,
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
                    id_usr_act = :id_usr_act,
                    fecha_act = :fecha_act,
                    estado = :estado
                    WHERE id = :id_hv";

                $sql = $cmd->prepare($sql);

                // Asignar valores utilizando bindValue

                $sql->bindValue(':placa', $_POST['placa']);
                $sql->bindValue(':serial', $_POST['serial']);
                $sql->bindValue(':id_marca', $_POST['id_marca'] ? $_POST['id_marca'] : null, PDO::PARAM_INT);
                $sql->bindValue(':valor', $_POST['valor']);
                $sql->bindValue(':tipo_activo', $_POST['tipo_activo'] ? $_POST['tipo_activo'] : null, PDO::PARAM_INT);
                $sql->bindValue(':id_articulo', $_POST['id_articulo'] ? $_POST['id_articulo'] : null, PDO::PARAM_INT);
                $sql->bindValue(':modelo', $_POST['modelo']);
                $sql->bindValue(':id_sede', $_POST['id_sede'] ? $_POST['id_sede'] : null, PDO::PARAM_INT);
                $sql->bindValue(':id_area', $_POST['id_area'] ? $_POST['id_area'] : null, PDO::PARAM_INT);
                $sql->bindValue(':id_proveedor', $_POST['id_proveedor'] ? $_POST['id_proveedor'] : null, PDO::PARAM_INT);
                $sql->bindValue(':lote', $_POST['lote']);
                
                $fecha_fabricacion_mysql = $_POST['fecha_fabricacion'] ? date('Y-m-d', strtotime($_POST['fecha_fabricacion'])) : null;
                $sql->bindValue(':fecha_fabricacion', $fecha_fabricacion_mysql);
                
                $sql->bindValue(':reg_invima', $_POST['reg_invima']);
                $sql->bindValue(':fabricante', $_POST['fabricante']);
                $sql->bindValue(':lugar_origen', $_POST['lugar_origen']);
                $sql->bindValue(':representante', $_POST['representante']);
                $sql->bindValue(':dir_representante', $_POST['dir_representante']);
                $sql->bindValue(':tel_representante', $_POST['tel_representante']);
                $sql->bindValue(':imagen', $_POST['imagen']);
                $sql->bindValue(':recom_fabricante', $_POST['recom_fabricante']);
                $sql->bindValue(':tipo_adquisicion', $_POST['tipo_adquisicion'] ? $_POST['tipo_adquisicion'] : null, PDO::PARAM_INT);
                $sql->bindValue(':fecha_adquisicion', $_POST['fecha_adquisicion'] ? date('Y-m-d', strtotime($_POST['fecha_adquisicion'])) : null);
                $sql->bindValue(':fecha_instalacion', $_POST['fecha_instalacion'] ? date('Y-m-d', strtotime($_POST['fecha_instalacion'])) : null);
                $sql->bindValue(':periodo_garantia', $_POST['periodo_garantia']);
                $sql->bindValue(':vida_util', $_POST['vida_util']);
                $sql->bindValue(':calif_4725', $_POST['calif_4725'] ? $_POST['calif_4725'] : null, PDO::PARAM_INT);
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
                $sql->bindValue(':riesgo', $_POST['riesgo'] ? $_POST['riesgo'] : null, PDO::PARAM_INT);
                $sql->bindValue(':uso', $_POST['uso'] ? $_POST['uso'] : null, PDO::PARAM_INT);
                $sql->bindValue(':cb_diagnostico', $_POST['cb_diagnostico']);
                $sql->bindValue(':cb_prevencion', $_POST['cb_prevencion']);
                $sql->bindValue(':cb_rehabilitacion', $_POST['cb_rehabilitacion']);
                $sql->bindValue(':cb_analisis_lab', $_POST['cb_analisis_lab']);
                $sql->bindValue(':cb_trat_mant', $_POST['cb_trat_mant']);
                $sql->bindValue(':estado_general', $_POST['estado_general'] ? $_POST['estado_general'] : null, PDO::PARAM_INT);
                $sql->bindValue(':causa_est_general', $_POST['causa_est_general']);
                $sql->bindValue(':fecha_fuera_servicio', $_POST['fecha_fuera_servicio'] ? date('Y-m-d', strtotime($_POST['fecha_fuera_servicio'])) : null);
                $sql->bindValue(':id_usr_act', $id_usr_crea, PDO::PARAM_INT);
                $sql->bindValue(':fecha_act', $fecha_crea);
                $sql->bindValue(':estado', $_POST['estado'] ? $_POST['estado'] : null, PDO::PARAM_INT);
                $sql->bindValue(':id_hv', $id_hv, PDO::PARAM_INT);

                $updated = $sql->execute();

                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id_hv'] = $id_hv;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            }

            if ($oper == 'del') {
                $id = $_POST['id'];
                $sql = "DELETE FROM acf_orden_ingreso_detalle WHERE id_ing_detalle=" . $id;
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
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
