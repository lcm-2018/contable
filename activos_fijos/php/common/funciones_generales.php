<?php

//FUNCION QUE RETORNAR FECHA Y HORA DEL SERVIDOR
function fecha_hora_servidor(){
    $res = array();
    date_default_timezone_set('America/Bogota');
    $res['hora'] = date('h:iA');
    $res['hora24h'] = date('H:i');
    $res['fecha'] = date('Y-m-d');    
    return $res;
}

//FUNCION PARA DAR FORMATO A LOS VALORES NUMERICOS
function formato_valor($valor){
    return '$' . number_format($valor, 2, ",", ".");    
}

//FUNCION QUE RETORNAR LOS DATOS DE UN ARTICULO
function datos_articulo($cmd, $id_med){
    try {
        $res = array();
        $sql = "SELECT far_medicamentos.id_med,far_medicamentos.cod_medicamento,
                    far_medicamentos.existencia,far_medicamentos.val_promedio,
                    far_medicamentos.nom_medicamento,
                    IF(acf_orden_ingreso_detalle.valor IS NULL,0,acf_orden_ingreso_detalle.valor) AS valor
                FROM far_medicamentos
                LEFT JOIN (SELECT acf_orden_ingreso_detalle.id_articulo,MAX(acf_orden_ingreso_detalle.id_ing_detalle) AS id 
                           FROM acf_orden_ingreso_detalle 
                           INNER JOIN acf_orden_ingreso ON (acf_orden_ingreso.id_ingreso=acf_orden_ingreso_detalle.id_ingreso)
                           WHERE acf_orden_ingreso.estado=2 AND acf_orden_ingreso_detalle.id_articulo=$id_med) AS v ON (v.id_articulo=far_medicamentos.id_med)
                LEFT JOIN acf_orden_ingreso_detalle ON (acf_orden_ingreso_detalle.id_ing_detalle=v.id)
                WHERE far_medicamentos.id_med=$id_med";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if (isset($obj['id_med'])) {
            $res = array('id_med' => $obj['id_med'],
                        'cod_articulo' => $obj['cod_medicamento'],
                        'nom_articulo' => $obj['nom_medicamento'],
                        'existencia' => $obj['existencia'],
                        'val_promedio' => $obj['val_promedio'],
                        'valor_ultima_compra' => $obj['valor']
                    );
        } else {
            $res = array('id_med' => '', 'cod_articulo' => '', 'nom_articulo' => '', 'existencia' => '', 'val_promedio' => '');
        }
        $cmd = null;
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

//FUNCION QUE RETORNAR LOS DATOS DE UN ACTIVO FIJO
function datos_activo_fijo($cmd, $id_acf){
    try {
        $res = array();
        $sql = "SELECT acf_hojavida.id_activo_fijo,acf_hojavida.placa,acf_hojavida.estado_general,acf_hojavida.id_area,
                    far_medicamentos.cod_medicamento,
                    far_medicamentos.nom_medicamento,
                    acf_hojavida.des_activo
                FROM acf_hojavida
                INNER JOIN far_medicamentos ON (far_medicamentos.id_med=acf_hojavida.id_articulo)
                WHERE acf_hojavida.id_activo_fijo=$id_acf";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if (isset($obj['id_activo_fijo'])) {
            $res = array('id_activo_fijo' => $obj['id_activo_fijo'],
                        'placa' => $obj['placa'],
                        'cod_articulo' => $obj['cod_medicamento'],
                        'nom_articulo' => $obj['nom_medicamento'],
                        'des_activo' => $obj['des_activo'],
                        'estado_general' => $obj['estado_general'],
                        'id_area' => $obj['id_area']
                    );
        } else {
            $res = array('id_activo_fijo' => '', 'placa' => '', 'cod_articulo' => '', 'nom_articulo' => '');
        }
        $cmd = null;
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

//FUNCION QUE ACTIVA O DESACTIVA EL ESTADO DEL ACTIVO FIJO PARA MODIFICAR
function edit_estados_activo_fijo($cmd, $id_acf){
    try {
        $res = array();
        $edit_ubi = 1;
        $edit_art = 1;
        $edit_est = 1;
        $sql = "SELECT COUNT(*) AS total FROM acf_traslado_detalle
                INNER JOIN far_traslado ON (far_traslado.id_traslado=acf_traslado_detalle.id_traslado)
                WHERE far_traslado.estado IN (1,2) AND acf_traslado_detalle.id_activo_fijo=$id_acf";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if ($obj['total'] > 0) {
            $edit_ubi = 0;
        }
        
        $sql = "SELECT COUNT(*) AS total FROM acf_baja_detalle
                INNER JOIN acf_baja ON (acf_baja.id_baja=acf_baja_detalle.id_baja)
                WHERE acf_baja.estado=2 AND acf_baja_detalle.id_activo_fijo=$id_acf";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if ($obj['total'] > 0) {
            $edit_ubi = 0;
            $edit_art = 0;
            $edit_est = 0;
        }    

        $sql = "SELECT COUNT(*) AS total FROM acf_mantenimiento_detalle
                INNER JOIN acf_mantenimiento ON (acf_mantenimiento.id_mantenimiento=acf_mantenimiento_detalle.id_mantenimiento)
                WHERE acf_mantenimiento.estado IN (2,3) AND acf_mantenimiento_detalle.id_activo_fijo=$id_acf";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if ($obj['total'] > 0) {                
            $edit_est = 0;
        }            
        $cmd = null;
        $res = array('edit_ubi' => $edit_ubi, 'edit_art' => $edit_art, 'edit_est' => $edit_est);
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

//FUNCION QUE RETORNA EL ESTADO DE MANTENIMIENTO DE UN ACTIVO FIJO
function estados_activo_fijo($cmd, $id_acf){
    try {
        $res = array();
        $sql = "SELECT COUNT(*) AS total FROM acf_mantenimiento_detalle
                INNER JOIN acf_mantenimiento ON (acf_mantenimiento.id_mantenimiento=acf_mantenimiento_detalle.id_mantenimiento)
                WHERE acf_mantenimiento.estado=3 AND acf_mantenimiento_detalle.estado IN (1,2) 
                    AND acf_mantenimiento_detalle.id_activo_fijo=$id_acf";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if ($obj['total']>0) {
            $res = array('estado' => 3);
        } else {        
            $sql = "SELECT COUNT(*) AS total FROM acf_mantenimiento_detalle
                    INNER JOIN acf_mantenimiento ON (acf_mantenimiento.id_mantenimiento=acf_mantenimiento_detalle.id_mantenimiento)
                    WHERE acf_mantenimiento.estado=2 AND acf_mantenimiento_detalle.estado IN (1,2) 
                        AND acf_mantenimiento_detalle.id_activo_fijo=$id_acf";
            $rs = $cmd->query($sql);
            $obj = $rs->fetch();
            if ($obj['total']>0) {
                $res = array('estado' => 2);
            } else {
                $res = array('estado' => 1);
            }    
        }
        $cmd = null;
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

//BITACORA DE MENSAJES A UN ARCHIVO DE ACCIONES REALIZADAS
function bitacora($accion, $opcion, $detalle, $id_usuario, $login) {
    $fecha = '[' . date('Y-m-d h:i:s A') . ']';
    $usuario = $id_usuario . '-' . $login;
    $ip=$_SERVER['REMOTE_ADDR'];    
    $archivo = $_SESSION['ruta_logs'] . date('Ym') . '.log';
    $log= "$fecha Usuario: $usuario, IP: $ip, Accion: $accion, Opcion: $opcion, Registro: $detalle\r\n";
    file_put_contents("$archivo", $log, FILE_APPEND | LOCK_EX);
}

//FUNCION QUE RETORNA LA BODEGA PRINCIPAL DE LA ENTIDAD
function bodega_principal($cmd){
    try {
        $idusr = $_SESSION['id_user'];
        $idrol = $_SESSION['rol'];
        $res = array();
        $sql = "SELECT far_bodegas.id_bodega,far_bodegas.nombre,tb_sedes_bodega.id_sede,seg_bodegas_usuario.id_usuario
                FROM far_bodegas 
                LEFT JOIN tb_sedes_bodega ON (tb_sedes_bodega.id_bodega=far_bodegas.id_bodega)
                LEFT JOIN seg_bodegas_usuario ON (seg_bodegas_usuario.id_bodega=far_bodegas.id_bodega AND seg_bodegas_usuario.id_usuario=$idusr)
                WHERE far_bodegas.es_principal=1";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if (isset($obj['id_bodega'])) {
            if (isset($obj['id_sede'])) {
                if (isset($obj['id_usuario']) || $idrol == 1) {
                    $res = array('id_bodega' => $obj['id_bodega'], 'nom_bodega' => $obj['nombre'], 'id_sede' => $obj['id_sede']);
                } else {
                    $res = array('id_bodega' => '', 'nom_bodega' => 'La Bodega Principal no esta asociada al Usuario', 'id_sede' => '');        
                }    
            } else {
                $res = array('id_bodega' => '', 'nom_bodega' => 'La Bodega Principal no tiene Sede', 'id_sede' => '');    
            }    
        } else {
            $res = array('id_bodega' => '', 'nom_bodega' => 'No Existe Bodega Principal', 'id_sede' => '');
        }
        $cmd = null;
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function sede_principal($cmd){
    try {
        $idusr = $_SESSION['id_user'];
        $idrol = $_SESSION['rol'];
        $res = array();
        $sql = "SELECT tb_sedes.id_sede,tb_sedes.nom_sede,tb_sedes.es_principal,seg_sedes_usuario.id_usuario
                FROM tb_sedes
                LEFT JOIN seg_sedes_usuario ON (seg_sedes_usuario.id_sede = tb_sedes.id_sede AND seg_sedes_usuario.id_usuario = $idusr)
                WHERE tb_sedes.es_principal=1";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
  
        if (isset($obj['id_sede'])) {
            if (isset($obj['id_usuario']) || $idrol == 1) {
                $res = array('id_sede' => $obj['id_sede'], 'nom_sede' => $obj['nom_sede']);
            } else {
                $res = array('id_sede' => '', 'nom_sede' => 'La Bodega Principal no esta asociada al Usuario', 'id_sede' => ''); 
            }
        } else {
            $res = array('id_sede' => '', 'nom_sede' => 'No Existe Sede Principal', 'id_sede' => '');    
        }
  
        $cmd = null;
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function area_principal($cmd){
    try {
 
        $res = array();
        $sql = "SELECT far_centrocosto_area.id_area,far_centrocosto_area.nom_area,far_centrocosto_area.id_responsable,
                    CONCAT_WS(' ',usr.apellido1,usr.apellido2,usr.nombre1,usr.nombre2) AS nom_responsable
                FROM far_centrocosto_area 
                INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=far_centrocosto_area.id_responsable)
                WHERE far_centrocosto_area.es_almacen=1";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
  
        if (isset($obj['id_area'])) {
            $res = array('id_area' => $obj['id_area'], 'nom_area' => $obj['nom_area'], 'id_responsable' => $obj['id_responsable'], 'nom_responsable' => $obj['nom_responsable']);
        } else {
            $res = array('id_area' => '', 'nom_area' => 'No Existe Area Principal', 'id_responsable' => '', 'nom_responsable' => '');    
        }
  
        $cmd = null;
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
