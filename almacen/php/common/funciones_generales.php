<?php

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

//FUNCION QUE RETORNAR LOS DATOS DE UN LOTE
function datos_lote($cmd, $id_lote){
    try {
        $res = array();
        $sql = "SELECT far_medicamento_lote.id_lote,far_medicamento_lote.lote,
                    far_medicamentos.nom_medicamento AS nom_articulo,far_medicamentos.val_promedio,
                    far_medicamento_lote.id_presentacion,far_presentacion_comercial.nom_presentacion,
                    IFNULL(far_presentacion_comercial.cantidad,1) AS cantidad_umpl
                FROM far_medicamento_lote
                INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
                INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_medicamento_lote.id_presentacion)
                WHERE far_medicamento_lote.id_lote=$id_lote";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if (isset($obj['id_lote'])) {
            $res = array('id_lote' => $obj['id_lote'], 'lote' => $obj['lote'], 'nom_articulo' => $obj['nom_articulo'], 'val_promedio' => $obj['val_promedio'], 'id_presentacion' => $obj['id_presentacion'], 'nom_presentacion' => $obj['nom_presentacion'], 'cantidad_umpl' => $obj['cantidad_umpl']);
        } else {
            $res = array('id_lote' => '', 'lote' => '', 'nom_articulo' => '', 'val_promedio' => '', 'id_presentacion' => '', 'nom_presentacion' => '', 'cantidad_umpl' => '');
        }
        $cmd = null;
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

//FUNCION QUE RETORNAR LOS DATOS DE UN ARTICULO
function datos_articulo($cmd, $id_med){
    try {
        $res = array();
        $sql = "SELECT id_med,nom_medicamento,val_promedio
                FROM far_medicamentos
                WHERE id_med=$id_med";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if (isset($obj['id_med'])) {
            $res = array('id_med' => $obj['id_med'], 
                        'nom_articulo' => $obj['nom_medicamento'], 
                        'val_promedio' => $obj['val_promedio']);
        } else {
            $res = array('id_med' => '', 'nom_articulo' => '', 'val_promedio' => '');
        }
        $cmd = null;
        return $res;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

//BITACORA DE MENSAJES A UN ARCHIVO DE ACCIONES REALIZADAS
function bitacora($accion, $opcion, $detalle, $id_usuario, $login) {
    $fecha = date('Y-m-d h:i:s A');
    $usuario = $id_usuario . '-' . $login;
    $ip=$_SERVER['REMOTE_ADDR'];
    $archivo = date('Ym');
    $dir='C:\wamp64\www\contable\log';
    $log= "Fecha: $fecha, Id Usuario-Login: $usuario, Accion: $accion, Opcion: $opcion, Registro: $detalle,IP:$ip\r\n";
    file_put_contents("$dir/$archivo.log", $log, FILE_APPEND | LOCK_EX);
}
