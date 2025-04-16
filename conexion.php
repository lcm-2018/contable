<?php
date_default_timezone_set('America/Bogota');

$bd_driver = "mysql";
<<<<<<< HEAD
$bd_servidor = "localhost";
$bd_base = "bd_aguazul";
=======
$bd_servidor = "localhost:3366";
$bd_base = "municipal";
>>>>>>> 9a58158badcf106f5e39cf701a5119c4532ee74c
$api =  "http://200.7.102.155/api_terceros/";
$charset = "charset=utf8";
$bd_usuario = "root";
$bd_clave = "12345";
$_SESSION['urlin'] = "/contable";

//Rutas de firmas y logs
$ruta_firmas = "/cronhis/img/firmas/";
$_SESSION['ruta_logs'] = "/var/www/html/contable/log/";

$ruta_firmas = "/proyecto/hc/img/firmas/";
$_SESSION['ruta_logs'] = "C:/wamp64/www/contable/log/";