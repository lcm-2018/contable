<?php

session_start();

include '../../../conexion.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT
    `rubro`
    , SUM(`valor`) AS valor
FROM
    `pto_documento_detalles`
WHERE (`tipo_mov` ='CDP')
GROUP BY `rubro`;";
    $rs = $cmd->query($sql);
    $centros = $rs->fetchAll();
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo "<br>";
echo "<br>";
echo "<br>";
$cdps = [];
foreach ($centros as $key => $value) {
    // crear array con llave y valor
    $cdps[$value['rubro']] = $value['valor'];
}
print_r($cdps);
/*
$busqueda = "2.4.5.02.09.05";

$resultados = array_filter($centros, function ($clave) use ($busqueda) {
    return   $clave != $busqueda;
});
print_r($resultados);
echo "<br>";
echo "<br>";
echo "<br>";
if (!empty($resultados)) {
    foreach ($resultados as $clave => $valor) {
        print_r($clave) . " - " . print_r($valor) . '<br>';
    }
} else {
    echo "No se encontraron '$clave' elementos que empiecen con '$busqueda' en el array";
}
*/
$mi_array = array(
    "manzana" => "roja",
    "banana" => "amarilla",
    "naranja" => "anaranjada",
    "mango" => "amarillo"
);
echo "<br>";
echo "<br>";
$busqueda = "2";

$resultados = array_filter($cdps, function ($clave) use ($busqueda) {
    return substr($clave, 0, strlen($busqueda)) === $busqueda;
}, ARRAY_FILTER_USE_KEY);

if (!empty($resultados)) {
    foreach ($resultados as $clave => $valor) {
        echo "La clave '$clave' tiene el valor '$valor'<br>";
    }
} else {
    echo "No se encontraron elementos que empiecen con '$busqueda' en el array";
}
