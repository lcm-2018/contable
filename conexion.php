<?php
date_default_timezone_set('America/Bogota');

$bd_driver = "mysql";
<<<<<<< HEAD
$bd_servidor = "localhost";
$bd_base = "bd_cronhis_af";
=======
<<<<<<< HEAD
$bd_servidor = "localhost";
$bd_base = "ips_mun";
=======
$bd_servidor = "localhost:3366";
$bd_base = "municipal";
>>>>>>> c38bd91f33799df1caf435dfd8235d2d48f79ad0

>>>>>>> a1e488548f40166116d8295d66cc5c8ab4cf1259
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
