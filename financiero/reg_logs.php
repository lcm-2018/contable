<?php

function obtenerIPCliente()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Si el usuario está detrás de un proxy, puede haber múltiples IPs separadas por comas
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function RegistraLogs($ruta_base,$sql)
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!file_exists($ruta_base)) {
        mkdir($ruta_base, 0777, true);
    }

    $fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
    $id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 'Desconocido';
    $usuario = isset($_SESSION['user']) ? $_SESSION['user'] : 'Desconocido';

    // Construir ruta del archivo usando '/'
    $archivo = $ruta_base . '/' . $fecha->format('Y') . $fecha->format('m') . '.log';

    $ip_cliente = obtenerIPCliente();

    $cadena = "[{$fecha->format('Y-m-d H:i:s')}] Usuario: $id_user-$usuario, IP: $ip_cliente, SQL: $sql" . PHP_EOL;

    // Intentar escribir al archivo con manejo de errores
    try {
        if (!file_put_contents($archivo, $cadena, FILE_APPEND | LOCK_EX)) {
            throw new Exception("Error al escribir el log");
        }
    } catch (Exception $e) {
        echo "Error al registrar log: " . $e->getMessage();
    }
}
