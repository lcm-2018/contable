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
        $sql = "SELECT id_med,nom_medicamento AS nom_articulo,val_promedio
                FROM far_medicamentos
                WHERE id_med=$id_med";
        $rs = $cmd->query($sql);
        $obj = $rs->fetch();
        if (isset($obj['id_med'])) {
            $res = array('id_med' => $obj['id_med'], 'nom_articulo' => $obj['nom_articulo'], 'val_promedio' => $obj['val_promedio']);
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
