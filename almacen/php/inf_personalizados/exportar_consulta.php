<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../../conexion.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$fecha = date('Y-m-d H:i:s');
$id_usr = $_SESSION['id_user'];

session_write_close();
set_time_limit(0);
ini_set('memory_limit', '-1');

$id = $_POST['id'];
$parametros = json_decode($_POST['parametros']);
$separador = ",";
$res = array();

try {
    
    $sql = 'SELECT consulta,nom_consulta FROM tb_consultas_sql WHERE id_consulta=' . $id . ' LIMIT 1';
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cnsql = $obj['consulta'];
    $nom_consulta = $obj['nom_consulta'];

    foreach ($parametros as $pr) {
        $cnsql = str_replace('[' . $pr->parametro . ']', $pr->valor, $cnsql);
    }

    $rs = $cmd->query($cnsql);
    $objs = $rs->fetchAll();
    $n = $rs->columnCount(); 

    $archivo = fopen('consulta_'.$id_usr.'.csv', 'w+b');

    $encabezado = ''; /* Variable q almacena el nombre de las columnas */
    for ($i = 0; $i < $n; ++$i) {
        $col = $rs->getColumnMeta($i);
        $i != $n - 1 ? $encabezado .= $col['name'] . $separador : $encabezado.=$col['name'] . "\r\n";
    }
    $encabezado=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$encabezado);
    fwrite($archivo, $encabezado);

    $l = count($objs); //Cantidad Total Registros
    $s = 1;
    foreach ($objs as $obj) {
        $fila = '';
        for ($i = 0; $i < $n; ++$i) {
            $str=str_replace(array("\r", "\n","\r\n",'"'), "", $obj[$i]);
            if ($s != $l) {
                $i != $n - 1 ? $fila.=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',str_replace($separador,"?",$str)) . $separador : $fila.=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',str_replace($separador,"?",$str)) . "\r\n";
            } else {
                $i != $n - 1 ? $fila.=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',str_replace($separador,"?",$str)) . $separador : $fila.=iconv('UTF-8', 'ISO-8859-1//TRANSLIT',str_replace($separador,"?",$str));
            }
        }
        $data = str_replace("\t", "", $fila);        
        fwrite($archivo, $data);
        $s++;
    }
    fflush($archivo);
    fclose($archivo);

    $res['mensaje']='ok';
    $res['archivo']='consulta_'.$id_usr.'.csv';

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
