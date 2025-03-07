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

//BITACORA DE MENSAJES A UN ARCHIVO DE ACCIONES REALIZADAS
function bitacora($accion, $opcion, $detalle, $id_usuario, $login) {
    $fecha = date('Y-m-d h:i:s A');
    $usuario = $id_usuario . '-' . $login;
    $ip=$_SERVER['REMOTE_ADDR'];    
    $archivo = $_SESSION['ruta_logs'] . date('Ym') . 'log';
    $log= "Fecha: $fecha, Id Usuario-Login: $usuario, Accion: $accion, Opcion: $opcion, Registro: $detalle,IP:$ip\r\n";
    file_put_contents("$archivo", $log, FILE_APPEND | LOCK_EX);
}
